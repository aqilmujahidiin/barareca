<?php

namespace App\Notifications\Import;

use App\Models\ImportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportStartedNotification extends Notification implements ShouldQueue
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
        return [
            'import_log_id' => $this->importLog->id,
            'title' => 'Import Dimulai',
            'message' => "Import data customer sedang diproses",
            'file_name' => $this->importLog->file_name,
            'started_at' => $this->importLog->started_at->format('Y-m-d H:i:s'),
            'type' => 'import_started'
        ];
    }
}