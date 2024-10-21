<?php

namespace App\Jobs;

use App\Imports\CustomersImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class ImportCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $userId;

    public function __construct(string $filePath, int $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        try {
            $import = new CustomersImport();
            Excel::import($import, $this->filePath);

            Notification::make()
                ->title('Import Pelanggan Berhasil')
                ->success()
                ->body("Jumlah data diimpor: {$import->getRowsImported()}\n" .
                    "Jumlah data gagal: {$import->getRowsFailed()}\n" .
                    "Range: {$import->getImportedRange()}")
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(route('filament.resources.customers.index'), shouldOpenInNewTab: true),
                ])
                ->sendToDatabase($this->userId);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Import Pelanggan Gagal')
                ->danger()
                ->body('Gagal import Excel: ' . $e->getMessage())
                ->sendToDatabase($this->userId);
        }
    }
}