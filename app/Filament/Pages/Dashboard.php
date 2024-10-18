<?php

namespace App\Filament\Pages;

use App\Models\Divisi;
use App\Models\Company;
use App\Models\Product;
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
                        Select::make('companies')
                            ->label('Perusahaan')
                            ->options(Company::pluck('name', 'id'))
                            ->placeholder('Pilih Perusahaan'),
                        Select::make('divisis')
                            ->label('Divisi')
                            ->options(Divisi::pluck('name', 'id'))
                            ->placeholder('Semua Divisi'),
                        Select::make('products')
                            ->label('Product')
                            ->options(Product::pluck('name', 'id'))
                            ->placeholder('Semua Produk'),
                        DatePicker::make('startDate')
                            ->label('Tanggal Mulai'),
                        DatePicker::make('endDate')
                            ->label('Tanggal Akhir'),
                    ])
                    ->columns(5),
            ])
            ->statePath('filters');
    }
}