<?php

namespace App\Jobs;

use App\Models\UserImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ProcessUserImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $import;

    public function __construct(UserImport $import)
    {
        $this->import = $import;
    }

    public function handle()
    {
        try {
            $this->import->update(['status' => 'processing']);

            $importer = new UsersImport($this->import->id);
            Excel::import($importer, Storage::disk('public')->path($this->import->file_path));

            $summary = $importer->getImportSummary();

            $this->import->update([
                'status' => 'completed',
                'processed_rows' => $summary['successful_rows'] + $summary['failed_rows'],
                'successful_rows' => $summary['successful_rows'],
                'failed_rows' => $summary['failed_rows'],
                'error_messages' => $summary['error_messages'],
                'completed_at' => now()
            ]);

            Notification::make()
                ->title('Import Selesai')
                ->success()
                ->body("Berhasil import {$summary['successful_rows']} data dari total {$this->import->processed_rows} baris.")
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view_details')
                        ->button()
                        ->label('Lihat Detail')
                    // ->url(route('import.show', $this->import->id), true)
                ])
                ->sendToDatabase($this->import->user);

        } catch (\Throwable $e) {
            $this->import->update([
                'status' => 'failed',
                'error_messages' => [$e->getMessage()],
                'completed_at' => now()
            ]);

            Notification::make()
                ->title('Import Gagal')
                ->danger()
                ->body('Terjadi kesalahan saat memproses import: ' . $e->getMessage())
                ->sendToDatabase($this->import->user);

            throw $e;
        }
    }
}