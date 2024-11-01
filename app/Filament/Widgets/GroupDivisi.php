<?php

namespace App\Filament\Widgets;

use App\Models\DataCustomer;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GroupDivisi extends TableWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 1;

    // Perbaikan method getTableRecordKey
    public function getTableRecordKey(Model $record): string
    {
        return $record->divisi;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DataCustomer::query()
                    ->select(
                        'divisi',
                        DB::raw('COUNT(*) as total_customer'),
                        DB::raw('SUM(quantity) as total_quantity'),
                        DB::raw('SUM(total_pembayaran) as total_omset')
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
                    ->groupBy('divisi')
            )
            ->columns([
                Tables\Columns\TextColumn::make('divisi')
                    ->label('Divisi')
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
            ->defaultSort('total_omset', 'desc');
    }
}