<?php

namespace App\Jobs\Import;

use Throwable;
use App\Models\ImportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use App\Imports\DataCustomerImport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessDataCustomerImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importLog;
    protected $filePath;

    public function __construct(ImportLog $importLog, string $filePath)
    {
        $this->importLog = $importLog;
        $this->filePath = $filePath;
    }

    public function handle(): void
    {
        try {
            DB::beginTransaction();

            if (!Storage::disk('public')->exists($this->filePath)) {
                throw new \Exception("File import tidak ditemukan");
            }

            // Update status to processing
            $this->importLog->update([
                'status' => ImportLog::STATUS_PROCESSING,
                'started_at' => now()
            ]);

            // Process import
            $import = new DataCustomerImport($this->importLog->id);
            Excel::import($import, Storage::disk('public')->path($this->filePath));

            // Set final status
            $status = $import->getFailureCount() > 0
                ? ImportLog::STATUS_COMPLETED_WITH_ERRORS
                : ImportLog::STATUS_COMPLETED;

            // Update final status - this will trigger observer
            $this->importLog->update([
                'status' => $status,
                'completed_at' => now()
            ]);

            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            $this->handleError($e);
            throw $e;
        } finally {
            Storage::disk('public')->delete($this->filePath);
        }
    }

    protected function handleError(Throwable $e)
    {
        Log::error('Import failed', [
            'import_log_id' => $this->importLog->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        $this->importLog->update([
            'status' => ImportLog::STATUS_FAILED,
            'error_message' => [$e->getMessage()],
            'completed_at' => now()
        ]);
    }
}