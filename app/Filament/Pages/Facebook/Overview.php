<?php

namespace App\Filament\Pages\Facebook;

use Filament\Pages\Page;

class Overview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = "Divisi Facebook";
    protected static string $view = 'filament.pages.facebook.overview';
}