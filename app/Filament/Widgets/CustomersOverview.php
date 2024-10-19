<?php

namespace App\Filament\Widgets;

use Akaunting\Money\Money;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class CustomersOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $division = $this->filters['divisis'] ?? null;
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        $query = Customer::query()
            ->when($division, fn(Builder $query) => $query->where('divisi_id', $division))
            ->when($startDate, fn(Builder $query) => $query->whereDate('tanggal', '>=', $startDate))
            ->when($endDate, fn(Builder $query) => $query->whereDate('tanggal', '<=', $endDate));

        return [
            Stat::make(
                'Total revenue',
                Money::IDR($query->sum('total_pembayaran'))
            ),
            Stat::make(
                'Total Customer',
                $query->count()
            ),
            Stat::make(
                'Total Quantity',
                $query->sum('quantity')
            )
        ];
    }
}