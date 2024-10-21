<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use App\Imports\CustomersImport;
use App\Jobs\ImportCustomersJob;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\CustomerResource;
use Filament\Forms\Components\FileUpload;  // Tambahkan import ini

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import Customers')
                ->icon('heroicon-o-arrow-up-tray')
                ->action(function (array $data) {
                    $file = $data['file'];

                    // Log informasi file
                    \Log::info("Attempting to import file: " . $file);

                    // Cek apakah file ada
                    if (!Storage::disk('public')->exists($file)) {
                        \Log::error("File not found: " . $file);
                        Notification::make()
                            ->title('Import gagal')
                            ->body("File tidak ditemukan: $file")
                            ->danger()
                            ->send();
                        return;
                    }

                    // Dapatkan full path file
                    $fullPath = Storage::disk('public')->path($file);
                    \Log::info("Full path of file: " . $fullPath);

                    try {
                        $import = new CustomersImport();
                        Excel::import($import, $fullPath);
                        // ImportCustomersJob::dispatch($fullPath, auth()->id());
        
                        Notification::make()
                            ->title('Import berhasil')
                            // ->body("Jumlah data diimpor: {$import->getRowCount()}\nRange: {$import->getImportedRange()}")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Log::error("Import failed: " . $e->getMessage());
                        Notification::make()
                            ->title('Import gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }

                    // Opsional: Hapus file setelah import
                    Storage::disk('public')->delete($file);
                })
                ->form([
                    FileUpload::make('file')
                        ->label('Excel File')
                        ->disk('public')
                        ->directory('imports')
                        ->visibility('public')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required(),
                ]),
            Actions\CreateAction::make()
                ->icon('heroicon-o-user-plus'),
        ];

    }
}