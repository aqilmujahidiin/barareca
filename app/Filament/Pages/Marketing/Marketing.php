<?php

namespace App\Filament\Pages\Marketing;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;

class Marketing extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $title = 'Marketing Report';
    protected static ?string $navigationLabel = 'Marketing';
    // protected static ?int $navigationSort = ;
    protected static string $view = 'filament.pages.marketing.marketing';
}