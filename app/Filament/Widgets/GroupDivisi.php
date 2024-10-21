<?php
namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GroupDivisi extends TableWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Customer::query()
                    ->select(
                        'divisis.id as id',
                        'divisis.name as nama_divisi',
                        DB::raw('COUNT(DISTINCT customers.id) as total_customer'),
                        DB::raw('SUM(customers.quantity) as total_quantity'),
                        DB::raw('SUM(customers.total_pembayaran) as total_omset')
                    )
                    ->join('divisis', 'customers.divisi_id', '=', 'divisis.id')
                    ->when(
                        $this->filters['divisis'] ?? null,
                        fn(Builder $query, $divisiId) => $query->where('divisis.id', $divisiId)
                    )
                    ->when(
                        $this->filters['startDate'] ?? null,
                        fn(Builder $query, $date) => $query->where('customers.tanggal', '>=', $date)
                    )
                    ->when(
                        $this->filters['endDate'] ?? null,
                        fn(Builder $query, $date) => $query->where('customers.tanggal', '<=', $date)
                    )
                    ->groupBy('divisis.id', 'divisis.name')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama_divisi')
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