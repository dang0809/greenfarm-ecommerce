<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Bảng này cần sau orders vì có FK order_id
Schema::create('delivery_schedules', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('subscription_id')
          ->constrained('subscriptions')
          ->cascadeOnDelete();                                                           // FK
    $table->integer('delivery_order');                                        // Đợt 1→8
    $table->date('delivery_date')->index();                                   // INDEX — Cron Job quét cột này
    $table->json('items')->nullable();                                        // JSON danh sách rau tự chọn
    $table->foreignId('order_id')
          ->nullable()
          ->constrained('orders')
          ->nullOnDelete();                                                           // FK — NULL khi chưa tới ngày
    $table->string('status', 50)->default('pending')->index();                // pending|processing|delivered
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_schedules');
    }
};
