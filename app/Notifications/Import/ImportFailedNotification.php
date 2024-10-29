<?php

namespace App\Notifications\Import;

use App\Models\ImportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $importLog;
    protected $exception;

    public function __construct(ImportLog $importLog, \Exception $exception = null)
    {
        $this->importLog = $importLog;
        $this->exception = $exception;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'import_log_id' => $this->importLog->id,
            'title' => 'Import Gagal',
            'message' => "Import data customer gagal diproses",
            'file_name' => $this->importLog->file_name,
            'error_message' => $this->importLog->error_message,
            'error_detail' => $this->exception ? $this->exception->getMessage() : null,
            'failed_at' => $this->importLog->completed_at->format('Y-m-d H:i:s'),
            'type' => 'import_failed',
            // Informasi tambahan yang mungkin berguna untuk debugging
            'rows_processed' => $this->importLog->total_rows,
            'success_before_fail' => $this->importLog->success_rows,
            'failed_rows' => $this->importLog->failed_rows,
        ];
    }
}