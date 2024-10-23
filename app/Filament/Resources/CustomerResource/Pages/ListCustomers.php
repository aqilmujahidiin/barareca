<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Imports\CustomerImporter;
use App\Imports\UsersImport;
use Exception;
use Filament\Actions\ImportAction;
use Filament\Actions\Action;
use App\Jobs\ImportCustomersJob;
use App\Filament\Imports\UserImporter;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\CustomerResource;
use Maatwebsite\Excel\Excel;

class ListCustomers extends ManageRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('importUsers')
                ->label('Import User')
                ->icon('heroicon-o-plus')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Excel File Or CSV')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                            'application/csv',
                            'text/plain'
                        ])
                        ->required()
                ])
                ->action(function (array $data): void {
                    $file = public_path('storage/' . $data['attachment']);
                    $import = new UsersImport();

                    try {
                        // Proses impor data
                        Excel::import($import, $file);
                        $rowCount = $import->getRowCount();

                        // Mengirim notifikasi sukses kepada user yang login
                        Notification::make()
                            ->title('Import Berhasil')
                            ->success()
                            ->body("Data berhasil diimpor. Jumlah baris: {$rowCount}")
                            ->sendToDatabase(auth()->user()); // Menggunakan sendToDatabase
        
                    } catch (Exception $e) {
                        // Mengirim notifikasi jika terjadi kesalahan saat impor
                        Notification::make()
                            ->title('Import Gagal')
                            ->danger()
                            ->body('Terjadi kesalahan saat mengimpor data.')
                            ->sendToDatabase(auth()->user()); // Menggunakan sendToDatabase
                    }
                }),
            // ImportAction::make()
            //     ->importer(CustomerImporter::class),
            // ->csvDelimiter(';'),
            Action::make('create')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
