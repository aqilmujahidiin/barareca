<?php

namespace App\Filament\Resources\UserImportResource\Pages;

use App\Filament\Resources\UserImportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserImport extends EditRecord
{
    protected static string $resource = UserImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
