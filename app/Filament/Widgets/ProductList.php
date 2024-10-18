<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use App\Models\DivisiReport; // Pastikan nama model ini benar

class ProductList extends BaseWidget
{
    use HasWidgetShield;
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Laporan Divisi dan Produk';
    protected static ?string $minHeight = '300px';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('nama_produk')
                    ->label('Nama Produk')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_customer')
                    ->label('Total Customer')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Total Quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_omset')
                    ->label('Total Omset')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('total_omset', 'desc')
            ->striped();
    }

    protected function getTableQuery(): Builder
    {
        return DivisiReport::query()->getProductGroupReport();
    }

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return 'composite_key';
    }
}