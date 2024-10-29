<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\DataCustomer;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class CustomersChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Trend Omset';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $endDate = $this->filters['endDate'] ?? now()->endOfDay();
        $startDate = $this->filters['startDate'] ?? now()->subMonth()->startOfDay();
        $divisiId = $this->filters['divisis'] ?? null;

        // Pastikan kita selalu memiliki rentang tanggal yang valid
        if (!$startDate || !$endDate || $startDate > $endDate) {
            $endDate = now()->endOfDay();
            $startDate = now()->subMonth()->startOfDay();
        }

        $data = DataCustomer::query()
            ->select(DB::raw('DATE(tanggal) as date'), DB::raw('SUM(total_pembayaran) as total'))
            ->when($divisiId, fn($query) => $query->where('divisi', $divisiId))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderBy('date')
            ->get();

        $dateRange = collect(Carbon::parse($startDate)->daysUntil($endDate));

        $filledData = $dateRange->map(function ($date) use ($data) {
            $matchingData = $data->firstWhere('date', $date->format('Y-m-d'));
            return [
                'date' => $date->format('d M'),
                'total' => $matchingData ? $matchingData->total : 0,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Omset',
                    'data' => $filledData->pluck('total')->toArray(),
                ],
            ],
            'labels' => $filledData->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => '(value) => "Rp " + new Intl.NumberFormat("id-ID").format(value)',
                    ],
                ],
            ],
        ];
    }
}