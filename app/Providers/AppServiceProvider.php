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
            $contact = SystemSetting::contactSettings();

            $view->with('farmName', SystemSetting::farmName());
            $view->with('hotline', $contact['hotline'] ?? '0987.654.321');
            $view->with('farmEmail', $contact['email'] ?? 'lienhe@datlanhfarm.vn');
            $view->with('farmAddress', $contact['farm_address'] ?? '');
            $view->with('vipPackageName', SystemSetting::vipPackageName());
            $view->with('vipDeliveryMaxWeight', SystemSetting::vipDeliveryMaxWeight());
        });
    }
}