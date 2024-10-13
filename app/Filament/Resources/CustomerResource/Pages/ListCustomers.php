<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Imports\CustomerImporter;
use App\Filament\Resources\CustomerResource;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->label('Import Customer')
                ->icon('heroicon-o-arrow-up-tray')
                ->importer(CustomerImporter::class)
                ->color('primary'),
            Actions\CreateAction::make()
                ->label('New Customer')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}