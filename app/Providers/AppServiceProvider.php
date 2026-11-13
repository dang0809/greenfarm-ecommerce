<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Cung cấp tên nông trại hiện tại cho các view
     */
    public function boot(): void
    {
        View::composer('*', function (\Illuminate\View\View $view): void {
            $view->with('farmName', SystemSetting::farmName());
        });
    }
}