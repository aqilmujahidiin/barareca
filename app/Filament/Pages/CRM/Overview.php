<?php

namespace App\Filament\Pages\CRM;

use Filament\Pages\Page;

class Overview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = "Divisi CRM";
    protected static string $view = 'filament.pages.c-r-m.overview';
}
