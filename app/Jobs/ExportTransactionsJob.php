<?php

namespace App\Jobs;

use App\Services\TransactionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class ExportTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $filters;
    protected array $sort;
    protected string $filePath;
    protected string $jobId;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;
    public $backoff = [60, 120]; // Retry delays in seconds

    private readonly TransactionService $transactionService;
    /**
     * Create a new job instance.
     */
    public function __construct(array $filters, array $sort, string $filePath, string $jobId)
    {
        $this->filters = $filters;
        $this->sort = $sort;
        $this->filePath = $filePath;
        $this->jobId = $jobId;
        $this->transactionService = app(TransactionService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Build query with filters, Get all transactions (no pagination for export)
            $transactions = $this->transactionService->getExportData($this->filters, $this->sort);

            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Transactions');

            // Define headers
            $headers = ['DO Date', 'DO Actual Date', 'DO Number', 'Customer', 'Vehicle Plate', 'Destination Address', 'Trip Amount', 'Origin', 'Destination', 'Status', 'Created At', 'Detail Tujuan', 'Detail_Amount', 'Detail_Status', 'Detail_Created_At'];
            $sheet->fromArray([$headers], null, 'A1');

            // Style header row
            $headerStyle = $sheet->getStyle('A1:O1');
            $headerStyle->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
            $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF366092');
            $headerStyle->getAlignment()->setHorizontal('center')->setVertical('center');

            // Add data
            $row = 2;
            foreach ($transactions as $transaction) {
                foreach($transaction->transactionDetails as $detail) {
                    $sheet->setCellValue('A' . $row, $transaction->do_date);
                    $sheet->setCellValue('B' . $row, $transaction->do_actual_date);
                    $sheet->setCellValue('C' . $row, $transaction->do_number);
                    $sheet->setCellValue('D' . $row, $transaction->customer_name ?? '-');
                    $sheet->setCellValue('E' . $row, $transaction->vehicle_plate ?? '-');
                    $sheet->setCellValue('F' . $row, $transaction->dest_address ?? '-');
                    $sheet->setCellValue('G' . $row, $transaction->trip_price_amount);
                    $sheet->setCellValue('H' . $row, $transaction->origin_district ?? '-');
                    $sheet->setCellValue('I' . $row, $transaction->destination_district ?? '-');
                    $sheet->setCellValue('J' . $row, $transaction->status);
                    $sheet->setCellValue('K' . $row, $transaction->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i'));
                    // Detail columns
                    $sheet->setCellValue('L' . $row, $detail->purpose ?? '-');
                    $sheet->setCellValue('M' . $row, $detail->amount ?? '-');
                    $sheet->setCellValue('N' . $row, $detail->status ?? '-');
                    $sheet->setCellValue('O' . $row, $detail->created_at ?  $detail->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') : '-');

                    // Alternate row colors for readability
                    if ($row % 2 === 0) {
                        $sheet->getStyle('A' . $row . ':O' . $row)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('FFF2F2F2');
                    }

                    $row++;
                }
            }

            // Adjust column widths
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(18);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(15);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(20);
            $sheet->getColumnDimension('L')->setWidth(20);
            $sheet->getColumnDimension('M')->setWidth(15);
            $sheet->getColumnDimension('N')->setWidth(15);
            $sheet->getColumnDimension('O')->setWidth(20);

            // Format date columns
            $sheet->getStyle('A2:A' . ($row - 1))->getNumberFormat()->setFormatCode('YYYY-MM-DD');
            $sheet->getStyle('B2:B' . ($row - 1))->getNumberFormat()->setFormatCode('YYYY-MM-DD');
            $sheet->getStyle('K2:K' . ($row - 1))->getNumberFormat()->setFormatCode('YYYY-MM-DD HH:MM');
            $sheet->getStyle('O2:O' . ($row - 1))->getNumberFormat()->setFormatCode('YYYY-MM-DD HH:MM');

            // Write to storage
            $writer = new Xlsx($spreadsheet);
            $stream = fopen('php://temp', 'w+');
            $writer->save($stream);
            rewind($stream);
            $excelContent = stream_get_contents($stream);
            fclose($stream);

            // Save file
            Storage::disk('local')->put($this->filePath, $excelContent);

            // Log success
            Log::info("Export job {$this->jobId} completed successfully", [
                'rows' => $row-1,
                'file' => $this->filePath,
            ]);

        } catch (Throwable $exception) {
            // Save error status
            $errorStatus = [
                'status' => 'failed',
                'job_id' => $this->jobId,
                'error' => $exception->getMessage(),
                'timestamp' => now(),
            ];

            Storage::disk('local')->put(
                "exports/transactions/{$this->jobId}.status",
                json_encode($errorStatus)
            );

            Log::error("Export job {$this->jobId} failed", [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            // Optionally re-throw to trigger retry
            // throw $exception;
        }
    }
}
