<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Customer;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CustomersChart extends ChartWidget
{
    use InteractsWithPageFilters, HasWidgetShield;
    protected static ?string $heading = 'Trend Omset';
    // protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';
    protected function getData(): array
    {

        $start = $this->filters['startDate'];
        $end = $this->filters['endDate'];

        $data = Trend::model(Customer::class)
            ->between(
                start: $start ? Carbon::parse($start) : now()->subMonth(),
                end: $end ? Carbon::parse($end) : now(),
            )
            ->perDay()
            ->sum('total_pembayaran');

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
