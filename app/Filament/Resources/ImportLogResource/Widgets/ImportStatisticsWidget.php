<?php

namespace App\Filament\Resources\ImportLogResource\Widgets;

use App\Models\ImportLog;
use App\Models\DataCustomer;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\Cache;

class ImportStatisticsWidget extends BaseWidget
{
    // Interval polling default untuk admin dan operator
    protected function getPollingInterval(): ?string
    {
        if (auth()->user()->hasRole(['admin', 'operator'])) {
            return '3s';
        }
        return null;
    }

    protected function getStats(): array
    {
        return [
            ...$this->getCurrentImportStats(),
            ...$this->getCustomerStats(),
            ...$this->getStatusStats(),
            ...$this->getTodayStats(),
        ];
    }

    protected function getCurrentImportStats(): array
    {
        $stats = [];
        $latestImport = $this->getLatestImport();

        // Jika ada import yang sedang berjalan
        if ($latestImport && $latestImport->status === ImportLog::STATUS_PROCESSING) {
            $stats[] = Stat::make('Current Import', $latestImport->file_name)
                ->description($latestImport->duration ?? 'Just started')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning')
                ->extraAttributes(['class' => 'animate-pulse']);

            // Jika ada progress (success_rows vs total_rows)
            if ($latestImport->total_rows > 0) {
                $progress = round(($latestImport->success_rows / $latestImport->total_rows) * 100, 2);
                $stats[] = Stat::make('Import Progress', $progress . '%')
                    ->description("{$latestImport->success_rows} of {$latestImport->total_rows} rows")
                    ->descriptionIcon('heroicon-m-chart-bar')
                    ->color('info');
            }
        }

        return $stats;
    }

    protected function getCustomerStats(): array
    {
        $latestImport = $this->getLatestImport();

        // Get customers count before latest import
        $previousCustomersCount = 0;
        if ($latestImport) {
            $previousCustomersCount = Cache::remember(
                'previous_customers_count_' . $latestImport->id,
                now()->addMinutes(5),
                fn() => DataCustomer::where('created_at', '<', $latestImport->created_at)->count()
            );
        }

        // Get current total customers
        $currentCustomersCount = DataCustomer::count();
        $newCustomersCount = $currentCustomersCount - $previousCustomersCount;

        return [
            Stat::make('Previous Customers', number_format($previousCustomersCount))
                ->description('Before latest import')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('New Customers', number_format($newCustomersCount))
                ->description('From latest import')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('success'),

            Stat::make('Total Customers', number_format($currentCustomersCount))
                ->description('Current total')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }

    protected function getStatusStats(): array
    {
        $query = $this->getBaseQuery();

        $statusCounts = [
            'processing' => $query->clone()->where('status', ImportLog::STATUS_PROCESSING)->count(),
            'completed' => $query->clone()->where('status', ImportLog::STATUS_COMPLETED)->count(),
            'failed' => $query->clone()->where('status', ImportLog::STATUS_FAILED)->count(),
            'completed_with_errors' => $query->clone()->where('status', ImportLog::STATUS_COMPLETED_WITH_ERRORS)->count(),
        ];

        $stats = [];

        // Jika ada yang butuh perhatian (failed atau completed with errors)
        $needsAttention = $statusCounts['failed'] + $statusCounts['completed_with_errors'];
        if ($needsAttention > 0) {
            $stats[] = Stat::make('Needs Attention', $needsAttention)
                ->description('Failed or has errors')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger');
        }

        // Success Rate keseluruhan
        $successRate = $this->calculateSuccessRate($query);
        $stats[] = Stat::make('Overall Success Rate', $successRate . '%')
            ->description('All time average')
            ->descriptionIcon('heroicon-m-chart-bar')
            ->color($this->getSuccessRateColor($successRate));

        return $stats;
    }

    protected function getTodayStats(): array
    {
        $query = $this->getBaseQuery()->whereDate('created_at', today());

        $todayStats = $query->selectRaw('
            COUNT(*) as total_imports,
            SUM(success_rows) as success_rows,
            SUM(total_rows) as total_rows,
            SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as processing_count
        ', [ImportLog::STATUS_PROCESSING])
            ->first();

        if ($todayStats->total_imports > 0) {
            $successRate = $todayStats->total_rows > 0
                ? round(($todayStats->success_rows / $todayStats->total_rows) * 100, 2)
                : 0;

            return [
                Stat::make("Today's Imports", $todayStats->total_imports)
                    ->description($todayStats->processing_count . ' in progress')
                    ->descriptionIcon('heroicon-m-calendar')
                    ->color('primary'),

                Stat::make("Today's Success Rate", $successRate . '%')
                    ->description("From {$todayStats->total_imports} imports")
                    ->descriptionIcon('heroicon-m-chart-bar')
                    ->color($this->getSuccessRateColor($successRate)),
            ];
        }

        return [];
    }

    protected function getLatestImport()
    {
        return Cache::remember('latest_import_' . auth()->id(), 2, function () {
            return $this->getBaseQuery()
                ->latest('created_at')
                ->first();
        });
    }

    protected function getBaseQuery()
    {
        return ImportLog::query()
            ->when(
                !auth()->user()->hasRole('admin'),
                fn($query) => $query->where('user_id', auth()->id())
            );
    }

    protected function calculateSuccessRate($query): float
    {
        $stats = $query->selectRaw('
            SUM(success_rows) as total_success,
            SUM(total_rows) as total_rows
        ')->first();

        if (!$stats->total_rows) {
            return 0;
        }

        return round(($stats->total_success / $stats->total_rows) * 100, 2);
    }

    private function getSuccessRateColor(float $rate): string
    {
        return match (true) {
            $rate >= 90 => 'success',
            $rate >= 70 => 'warning',
            default => 'danger'
        };
    }
}