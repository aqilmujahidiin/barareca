<?php

namespace App\Filament\Resources\MarketingResource\Pages;

use App\Filament\Imports\MarketingImporter;
use App\Filament\Resources\MarketingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketings extends ListRecords
{
    protected static string $resource = MarketingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->label('Import Sales')
                ->icon('heroicon-o-arrow-up-tray')
                ->importer(MarketingImporter::class)
                ->color('primary'),
            Actions\CreateAction::make()
                ->label('Add Sales')
                ->icon('heroicon-o-plus'),
        ];
    }
}
