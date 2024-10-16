<?php

namespace App\Filament\Resources\StatusCustomerResource\Pages;

use App\Filament\Resources\StatusCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStatusCustomer extends EditRecord
{
    protected static string $resource = StatusCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
