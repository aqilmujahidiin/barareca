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
use Filament\Notifications\Notification;
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

    public function handle()
    {
        try {
            DB::beginTransaction();

            if (!Storage::disk('public')->exists($this->filePath)) {
                throw new \Exception("File import tidak ditemukan");
            }

            // Initialize import log
            $this->importLog->update([
                'started_at' => now(),
                'status' => ImportLog::STATUS_PROCESSING
            ]);

            // Process import
            Excel::import(
                new DataCustomerImport($this->importLog->id),
                Storage::disk('public')->path($this->filePath)
            );

            // Refresh import log data
            $this->importLog->refresh();

            // Send notification
            $this->sendSuccessNotification();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->handleError($e);
            throw $e;
        } finally {
            $this->cleanup();
        }
    }

    protected function sendSuccessNotification()
    {
        // Refresh the import log to get latest data
        $this->importLog->refresh();

        Log::info('Sending success notification', [
            'import_log_id' => $this->importLog->id,
            'total_rows' => $this->importLog->total_rows,
            'success_rows' => $this->importLog->success_rows,
            'failed_rows' => $this->importLog->failed_rows,
            'success_rate' => $this->importLog->success_rate,
            'duration' => $this->importLog->duration
        ]);

        $status = $this->importLog->failed_rows > 0 ?
            ImportLog::STATUS_COMPLETED_WITH_ERRORS :
            ImportLog::STATUS_COMPLETED;

        // Format message
        $message = sprintf(
            "Berhasil import %d dari %d data (%0.2f%% sukses).%s%s",
            (int) $this->importLog->success_rows,
            (int) $this->importLog->total_rows,
            (float) $this->importLog->success_rate,
            $this->importLog->failed_rows > 0 ? " Gagal: {$this->importLog->failed_rows} data." : "",
            $this->importLog->duration ? "\nWaktu proses: {$this->importLog->duration}" : ""
        );

        // Create notification
        $notification = Notification::make()
            ->title('Import Selesai')
            ->body($message);

        // Set notification status
        if ($status === ImportLog::STATUS_COMPLETED) {
            $notification->success();
        } elseif ($status === ImportLog::STATUS_COMPLETED_WITH_ERRORS) {
            $notification->warning();
        }

        // Add action buttons
        $notification->actions([
            \Filament\Notifications\Actions\Action::make('view_details')
                ->button()
                ->label('Lihat Detail')
                ->url(route('filament.admin.resources.import-logs.view', ['record' => $this->importLog->id]))
        ]);

        // Send notification
        $notification->sendToDatabase($this->importLog->user);
    }

    protected function handleError(Throwable $e)
    {
        Log::error('Import failed', [
            'import_log_id' => $this->importLog->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        DB::transaction(function () use ($e) {
            $this->importLog->update([
                'status' => ImportLog::STATUS_FAILED,
                'error_message' => [$e->getMessage()],
                'completed_at' => now()
            ]);

            $message = sprintf(
                "Import gagal setelah memproses %d data.\nError: %s",
                (int) $this->importLog->total_rows,
                $e->getMessage()
            );

            if ($this->importLog->duration) {
                $message .= "\nWaktu proses: {$this->importLog->duration}";
            }

            Notification::make()
                ->title('Import Gagal')
                ->danger()
                ->body($message)
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view_details')
                        ->button()
                        ->label('Lihat Detail Error')
                        ->url(route('filament.admin.resources.import-logs.view', ['record' => $this->importLog->id]))
                ])
                ->sendToDatabase($this->importLog->user);
        });
    }

    protected function cleanup()
    {
        try {
            Storage::disk('public')->delete($this->filePath);
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup import file', [
                'file_path' => $this->filePath,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function failed(Throwable $e)
    {
        $this->handleError($e);
    }
}