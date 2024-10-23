<?php

namespace App\Filament\Resources\UserImportResource\Pages;

use App\Filament\Resources\UserImportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserImports extends ListRecords
{
    protected static string $resource = UserImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
