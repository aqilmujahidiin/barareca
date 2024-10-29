<?php

namespace App\Filament\Widgets;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\DataCustomer;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class GroupProduk extends TableWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 3;

    public function getTableRecordKey(Model $record): string
    {
        return $record->nama_produk;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DataCustomer::query()
                    ->select(
                        'nama_produk',
                        DB::raw('COUNT(*) as total_customer'),
                        DB::raw('SUM(quantity) as total_quantity'),
                        DB::raw('SUM(total_pembayaran) as total_omset'),
                        DB::raw('ROUND(SUM(total_pembayaran)/SUM(quantity), 0) as harga_rata_rata')
                    )
                    ->when(
                        $this->filters['divisis'] ?? null,
                        fn(Builder $query, $divisi) => $query->where('divisi', $divisi)
                    )
                    ->when(
                        $this->filters['startDate'] ?? null,
                        fn(Builder $query, $date) => $query->where('tanggal', '>=', $date)
                    )
                    ->when(
                        $this->filters['endDate'] ?? null,
                        fn(Builder $query, $date) => $query->where('tanggal', '<=', $date)
                    )
                    ->groupBy('nama_produk')
            )
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

}