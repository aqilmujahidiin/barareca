<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Akaunting\Money\Money;
use App\Models\DataCustomer;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class DataCustomerOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static bool $isLazy = false;
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        // Query dasar
        $query = DataCustomer::query();

        // Terapkan filter
        $query->when(
            $this->filters['divisis'] ?? null,
            fn(Builder $q, $divisi) => $q->where('divisi', $divisi)
        );

        $query->when(
            $this->filters['startDate'] ?? null,
            fn(Builder $q, $date) => $q->whereDate('tanggal', '>=', $date)
        );

        $query->when(
            $this->filters['endDate'] ?? null,
            fn(Builder $q, $date) => $q->whereDate('tanggal', '<=', $date)
        );

        // Ambil data untuk chart (7 hari terakhir)
        $revenueChart = $query->clone()
            ->select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('SUM(total_pembayaran) as total')
            )
            ->whereBetween('tanggal', [now()->subDays(7), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->toArray();

        $customerChart = $query->clone()
            ->select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('tanggal', [now()->subDays(7), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->toArray();

        $quantityChart = $query->clone()
            ->select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('SUM(quantity) as total')
            )
            ->whereBetween('tanggal', [now()->subDays(7), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->toArray();

        $upSellingChart = $query->clone()
            ->select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw('SUM(quantity) / COUNT(DISTINCT id_pelacakan) as total')
            )
            ->whereBetween('tanggal', [now()->subDays(7), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->toArray();

        // Hitung total statistik
        $totalRevenue = $query->sum('total_pembayaran');
        $totalCustomers = $query->count();
        $totalQuantity = $query->sum('quantity');
        $totalUniqueTrackingIds = $query->distinct('id_pelacakan')->count('id_pelacakan');
        $upSellingValue = $totalUniqueTrackingIds > 0 ?
            round($totalQuantity / $totalUniqueTrackingIds, 2) : 0;

        return [
            Stat::make(
                'Total Revenue',
                'Rp ' . number_format($totalRevenue, 0, ',', '.')
            )
                ->description('Total pendapatan dari semua transaksi')
                ->chart($revenueChart)
                ->color('success'),

            Stat::make(
                'Total Customer',
                number_format($totalCustomers, 0, ',', '.')
            )
                ->description('Jumlah total customer')
                ->chart($customerChart)
                ->color('info'),

            Stat::make(
                'Total Quantity',
                number_format($totalQuantity, 0, ',', '.')
            )
                ->description('Total kuantitas produk')
                ->chart($quantityChart)
                ->color('warning'),

            Stat::make(
                'Up Selling',
                number_format($upSellingValue, 2, ',', '.')
            )
                ->description('Rata-rata quantity per customer')
                ->chart($upSellingChart)
                ->color('warning'),
        ];
    }

    // Opsional: Method untuk mengambil data chart dengan periode custom
    protected function getChartData($query, $column, $aggregate = 'SUM')
    {
        $startDate = $this->filters['startDate'] ?? now()->subDays(7);
        $endDate = $this->filters['endDate'] ?? now();

        return $query->clone()
            ->select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw("$aggregate($column) as total")
            )
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->toArray();
    }
}