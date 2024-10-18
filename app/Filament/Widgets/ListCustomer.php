<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\DivisiReport;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class ListCustomer extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 1;
    protected static ?string $heading = "Divisi";
    protected static ?string $minHeight = '300px';
    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return DivisiReport::query()->getReport();
            })
            ->columns([
                TextColumn::make('nama_divisi')
                    ->label('Nama Divisi')
                    ->sortable(),
                TextColumn::make('total_customer')
                    ->label('Total Customer')
                    ->sortable(),
                TextColumn::make('total_quantity')
                    ->label('Total Quantity')
                    ->sortable(),
                TextColumn::make('total_omset')
                    ->label('Total Omset')
                    ->money('IDR') // Asumsikan mata uang IDR, sesuaikan jika berbeda
                    ->sortable(),
            ])
            ->defaultSort('total_omset', 'desc')
            ->striped()
            ->paginated(false);
    }
}