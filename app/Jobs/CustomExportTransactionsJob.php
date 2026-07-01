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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class CustomExportTransactionsJob implements ShouldQueue
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

            // 2. Identify all unique truly dynamic columns across the entire dataset
            $dynamicPurposes = [];
            foreach ($transactions as $transaction) {
                foreach ($transaction->transactionDetails as $detail) {
                    $purpose = strtolower(trim($detail->purpose));

                    // Skip if empty or if it falls into one of our three smart static columns
                    if (empty($purpose) || str_contains($purpose, 'ujp') || str_contains($purpose, 'tabungan')) {
                        continue;
                    }

                    // Accumulate unique names for the right-hand dynamic expansion
                    if (!in_array(strtolower($purpose), $dynamicPurposes)) {
                        $dynamicPurposes[] = $purpose;
                    }
                }
            }
            sort($dynamicPurposes); // Sort alphabetically for a neat layout

            // 3. Define Final Sheet Headers
            $staticHeaders = [
                'No',
                'Tanggal',
                'Pelanggan',
                'Supir',
                'Plat Kendaraan',
                'Tujuan Revisi',
                'Total UJP',    // Smart Static Column 1 (Sums details containing 'UJP')
                'Total UJP di transfer',    // Smart Static Column 1 (Sums details containing 'UJP')
                'Harga Pabrik', // Smart Static Column 2 (Sums details equaling 'HARGA PABRIK')
                'Tabungan',     // Smart Static Column 3 (Sums details equaling 'TABUNGAN')
            ];

            // Combine fixed headers with the dynamically discovered columns expanding to the right
            $headers = array_merge($staticHeaders, $dynamicPurposes);


            // date - customer - revision destination - driver - plat - (all ujp (contain ujp)) - base price factory (revision ) - tabungan - other than that, dynamic column
            // Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Transactions');

            // Output headers matrix into row 1
            $sheet->fromArray([$headers], null, 'A1');

            // Style header row
            $highestColumn = Coordinate::stringFromColumnIndex(count($headers));
            $headerStyle = $sheet->getStyle("A1:{$highestColumn}1");
            $headerStyle->getFont()->setBold(true)->setColor(new Color('FFFFFFFF'));
            $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF366092');
            $headerStyle->getAlignment()->setHorizontal('center')->setVertical('center');

            // Add data
            $row = 2;
            foreach ($transactions as $transaction) {
                // Initialize clean arithmetic math buckets for the smart static columns per row
                $totalUjp = 0;
                $tabungan = 0;

                // Key-value array tracking the extra dynamic columns specifically for this transaction row
                $dynamicRowAmounts = [];

                foreach ($transaction->transactionDetails as $detail) {
                    $purpose = strtolower(trim($detail->purpose));

                    // Ensure robust handling for both standard Eloquent string states or native Enums
                    $isDone = (strtolower($detail->status->value) === 'done' || ($detail->status?->value ?? '') === 'done');
                    $amount = floatval($detail->amount ?? 0);

                    // Calculations only aggregate if status is officially 'Done'
                    if ($isDone) {
                        if (str_contains($purpose, 'ujp')) {
                            $totalUjp += $amount;
                        } elseif (str_contains($purpose, 'tabungan')) {
                            $tabungan += $amount;
                        } else {
                            // Map everything else into its own specific dynamic category slot
                            $dynamicRowAmounts[$purpose] = ($dynamicRowAmounts[$purpose] ?? 0) + $amount;
                        }
                    }
                }

                // Write core transaction dataset and our smart static columns (Columns A through H)
                $sheet->setCellValue('A' . $row, $row-1);
                $sheet->setCellValue('B' . $row, $transaction->do_date);
                $sheet->setCellValue('C' . $row, $transaction->customer_name ?? '-');
                $sheet->setCellValue('D' . $row, $transaction->driver_name ?? '-');
                $sheet->setCellValue('E' . $row, $transaction->vehicle_plate ?? '-');
                $sheet->setCellValue('F' . $row, $transaction->revision_destination_district ?? '-');
                $sheet->setCellValue('G' . $row, $transaction->revision_trip_price_amount ?? 0);
                $sheet->setCellValue('H' . $row, $totalUjp);
                $sheet->setCellValue('I' . $row, $transaction->revision_base_price_factory ?? 0);
                $sheet->setCellValue('J' . $row, $tabungan);

                // Write dynamically mapped detail columns starting right after column J (Index 11 / Column J)
                $columnIndex = 11;
                foreach ($dynamicPurposes as $headerPurpose) {
                    $cellValue = $dynamicRowAmounts[$headerPurpose] ?? 0; // Default to 0 if transaction lacks this category

                    $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                    $sheet->setCellValue($columnLetter . $row, $cellValue);
                    $columnIndex++;
                }

                // Apply visual zebra striping for easy scanning
                if ($row % 2 === 0) {
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFF2F2F2');
                }

                $row++;
            }

            // 6. Number formatting and layout layout widths cleanups
            // Apply numeric thousands separator pattern over all calculation columns (F to the rightmost cell)
            if ($row > 2) {
                $sheet->getStyle("G2:{$highestColumn}" . ($row - 1))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
            }

            // Automatically auto-fit all active column boundaries cleanly
            foreach (range(1, count($headers)) as $index) {
                $columnLetter = Coordinate::stringFromColumnIndex($index);
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }

            // Write to storage safely using a physical system temp file
            $writer = new Xlsx($spreadsheet);
            $tempFilePath = tempnam(sys_get_temp_dir(), 'xlsx_dynamic_');
            $writer->save($tempFilePath);

            // Save file
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
