<?php
// app/Imports/DataCustomerImport.php

namespace App\Imports;

use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Models\ImportLog;
use App\Models\DataCustomer;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class DataCustomerImport implements
    ToModel,
    WithHeadingRow,
    WithMapping,
    SkipsEmptyRows,
    WithChunkReading,
    ShouldQueue,
    WithBatchInserts,
    SkipsOnError,
    SkipsOnFailure,
    WithEvents
{
    use Importable;

    private $importLogId;
    private $operatorCache = [];
    private $successCount = 0;
    private $failureCount = 0;
    private $totalRows = 0;
    private $errors = [];
    private $importLog;
    private $rowNumber = 0;

    public function __construct($importLogId)
    {
        $this->importLogId = $importLogId;
        $this->importLog = ImportLog::findOrFail($importLogId);

        Log::info('Import initialized', [
            'class' => get_class($this),
            'import_log_id' => $this->importLogId,
            'status' => $this->importLog->status,
            'file_name' => $this->importLog->file_name
        ]);
    }

    public function model(array $row)
    {
        try {
            Log::debug('Processing row', [
                'import_log_id' => $this->importLogId,
                'row_number' => $this->rowNumber
            ]);

            $operatorId = $this->getOperatorId($row['nama_operator']);

            $model = new DataCustomer([
                'tanggal' => $this->transformDate($row['tanggal']),
                'nama_pelanggan' => $row['nama_pelanggan'],
                'no_telepon' => $row['no_telepon'],
                'nama_produk' => $row['nama_produk'],
                'quantity' => $row['quantity'] ?? null,
                'alamat_pengirim' => $row['alamat_pengirim'],
                'id_pelacakan' => $row['id_pelacakan'],
                'status_granular' => strtolower($row['status_granular']),
                'nama_pengirim' => $row['nama_pengirim'],
                'kontak_pengirim' => $row['kontak_pengirim'],
                'kode_pos_pengirim' => $row['kode_pos_pengirim'],
                'metode_pembayaran' => strtolower($row['metode_pembayaran']),
                'total_pembayaran' => $row['total_pembayaran'],
                'alamat_penerima' => $row['alamat_penerima'],
                'alamat_penerima_2' => $row['alamat_penerima_2'] ?? null,
                'kode_pos' => $row['kode_pos'],
                'no_invoice' => $row['no_invoice'],
                'keterangan_promo' => $row['keterangan_promo'] ?? null,
                'keterangan_issue' => $row['keterangan_issue'] ?? null,
                'ongkos_kirim' => $row['ongkos_kirim'] ?? 0,
                'potongan_ongkos_kirim' => $row['potongan_ongkos_kirim'] ?? 0,
                'potongan_lain_1' => $row['potongan_lain_1'] ?? 0,
                'potongan_lain_2' => $row['potongan_lain_2'] ?? 0,
                'potongan_lain_3' => $row['potongan_lain_3'] ?? 0,
                'customer_service' => $row['customer_service'],
                'advertiser' => $row['advertiser'],
                'operator_id' => $operatorId,
                'status_customer' => $row['status_customer'],
                'company' => $row['company'],
                'divisi' => $row['divisi'],
            ]);

            $this->successCount++;
            $this->updateImportProgress("Row {$this->rowNumber} imported successfully");

            Log::debug('Row processed successfully', [
                'import_log_id' => $this->importLogId,
                'row_number' => $this->rowNumber,
                'success_count' => $this->successCount
            ]);

            return $model;

        } catch (Exception $e) {
            $this->failureCount++;
            $this->errors[] = "Row {$this->rowNumber}: " . $e->getMessage();

            Log::error('Row processing failed', [
                'import_log_id' => $this->importLogId,
                'row_number' => $this->rowNumber,
                'error' => $e->getMessage()
            ]);

            $this->updateImportProgress("Error processing row {$this->rowNumber}");
            throw $e;
        }
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $this->totalRows = $this->rowNumber; // Update total rows setiap kali map dipanggil

        Log::debug('Mapping row', [
            'import_log_id' => $this->importLogId,
            'row_number' => $this->rowNumber,
            'total_rows' => $this->totalRows,
            'success_count' => $this->successCount,
            'failure_count' => $this->failureCount
        ]);

        return $row;
    }

    private function getOperatorId(string $operatorName): ?int
    {
        try {
            Log::debug('Getting operator ID', [
                'import_log_id' => $this->importLogId,
                'operator_name' => $operatorName,
                'row_number' => $this->rowNumber
            ]);

            if (empty($operatorName)) {
                throw new Exception("Nama operator tidak boleh kosong");
            }

            if (!isset($this->operatorCache[$operatorName])) {
                Log::info('Operator not in cache, querying database', [
                    'import_log_id' => $this->importLogId,
                    'operator_name' => $operatorName
                ]);

                $operator = User::query()
                    ->role('operator')
                    ->where(function ($query) use ($operatorName) {
                        $query->where('name', $operatorName)
                            ->orWhere('name', 'LIKE', "%{$operatorName}%");
                    })
                    ->first();

                if (!$operator) {
                    Log::warning('Operator not found', [
                        'import_log_id' => $this->importLogId,
                        'operator_name' => $operatorName,
                        'row_number' => $this->rowNumber
                    ]);

                    throw new Exception(
                        "Operator dengan nama '{$operatorName}' tidak ditemukan atau tidak memiliki role operator. " .
                        "Pastikan nama operator sudah benar dan memiliki role operator."
                    );
                }

                $this->operatorCache[$operatorName] = $operator->id;

                Log::info('Operator found and cached', [
                    'import_log_id' => $this->importLogId,
                    'operator_name' => $operatorName,
                    'operator_id' => $operator->id
                ]);
            }

            return $this->operatorCache[$operatorName];

        } catch (Exception $e) {
            Log::error('Error getting operator ID', [
                'import_log_id' => $this->importLogId,
                'operator_name' => $operatorName,
                'row_number' => $this->rowNumber,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    private function updateImportProgress(string $message = ''): void
    {
        try {
            $this->importLog->update([
                'total_rows' => $this->totalRows,
                'success_rows' => $this->successCount,
                'failed_rows' => $this->failureCount,
                'error_message' => !empty($this->errors) ? $this->errors : null
            ]);

            Log::info('Import progress updated', [
                'import_log_id' => $this->importLogId,
                'total_rows' => $this->totalRows,
                'success_rows' => $this->successCount,
                'failed_rows' => $this->failureCount,
                'current_row' => $this->rowNumber,
                'message' => $message
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update import progress', [
                'import_log_id' => $this->importLogId,
                'error' => $e->getMessage(),
                'total_rows' => $this->totalRows,
                'success_rows' => $this->successCount,
                'failed_rows' => $this->failureCount
            ]);
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                // Reset counters
                $this->rowNumber = 0;
                $this->totalRows = 0;
                $this->successCount = 0;
                $this->failureCount = 0;
                $this->errors = [];

                $this->importLog->update([
                    'status' => ImportLog::STATUS_PROCESSING,
                    'started_at' => now(),
                    'total_rows' => 0,
                    'success_rows' => 0,
                    'failed_rows' => 0,
                    'error_message' => null
                ]);

                Log::info('Starting import', [
                    'import_log_id' => $this->importLogId
                ]);
            },

            AfterImport::class => function (AfterImport $event) {
                $status = $this->failureCount > 0
                    ? ImportLog::STATUS_COMPLETED_WITH_ERRORS
                    : ImportLog::STATUS_COMPLETED;

                // Log before update
                Log::info('Final counts before update', [
                    'import_log_id' => $this->importLogId,
                    'total_rows' => $this->totalRows,
                    'success_rows' => $this->successCount,
                    'failed_rows' => $this->failureCount
                ]);

                $this->importLog->update([
                    'status' => $status,
                    'total_rows' => $this->totalRows,
                    'success_rows' => $this->successCount,
                    'failed_rows' => $this->failureCount,
                    'error_message' => !empty($this->errors) ? $this->errors : null,
                    'completed_at' => now()
                ]);

                // Log after update
                Log::info('Import completed', [
                    'import_log_id' => $this->importLogId,
                    'status' => $status,
                    'final_total_rows' => $this->totalRows,
                    'final_success_rows' => $this->successCount,
                    'final_failed_rows' => $this->failureCount
                ]);
            }
        ];
    }

    public function onError(Throwable $e)
    {
        $this->failureCount++;
        $this->errors[] = "Row {$this->rowNumber}: " . $e->getMessage();

        Log::error('Import row error', [
            'import_log_id' => $this->importLogId,
            'row' => $this->rowNumber,
            'error' => $e->getMessage()
        ]);
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->failureCount++;
            $this->errors[] = "Row {$failure->row()}: " . implode(', ', $failure->errors());

            Log::warning('Import row failure', [
                'import_log_id' => $this->importLogId,
                'row' => $failure->row(),
                'errors' => $failure->errors()
            ]);
        }
    }

    private function transformDate($value)
    {
        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (Exception $e) {
            throw new Exception("Format tanggal tidak valid. Gunakan format DD/MM/YYYY");
        }
    }

    public function getTotalRows(): int
    {
        return $this->totalRows;
    }
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getRowCount(): int
    {
        return $this->rowNumber;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}