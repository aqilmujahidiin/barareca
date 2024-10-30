<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\ImportLog;
use Filament\Support\Assets\Js;
use App\Observers\ImportLogObserver;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentAsset;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        setlocale(LC_TIME, 'id_ID.utf8');
        Carbon::setLocale('id');
        ImportLog::observe(ImportLogObserver::class);
    }
}
