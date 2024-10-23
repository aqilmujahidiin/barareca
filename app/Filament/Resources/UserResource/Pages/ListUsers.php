<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Models\UserImport;
use Filament\Actions;
use App\Jobs\ProcessUserImport;
use Filament\Actions\CreateAction;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Storage;
use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\UserImportResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importUsers')
                ->label('Import Users')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Excel File Or CSV')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->maxSize(5120)
                        ->directory('temp-imports')
                        ->preserveFilenames()
                        ->required()
                        ->helperText('Upload file Excel/CSV (Max: 5MB)')
                ])
                ->requiresConfirmation()
                ->modalHeading('Import Users')
                ->modalDescription('Pastikan format file sesuai dengan template yang telah disediakan.')
                ->modalSubmitActionLabel('Mulai Import')
                ->action(function (array $data): void {
                    try {
                        $filePath = $data['attachment'];

                        // Create import record
                        $import = UserImport::create([
                            'file_name' => basename($filePath),
                            'file_path' => $filePath,
                            'status' => 'pending',
                            'user_id' => auth()->id(),
                        ]);

                        // Dispatch job
                        ProcessUserImport::dispatch($import);

                        // Show success notification with progress link
                        Notification::make()
                            ->title('Import Dijadwalkan')
                            ->success()
                            ->body('File berhasil diunggah dan sedang diproses.')
                            ->persistent()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view_imports')
                                    ->button()
                                    ->label('Lihat Import')
                                    ->url(UserImportResource::getUrl()) // Assuming you have UserImportResource
                            ])
                            ->send();

                    } catch (\Throwable $e) {
                        // Handle file cleanup if needed
                        if (isset($filePath) && Storage::exists($filePath)) {
                            Storage::delete($filePath);
                        }

                        Notification::make()
                            ->title('Import Gagal')
                            ->danger()
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->persistent()
                            ->send();

                        logger()->error('Import Error', [
                            'error' => $e->getMessage(),
                            'file' => $filePath ?? null,
                            'user' => auth()->id()
                        ]);
                    }
                }),
            CreateAction::make()
                ->icon('heroicon-o-user-plus'),
        ];
    }
}