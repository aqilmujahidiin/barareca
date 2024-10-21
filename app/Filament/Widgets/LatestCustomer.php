<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\CustomerResource;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestCustomer extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 5;
    public function table(Table $table): Table
    {
        return $table
            ->query(
                CustomerResource::getEloquentQuery()
            )
            ->columns([
                TextColumn::make('No')
                    ->rowIndex(),
                TextColumn::make('tanggal')->date('d F Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('no_invoice'),
                TextColumn::make('operator.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nama_pelanggan'),
                TextColumn::make('product.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('statusCustomer.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('quantity'),
                TextColumn::make('metode_pembayaran')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('total_pembayaran')
                    ->label('Total Pembayaran')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('customerService.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('divisi.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('advertiser.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('company.name')
                    ->label('Company')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ongkos_kirim')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('potongan_ongkos_kirim')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('potongan_lain_1')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('potongan_lain_2')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('potongan_lain_3')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('keterangan_promo'),
            ]);
    }
}
