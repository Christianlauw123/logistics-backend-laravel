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

    /**
     * Create a new job instance.
     */
    public function __construct(array $filters, array $sort, string $filePath, string $jobId)
    {
        $this->filters = $filters;
        $this->sort = $sort;
        $this->filePath = $filePath;
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle(TransactionService $transactionService): void
    {
        try {
            // Build query with filters, Get all transactions (no pagination for export)
            $transactions = $transactionService->getExportData($this->filters, $this->sort);
            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Transactions');

            // Define headers
            $headers = [
                'Status',
                'Tanggal DO Dibuat',
                'NO DO',
                'Tanggal Aktual DO',
                'Pelanggan',
                'Akun Bank',
                'Supir',
                'Plat Kendaraan',
                'Biaya Trip Normal',
                'Asal',
                'Tujuan',
                'Tonase',
                'Ada Revisi Tujuan',
                'Tujuan Revisi',
                'Biaya Trip Revisi',
                'Revisi Tonase',
                'Dibuat Oleh',
                'Tanggal Dibuat',
                'Terakhir diubah oleh',
                'Tanggal Terakhir diubah',
                'Detail_Status',
                'Detail Keperluan',
                'Detail Jumlah',
                'Detail Catatan',
                'Detail Kasus Khusus',
                'Detail Bukti',
                'Detail Dibuat Oleh',
                'Detail Tanggal Dibuat',
                'Detail Terakhir diubah oleh',
                'Detail Tanggal Terakhir diubah',
                ];
            $sheet->fromArray([$headers], null, 'A1');

            // Style header row
            $headerStyle = $sheet->getStyle('A1:AB1');
            $headerStyle->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
            $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF366092');
            $headerStyle->getAlignment()->setHorizontal('center')->setVertical('center');

            // Add data
            $row = 2;
            foreach ($transactions as $transaction) {
                foreach($transaction->transactionDetails as $detail) {
                    $sheet->setCellValue('A' . $row, $transaction->status->value);
                    $sheet->setCellValue('B' . $row, $transaction->do_date);
                    $sheet->setCellValue('C' . $row, $transaction->do_number);
                    $sheet->setCellValue('D' . $row, $transaction->do_actual_date ?? '-');
                    $sheet->setCellValue('E' . $row, $transaction->customer_name ?? '-');
                    $sheet->setCellValue('F' . $row, $transaction->bank_account_num ?? '-');
                    $sheet->setCellValue('G' . $row, $transaction->driver_name ?? '-');
                    $sheet->setCellValue('H' . $row, $transaction->vehicle_plate ?? '-');
                    $sheet->setCellValue('I' . $row, $transaction->trip_price_amount);
                    $sheet->setCellValue('J' . $row, $transaction->origin_district ?? '-');
                    $sheet->setCellValue('K' . $row, $transaction->destination_district ?? '-');
                    $sheet->setCellValue('L' . $row, $transaction->weight_category ?? '-');
                    $sheet->setCellValue('M' . $row, $transaction->dest_sub_district_id != $transaction->revision_dest_sub_district_id ? 'y' : 'n');
                    $sheet->setCellValue('N' . $row, $transaction->revision_destination_district ?? '-');
                    $sheet->setCellValue('O' . $row, $transaction->revision_trip_price_amount ?? '-');
                    $sheet->setCellValue('P' . $row, $transaction->revision_weight_category ?? '-');
                    $sheet->setCellValue('Q' . $row, $transaction->user->email ?? '-');
                    $sheet->setCellValue('R' . $row, $transaction->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? '-');
                    $sheet->setCellValue('S' . $row, $transaction->lastUpdatedBy->email ?? '-');
                    $sheet->setCellValue('T' . $row, $transaction->updated_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? '-');

                    // Detail columns
                    $sheet->setCellValue('U' . $row, $detail->status?->value ?? '-');
                    $sheet->setCellValue('V' . $row, $detail->purpose ?? '-');
                    $sheet->setCellValue('W' . $row, $detail->amount ?? '-');
                    $sheet->setCellValue('X' . $row, $detail->note ?? '-');
                    $sheet->setCellValue('Y' . $row, $detail->is_special_case ? 'y' : 'n');
                    $sheet->setCellValue('Z' . $row, $detail->attachment->file_url ?? '-');
                    $sheet->setCellValue('AA' . $row, $detail->user->email ?? '-');
                    $sheet->setCellValue('AB' . $row, $detail->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? '-');
                    $sheet->setCellValue('AC' . $row, $detail->lastUpdatedBy->email ?? '-');
                    $sheet->setCellValue('AD' . $row, $detail->updated_at->timezone('Asia/Jakarta')->format('Y-m-d H:i') ?? '-');

                    // Alternate row colors for readability
                    if ($row % 2 === 0) {
                        $sheet->getStyle('A' . $row . ':AD' . $row)
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
            $sheet->getColumnDimension('P')->setWidth(15);
            $sheet->getColumnDimension('Q')->setWidth(20);
            $sheet->getColumnDimension('R')->setWidth(20);
            $sheet->getColumnDimension('S')->setWidth(15);
            $sheet->getColumnDimension('T')->setWidth(15);
            $sheet->getColumnDimension('U')->setWidth(20);
            $sheet->getColumnDimension('V')->setWidth(15);
            $sheet->getColumnDimension('W')->setWidth(20);
            $sheet->getColumnDimension('X')->setWidth(20);
            $sheet->getColumnDimension('Y')->setWidth(15);
            $sheet->getColumnDimension('Z')->setWidth(15);
            $sheet->getColumnDimension('AA')->setWidth(20);
            $sheet->getColumnDimension('AB')->setWidth(20);
            $sheet->getColumnDimension('AC')->setWidth(20);
            $sheet->getColumnDimension('AD')->setWidth(20);


            // Format date columns
            $sheet->getStyle('B2:B' . ($row - 1))->getNumberFormat()->setFormatCode('YYYY-MM-DD');
            $sheet->getStyle('D2:D' . ($row - 1))->getNumberFormat()->setFormatCode('YYYY-MM-DD');
            $sheet->getStyle('R2:R' . ($row - 1))->getNumberFormat()->setFormatCode('YYYY-MM-DD HH:mm');
            $sheet->getStyle('T2:T' . ($row - 1))->getNumberFormat()->setFormatCode('YYYY-MM-DD HH:mm');
            $sheet->getStyle('AB2:AB' . ($row - 1))->getNumberFormat()->setFormatCode('YYYY-MM-DD HH:mm');
            $sheet->getStyle('AD2:AD' . ($row - 1))->getNumberFormat()->setFormatCode('YYYY-MM-DD HH:mm');

            // Write to storage safely using a physical system temp file
            $writer = new Xlsx($spreadsheet);
            $tempFilePath = tempnam(sys_get_temp_dir(), 'xlsx_export_');
            $writer->save($tempFilePath);

            // // Write to storage
            // $writer = new Xlsx($spreadsheet);
            // $stream = fopen('php://temp', 'w+');
            // $writer->save($stream);
            // rewind($stream);
            // $excelContent = stream_get_contents($stream);
            // fclose($stream);

            // Save file
            // Storage::disk('local')->put($this->filePath, $excelContent);
            Storage::disk('local')->put($this->filePath, fopen($tempFilePath, 'r+'));
            unlink($tempFilePath);

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
                'trace' => $exception->getTrace(),
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
