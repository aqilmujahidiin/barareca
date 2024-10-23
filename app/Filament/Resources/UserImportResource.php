<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\UserImport;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\BadgeColumn;
use App\Filament\Resources\UserImportResource\Pages;

class UserImportResource extends Resource
{
    protected static ?string $model = UserImport::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?string $pluralModelLabel = 'User Imports Log';

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('file_name')
                    ->label('File Name')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'failed',
                        'warning' => 'pending',
                        'primary' => 'processing',
                        'success' => 'completed',
                    ]),

                TextColumn::make('processed_rows')
                    ->label('Processed')
                    ->numeric(),

                TextColumn::make('successful_rows')
                    ->label('Success')
                    ->numeric(),

                TextColumn::make('failed_rows')
                    ->label('Failed')
                    ->numeric(),

                TextColumn::make('user.name')
                    ->label('Imported By'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(UserImport $record): string => Storage::url($record->file_path))
                    ->openUrlInNewTab(),

                Action::make('view_errors')
                    ->icon('heroicon-o-exclamation-circle')
                    ->visible(fn(UserImport $record): bool => $record->failed_rows > 0)
                    ->modalContent(fn(UserImport $record): string => view('filament.resources.user-import.error-modal', ['import' => $record]))
                    ->modalWidth('lg'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserImports::route('/'),
        ];
    }
}