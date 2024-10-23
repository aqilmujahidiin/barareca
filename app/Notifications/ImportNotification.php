<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ImportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $rowCount;

    public function __construct($rowCount)
    {
        $this->rowCount = $rowCount;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Import Berhasil',
            'message' => "Data berhasil diimpor. Jumlah baris: {$this->rowCount}",
        ];
    }
}
