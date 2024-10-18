<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Akaunting\Money\Money;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Pages\Dashboard\Concerns\InteractsWithFiltersForm;

class CustomersOverview extends BaseWidget
{
    use InteractsWithPageFilters, HasWidgetShield;

    protected static bool $isLazy = false;
    protected static ?int $sort = -2;

    protected function getStats(): array
    {


        $query = Customer::query()
            ->when(
                $filters['division'] ?? null,
                fn($query, $divisionId) => $query->where('division_id', $divisionId)
            )
            ->when(
                $filters['startDate'] ?? null,
                fn($query, $date) => $query->whereDate('tanggal', '>=', $date)
            )
            ->when(
                $filters['endDate'] ?? null,
                fn($query, $date) => $query->whereDate('tanggal', '<=', $date)
            );

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