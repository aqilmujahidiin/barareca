<?php

namespace App\Filament\Resources\DataCustomerResource\Pages;

use Filament\Actions;
use App\Models\ImportLog;
use Filament\Actions\Action;
use App\Imports\DataCustomerImport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Placeholder;
use App\Exports\DataCustomerTemplateExport;
use App\Filament\Resources\ImportLogResource;
use App\Jobs\Import\ProcessDataCustomerImport;
use App\Filament\Resources\DataCustomerResource;
use App\Filament\Resources\ImportLogResource\Widgets\ImportStatisticsWidget;

class ListDataCustomers extends ListRecords
{
    protected static string $resource = DataCustomerResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ImportStatisticsWidget::class,
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import Data')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Customers')
                ->modalDescription('Upload file Excel yang berisi data customers. Import akan diproses di background.')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->helperText('Format file: .xls, .xlsx (Maksimal 1MB). Status import dapat dilihat di log import.')
                        ->directory('temp/imports/data_customers')
                        ->preserveFilenames()
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        ])
                        ->maxSize(1000)
                        ->required()
                        ->downloadable()
                        ->previewable()
                ])
                ->modalSubmitActionLabel('Mulai Import')
                ->modalCancelActionLabel('Batal')
                ->action(function (array $data): void {
                    try {
                        $filePath = 'temp/imports/data_customers/' . basename($data['file']);

                        // Create ImportLog
                        $importLog = ImportLog::create([
                            'user_id' => auth()->id(),
                            'file_name' => basename($data['file']),
                            'file_path' => $filePath,
                            'status' => ImportLog::STATUS_PENDING,
                            'total_rows' => 0,
                            'success_rows' => 0,
                            'failed_rows' => 0
                        ]);

                        // Dispatch job
                        ProcessDataCustomerImport::dispatch($importLog, $filePath);

                    } catch (\Exception $e) {
                        // Update ImportLog if exists
                        if (isset($importLog)) {
                            $importLog->update([
                                'status' => ImportLog::STATUS_FAILED,
                                'error_message' => [$e->getMessage()],
                                'completed_at' => now()
                            ]);
                        }

                        // Cleanup file
                        Storage::disk('public')->delete($data['file']);
                    }
                }),

            Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(function () {
                    // Pastikan direktori temp ada
                    Storage::makeDirectory('temp');

                    return response()->streamDownload(function () {
                        $export = new DataCustomerTemplateExport();
                        Excel::store($export, 'temp/template.xlsx', 'local');

                        // Gunakan storage_path dengan Storage facade
                        $filePath = Storage::path('temp/template.xlsx');
                        readfile($filePath);

                        // Hapus file setelah didownload
                        Storage::delete('temp/template.xlsx');
                    }, 'data_customer_import_template.xlsx');
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Template Downloaded')
                        ->body('Template berhasil didownload.')
                ),

            Actions\CreateAction::make(),
        ];
    }
}