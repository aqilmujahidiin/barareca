<?php

namespace App\Jobs\Import;

use App\Exports\DataCustomerErrorExport;
use App\Models\ImportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class GenerateImportErrorReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $importLog;

    public $timeout = 1800; // 30 minutes
    public $tries = 1;

    public function __construct(ImportLog $importLog)
    {
        $this->importLog = $importLog;
    }

    public function handle()
    {
        try {
            // Generate filename untuk error report
            $filename = 'error_report_' . $this->importLog->id . '_' . now()->format('YmdHis') . '.xlsx';
            $path = 'temp/imports/data_customers/errors/' . $filename;

            // Generate error report
            Excel::store(
                new DataCustomerErrorExport($this->importLog->id),
                $path,
                'public'
            );

            // Update import log dengan file error
            $this->importLog->update([
                'error_file' => $path
            ]);

            Log::info('Error report generated', [
                'import_log_id' => $this->importLog->id,
                'error_file' => $path
            ]);

        } catch (Throwable $e) {
            Log::error('Failed to generate error report', [
                'import_log_id' => $this->importLog->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception)
    {
        Log::error('Generate error report job failed', [
            'import_log_id' => $this->importLog->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}