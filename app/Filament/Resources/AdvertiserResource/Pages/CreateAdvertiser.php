<?php

namespace App\Filament\Resources\AdvertiserResource\Pages;

use App\Filament\Resources\AdvertiserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAdvertiser extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected static string $resource = AdvertiserResource::class;
}
