<?php

namespace App\Filament\Resources\StatusCustomerResource\Pages;

use App\Filament\Resources\StatusCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStatusCustomer extends CreateRecord
{
    protected static string $resource = StatusCustomerResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
