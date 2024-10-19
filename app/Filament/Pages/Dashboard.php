<?php

namespace App\Filament\Pages;

use App\Models\Divisi;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('divisis')  // Changed from 'divisis' to 'division'
                            ->label('Divisi')
                            ->options(Divisi::pluck('name', 'id'))
                            ->placeholder('Semua Divisi'),
                        DatePicker::make('startDate')
                            ->label('Tanggal Mulai'),
                        DatePicker::make('endDate')
                            ->label('Tanggal Akhir'),
                    ])
                    ->columns(3),
            ]);
    }
}