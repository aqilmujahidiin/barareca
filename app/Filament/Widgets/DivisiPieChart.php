<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class DivisiPieChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Distribusi Omset per Divisi';
    protected static ?string $maxHeight = '300px';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;
        $selectedDivisiId = $this->filters['divisis'] ?? null;

        $query = Customer::query()
            ->join('divisis', 'customers.divisi_id', '=', 'divisis.id')
            ->when($startDate, fn($q) => $q->where('customers.tanggal', '>=', $startDate))
            ->when($endDate, fn($q) => $q->where('customers.tanggal', '<=', $endDate))
            ->when($selectedDivisiId, fn($q) => $q->where('divisis.id', $selectedDivisiId))
            ->groupBy('divisis.id', 'divisis.name')
            ->select('divisis.name', DB::raw('SUM(customers.total_pembayaran) as total_omset'))
            ->orderByDesc('total_omset');

        $data = $query->get();

        $total = $data->sum('total_omset');

        $formattedData = $data->map(function ($item) use ($total) {
            $percentage = $total > 0 ? round(($item->total_omset / $total) * 100, 2) : 0;
            return [
                'name' => $item->name,
                'value' => $item->total_omset,
                'percentage' => $percentage
            ];
        });

        return [
            'datasets' => [
                [
                    'data' => $formattedData->pluck('value')->toArray(),
                    'backgroundColor' => $this->getColors($formattedData->count()),
                ],
            ],
            'labels' => $formattedData->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
                'datalabels' => [
                    'color' => '#ffffff',
                    'font' => [
                        'weight' => 'bold',
                    ],
                    'formatter' => "function(value, context) {
                        var dataset = context.chart.data.datasets[0];
                        var total = dataset.data.reduce((acc, data) => acc + data, 0);
                        var percentage = ((value / total) * 100).toFixed(2) + '%';
                        return percentage;
                    }",
                ],
            ],
        ];
    }

    private function getColors(int $count): array
    {
        $colors = [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#FF6384',
            '#C9CBCF',
            '#7BC8A4',
            '#E7E9ED'
        ];

        return array_slice($colors, 0, $count);
    }
}