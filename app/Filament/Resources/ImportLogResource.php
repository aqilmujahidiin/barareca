<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\ImportLog;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ImportLogResource\Pages;
use App\Filament\Resources\ImportLogResource\Widgets\ImportStatisticsWidget;

class ImportLogResource extends Resource
{
    protected static ?string $model = ImportLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationParentItem = 'Data Customer';
    // protected static ?string $navigationLabel = 'Import Logs';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->poll('3s')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Imported By')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('file_name')
                    ->label('File Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        ImportLog::STATUS_PROCESSING => 'warning',
                        ImportLog::STATUS_COMPLETED => 'success',
                        ImportLog::STATUS_FAILED => 'danger',
                        ImportLog::STATUS_COMPLETED_WITH_ERRORS => 'warning',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('total_rows')
                    ->label('Total')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('success_rows')
                    ->label('Success')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('failed_rows')
                    ->label('Failed')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable(),
                // Menambahkan kolom durasi dengan pengecekan null
                Tables\Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->formatStateUsing(function (?Model $record): ?string {
                        if (!$record || !$record instanceof ImportLog) {
                            return null;
                        }
                        return $record->duration;
                    })
                    ->visible(function (?Model $record): bool {
                        if (!$record || !$record instanceof ImportLog) {
                            return false;
                        }
                        return $record->status === ImportLog::STATUS_PROCESSING;
                    }),
                // Menambahkan kolom success rate dengan pengecekan null
                Tables\Columns\TextColumn::make('success_rate')
                    ->label('Success Rate')
                    ->formatStateUsing(function (?Model $record): ?string {
                        if (!$record || !$record instanceof ImportLog) {
                            return null;
                        }
                        return $record->success_rate . '%';
                    })
                    ->color(function (?Model $record): string {
                        if (!$record || !$record instanceof ImportLog) {
                            return 'secondary';
                        }
                        return match (true) {
                            $record->success_rate >= 90 => 'success',
                            $record->success_rate >= 70 => 'warning',
                            default => 'danger',
                        };
                    })
                    ->alignRight(),
            ])
            ->defaultSort('started_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        ImportLog::STATUS_PROCESSING => 'Processing',
                        ImportLog::STATUS_COMPLETED => 'Completed',
                        ImportLog::STATUS_FAILED => 'Failed',
                        ImportLog::STATUS_COMPLETED_WITH_ERRORS => 'Completed with Errors',
                    ]),
                Tables\Filters\Filter::make('started_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('started_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('started_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('download_errors')
                    ->label('Download Errors')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('danger')
                    ->url(fn(ImportLog $record) => $record->error_file ? Storage::url($record->error_file) : null)
                    ->openUrlInNewTab()
                    ->visible(fn(ImportLog $record) => $record->failed_rows > 0 && $record->error_file)
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImportLogs::route('/'),
            'view' => Pages\ViewImportLog::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            ImportStatisticsWidget::class,
        ];
    }

    public static function getUrl(string $name = 'index', array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null): string
    {
        return match ($name) {
            'view' => route('filament.admin.resources.import-logs.view', $parameters, $isAbsolute),
            default => parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant)
        };
    }
}