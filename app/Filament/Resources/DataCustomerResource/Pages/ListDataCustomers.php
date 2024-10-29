<?php

namespace App\Filament\Resources\DataCustomerResource\Pages;

use Filament\Actions;
use App\Models\ImportLog;
use Filament\Actions\Action;
use App\Imports\DataCustomerImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
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
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->directory('temp/imports/data_customers')
                        ->preserveFilenames()
                        ->acceptedFileTypes([
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        ])
                        ->maxSize(5120)
                        ->required()
                ])
                ->action(function (array $data): void {
                    try {
                        $filePath = 'temp/imports/data_customers/' . basename($data['file']);
                        // $filePath = Storage::disk('public')->path($data['file']);
        
                        // Log start process
                        Log::info('Starting import process', [
                            'file' => $data['file'],
                            'path' => $filePath,
                            'file_exists' => Storage::disk('public')->exists($filePath),
                            'user' => auth()->user()->name,
                        ]);

                        // Create ImportLog
                        $importLog = ImportLog::create([
                            'user_id' => auth()->id(),
                            'file_name' => basename($data['file']),
                            'file_path' => $filePath,  // simpan path file untuk digunakan di job
                            'status' => ImportLog::STATUS_PROCESSING,
                            'started_at' => now(),
                        ]);

                        // Dispatch job with new ImportLog
                        ProcessDataCustomerImport::dispatch($importLog, $filePath);

                        // Success notification
                        Notification::make()
                            ->title('Import Dijadwalkan')
                            ->success()
                            ->body('File sedang diproses dalam antrian')
                            ->persistent()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view_progress')
                                    ->button()
                                    ->label('Lihat Progress')
                                    ->url(fn() => ImportLogResource::getUrl('view', ['record' => $importLog]))
                            ])
                            ->send();

                    } catch (\Exception $e) {
                        // Log error
                        Log::error('Failed to start import', [
                            'file' => $data['file'] ?? 'unknown',
                            'user' => auth()->user()->name ?? 'unknown',
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);

                        // Update ImportLog if exists
                        if (isset($importLog)) {
                            $importLog->update([
                                'status' => ImportLog::STATUS_FAILED,
                                'error_message' => $e->getMessage(),
                                'completed_at' => now()
                            ]);
                        }

                        // Cleanup file
                        try {
                            Storage::disk('public')->delete($data['file']);
                            Log::info('Cleaned up failed import file', [
                                'file' => $data['file']
                            ]);
                        } catch (\Exception $deleteError) {
                            Log::warning('Failed to cleanup import file');
                        }

                        // Error notification
                        Notification::make()
                            ->title('Import Gagal Dimulai')
                            ->danger()
                            ->persistent()
                            ->body(implode("\n", [
                                "File: " . basename($data['file']),
                                "Error: " . $e->getMessage(),
                                "Time: " . now()->format('Y-m-d H:i:s')
                            ]))
                            ->sendToDatabase(auth()->user());
                        // ->send();
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }
}