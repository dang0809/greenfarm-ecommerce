<?php

use App\Services\FarmPlotService;
use Illuminate\Support\Facades\Schedule;

// 1. Tự động hủy các đơn hàng chưa thanh toán quá 5 phút, nhả kho giữ chỗ và hoàn điểm
Schedule::command('orders:cancel-expired')
    ->everyMinute()
    ->withoutOverlapping();

// 2. Tự động quét và tạo đơn hàng cho các đợt giao rau VIP vào 06:00 sáng mỗi ngày
Schedule::command('vip:generate-orders')
    ->dailyAt('06:00')
    ->withoutOverlapping();

// 3. Tự động nâng mức giảm Flash Sale từ 30% lên 50% khi lô cận hạn (<= 1 ngày), hoặc tắt sale nếu hết hạn
Schedule::command('batches:sync-flash-sale')
    ->everyMinute() //dailyAt('00:01')
    ->withoutOverlapping();

// 4. Tự động chuyển các vụ gieo trồng sang trạng thái thu hoạch khi đến ngày tối thiểu
Schedule::call(function () {
    app(FarmPlotService::class)->syncHarvestingStatuses();
})
    ->name('sync-planting-harvesting')
    ->everyMinute()
    ->withoutOverlapping();
