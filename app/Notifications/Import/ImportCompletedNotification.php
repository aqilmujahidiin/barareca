<?php

namespace App\Notifications\Import;

use App\Models\ImportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $importLog;

    public function __construct(ImportLog $importLog)
    {
        $this->importLog = $importLog;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $successRate = $this->importLog->getSuccessRateAttribute();
        $duration = $this->importLog->getDurationAttribute();

        return [
            'import_log_id' => $this->importLog->id,
            'title' => 'Import Selesai',
            'message' => "Import data customer telah selesai",
            'file_name' => $this->importLog->file_name,
            'total_rows' => $this->importLog->total_rows,
            'success_rows' => $this->importLog->success_rows,
            'failed_rows' => $this->importLog->failed_rows,
            'success_rate' => $successRate . '%',
            'duration' => $duration,
            'completed_at' => $this->importLog->completed_at->format('Y-m-d H:i:s'),
            'has_errors' => $this->importLog->failed_rows > 0,
            'error_file' => $this->importLog->error_file,
            'type' => 'import_completed'
        ];
    }
}