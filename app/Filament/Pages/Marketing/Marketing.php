<?php

namespace App\Filament\Pages\Marketing;

use Filament\Pages\Page;

class Marketing extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $title = "Marketing Report";
    protected static string $view = 'filament.pages.marketing.marketing';
}
