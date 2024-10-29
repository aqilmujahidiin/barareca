<?php

namespace App\Imports;

use Exception;
use Carbon\Carbon;
use App\Models\DataCustomer;
use App\Models\ImportLog;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class DataCustomerImport implements
    ToModel,
    WithHeadingRow,
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
    private $rows = 0;
    private $successCount = 0;
    private $failureCount = 0;
    private $errors = [];
    private $operatorId;
    private $importLog;

    public function __construct($importLogId)
    {
        $this->importLogId = $importLogId;
        $this->importLog = ImportLog::findOrFail($importLogId);
        $this->operatorId = $this->importLog->user_id;

        // Inisialisasi status awal
        $this->importLog->update([
            'status' => ImportLog::STATUS_PROCESSING,
            'started_at' => now(),
            'total_rows' => 0,
            'success_rows' => 0,
            'failed_rows' => 0
        ]);

        Log::info('DataCustomerImport initialized', [
            'import_log_id' => $importLogId,
            'operator_id' => $this->operatorId
        ]);
    }

    public function model(array $row)
    {
        try {
            $this->rows++;

            // Debug log
            Log::info('Processing row', [
                'import_log_id' => $this->importLogId,
                'row_number' => $this->rows,
                'data' => $row
            ]);

            $date = ExcelDate::excelToDateTimeObject($row['tanggal'])->format('Y-m-d');

            $model = new DataCustomer([
                'tanggal' => $date,
                'operator_id' => $this->operatorId,
                'nama_pelanggan' => $row['nama_pelanggan'] ?? null,
                'no_telepon' => $row['no_telepon'] ?? null,
                'nama_produk' => $row['nama_produk'] ?? null,
                'quantity' => $row['quantity'] ?? null,
                'alamat_pengirim' => $row['alamat_pengirim'] ?? null,
                'id_pelacakan' => $row['id_pelacakan'] ?? null,
                'status_granular' => $row['status_granular'] ?? 'pending',
                'nama_pengirim' => $row['nama_pengirim'] ?? null,
                'kontak_pengirim' => $row['kontak_pengirim'] ?? null,
                'kode_pos_pengirim' => $row['kode_pos_pengirim'] ?? null,
                'metode_pembayaran' => $row['metode_pembayaran'] ?? 'transfer',
                'total_pembayaran' => $row['total_pembayaran'] ?? 0,
                'alamat_penerima' => $row['alamat_penerima'] ?? null,
                'alamat_penerima_2' => $row['alamat_penerima_2'] ?? null,
                'kode_pos' => $row['kode_pos'] ?? null,
                'no_invoice' => $row['no_invoice'] ?? null,
                'keterangan_promo' => $row['keterangan_promo'] ?? null,
                'keterangan_issue' => $row['keterangan_issue'] ?? null,
                'ongkos_kirim' => $row['ongkos_kirim'] ?? 0,
                'potongan_ongkos_kirim' => $row['potongan_ongkos_kirim'] ?? 0,
                'potongan_lain_1' => $row['potongan_lain_1'] ?? 0,
                'potongan_lain_2' => $row['potongan_lain_2'] ?? 0,
                'potongan_lain_3' => $row['potongan_lain_3'] ?? 0,
                'customer_service' => $row['customer_service'] ?? '',
                'advertiser' => $row['advertiser'] ?? '',
                'status_customer' => $row['status_customer'] ?? null,
                'company' => $row['company'] ?? '',
                'divisi' => $row['divisi'] ?? ''
            ]);

            $model->save();

            $this->successCount++;

            // Update progress setelah setiap baris berhasil
            $this->updateImportProgress('Row imported successfully');

            return $model;

        } catch (Exception $e) {
            $this->failureCount++;
            $this->errors[] = "Row {$this->rows}: " . $e->getMessage();

            Log::error('Error importing row', [
                'import_log_id' => $this->importLogId,
                'row_number' => $this->rows,
                'error' => $e->getMessage()
            ]);

            // Update progress saat error
            $this->updateImportProgress('Error importing row');

            throw $e;
        }
    }

    private function updateImportProgress($message = '')
    {
        try {
            $result = ImportLog::where('id', $this->importLogId)->update([
                'total_rows' => $this->rows,
                'success_rows' => $this->successCount,
                'failed_rows' => $this->failureCount,
                'error_message' => !empty($this->errors) ? $this->errors : null
            ]);

            Log::info('Import progress updated', [
                'import_log_id' => $this->importLogId,
                'total_rows' => $this->rows,
                'success_rows' => $this->successCount,
                'failed_rows' => $this->failureCount,
                'update_result' => $result,
                'message' => $message
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update import progress', [
                'import_log_id' => $this->importLogId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                Log::info('Starting import process', [
                    'import_log_id' => $this->importLogId
                ]);

                $this->importLog->update([
                    'status' => ImportLog::STATUS_PROCESSING,
                    'started_at' => now()
                ]);
            },

            AfterImport::class => function (AfterImport $event) {
                $status = $this->failureCount > 0 ? ImportLog::STATUS_COMPLETED_WITH_ERRORS : ImportLog::STATUS_COMPLETED;

                $updated = $this->importLog->update([
                    'status' => $status,
                    'total_rows' => $this->rows,
                    'success_rows' => $this->successCount,
                    'failed_rows' => $this->failureCount,
                    'error_message' => !empty($this->errors) ? $this->errors : null,
                    'completed_at' => now()
                ]);

                Log::info('Import completed', [
                    'import_log_id' => $this->importLogId,
                    'final_status' => $status,
                    'total_rows' => $this->rows,
                    'success_rows' => $this->successCount,
                    'failed_rows' => $this->failureCount,
                    'update_success' => $updated
                ]);
            }
        ];
    }

    public function onError(Throwable $e)
    {
        $this->failureCount++;
        $this->errors[] = "Row {$this->rows}: " . $e->getMessage();
        $this->updateImportProgress('Error occurred');
    }

    public function onFailure(...$failures)
    {
        foreach ($failures as $failure) {
            $this->failureCount++;
            $this->errors[] = "Row {$this->rows}: " . implode(', ', $failure->errors());
        }
        $this->updateImportProgress('Failure occurred');
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