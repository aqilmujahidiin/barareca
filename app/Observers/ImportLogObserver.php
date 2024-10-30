<?php

namespace App\Observers;

use App\Models\ImportLog;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ImportLogObserver
{
    public function created(ImportLog $importLog)
    {
        // Ketika import log baru dibuat
        Notification::make()
            ->title('Import Dijadwalkan')
            ->success()
            ->body('File sedang diproses dalam antrian.')
            ->persistent()
            ->actions([
                \Filament\Notifications\Actions\Action::make('view_progress')
                    ->button()
                    ->label('Lihat Detail')
                    ->url(fn() => ImportLogResource::getUrl('view', ['record' => $importLog]))
            ])
            ->send();
    }

    public function updated(ImportLog $importLog)
    {
        // Log untuk debugging
        Log::info('ImportLog Updated', [
            'id' => $importLog->id,
            'status' => $importLog->status,
            'isDirty' => $importLog->isDirty('status'),
            'original' => $importLog->getOriginal('status'),
            'current' => $importLog->status,
        ]);

        // Ketika status berubah
        if ($importLog->isDirty('status')) {
            $this->handleStatusChange($importLog);
        }
    }

    protected function handleStatusChange(ImportLog $importLog)
    {
        switch ($importLog->status) {
            case ImportLog::STATUS_PROCESSING:
                Notification::make()
                    ->title('Import Sedang Diproses')
                    ->info()
                    ->body(sprintf(
                        "File: %s sedang diproses",
                        $importLog->file_name
                    ))
                    ->persistent()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view_progress')
                            ->button()
                            ->label('Lihat Progress')
                            ->url(route('filament.admin.resources.import-logs.view', ['record' => $importLog->id]))
                    ])
                    ->sendToDatabase($importLog->user);
                break;

            case ImportLog::STATUS_COMPLETED:
                // Refresh importLog untuk mendapatkan data terbaru
                $importLog->refresh();

                $notification = Notification::make()
                    ->title('Import Berhasil')
                    ->success()
                    ->body(sprintf(
                        "Total data: %d\nBerhasil: %d data\nGagal: %d data",
                        (int) $importLog->total_rows,
                        (int) $importLog->success_rows,
                        (int) $importLog->failed_rows
                    ))
                    ->persistent()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view_details')
                            ->button()
                            ->label('Lihat Detail')
                            ->url(route('filament.admin.resources.import-logs.view', ['record' => $importLog->id]))
                    ]);

                // Log untuk debugging
                \Log::info('Import Completed Notification', [
                    'import_log_id' => $importLog->id,
                    'total_rows' => $importLog->total_rows,
                    'success_rows' => $importLog->success_rows,
                    'failed_rows' => $importLog->failed_rows
                ]);

                $notification->sendToDatabase($importLog->user);
                break;

            case ImportLog::STATUS_FAILED:
                Notification::make()
                    ->title('Import Gagal')
                    ->danger()
                    ->body(sprintf(
                        "File: %s\nError: %s",
                        $importLog->file_name,
                        is_array($importLog->error_message)
                        ? implode("\n", $importLog->error_message)
                        : ($importLog->error_message ?? 'Unknown error')
                    ))
                    ->persistent()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('view_details')
                            ->button()
                            ->label('Lihat Detail Error')
                            ->url(route('filament.admin.resources.import-logs.view', ['record' => $importLog->id]))
                    ])
                    ->sendToDatabase($importLog->user);
                break;
        }
    }

    public function deleted(ImportLog $importLog)
    {
        Notification::make()
            ->title('Import Log Dihapus')
            ->warning()
            ->body("File: {$importLog->file_name} telah dihapus")
            ->persistent()
            ->sendToDatabase($importLog->user);
    }
}