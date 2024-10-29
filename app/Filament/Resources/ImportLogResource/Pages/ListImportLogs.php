<?php

namespace App\Filament\Resources\ImportLogResource\Pages;

use App\Filament\Resources\ImportLogResource;
use App\Filament\Resources\ImportLogResource\Widgets\ImportStatisticsWidget;  // Tambahkan import ini
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListImportLogs extends ListRecords
{
    protected static string $resource = ImportLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->when(
                !auth()->user()->hasRole('admin'),
                fn(Builder $query) => $query->where('user_id', auth()->id())
            )
            ->latest('started_at');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ImportLogResource::getWidgets()[0],
        ];
    }
}