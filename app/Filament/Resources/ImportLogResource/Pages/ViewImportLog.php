<?php

namespace App\Filament\Resources\ImportLogResource\Pages;

use App\Filament\Resources\ImportLogResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms;

class ViewImportLog extends ViewRecord
{
    protected static string $resource = ImportLogResource::class;

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make('Import Information')
                ->schema([
                    TextInput::make('file_name')
                        ->label('File Name')
                        ->disabled(),
                    TextInput::make('user.name')
                        ->label('Imported By')
                        ->disabled(),
                    TextInput::make('status')
                        ->disabled(),
                    DateTimePicker::make('started_at')
                        ->disabled(),
                    DateTimePicker::make('completed_at')
                        ->disabled(),
                ])->columns(2),

            Section::make('Statistics')
                ->schema([
                    TextInput::make('total_rows')
                        ->label('Total Rows')
                        ->disabled(),
                    TextInput::make('success_rows')
                        ->label('Successful Rows')
                        ->disabled(),
                    TextInput::make('failed_rows')
                        ->label('Failed Rows')
                        ->disabled(),
                    TextInput::make('success_rate')
                        ->label('Success Rate')
                        ->disabled()
                        ->formatStateUsing(fn($record) => $record->getSuccessRateAttribute() . '%'),
                    TextInput::make('duration')
                        ->label('Duration')
                        ->disabled()
                        ->formatStateUsing(fn($record) => $record->getDurationAttribute()),
                ])->columns(2),

            Section::make('Error Details')
                ->schema([
                    Forms\Components\Textarea::make('error_message')
                        ->label('Error Message')
                        ->disabled()
                        ->visible(fn($record) => !empty ($record->error_message)),
                ])->visible(fn($record) => $record->status === 'failed')
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_errors')
                ->label('Download Error Report')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('danger')
                ->url(fn() => $this->record->error_file ? Storage::url($this->record->error_file) : null)
                ->openUrlInNewTab()
                ->visible(fn() => $this->record->failed_rows > 0 && $this->record->error_file),

            Action::make('back')
                ->label('Back to List')
                ->url(fn() => ImportLogResource::getUrl())
                ->color('gray'),
        ];
    }
}