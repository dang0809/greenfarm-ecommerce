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
        Schema::create('orders', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('user_id')
          ->constrained('users')
          ->restrictOnDelete();                                                           // FK
    $table->string('order_code', 50)->unique();                               // UK
    $table->string('order_type', 50)->default('retail')->index();             // retail|subscription|compensation
    $table->decimal('subtotal', 12, 2)->default(0);
    $table->decimal('shipping_distance_km', 8, 2)->nullable();
    $table->decimal('shipping_fee', 12, 2)->default(0);
    $table->integer('points_used')->default(0);
    $table->integer('points_earned')->default(0);
    $table->decimal('total', 12, 2)->default(0)->index();
    $table->string('payment_method', 50)->default('cod')->index();            // cod|vnpay|momo
    $table->string('payment_status', 50)->default('pending')->index();        // pending|paid|failed
    $table->string('shipping_provider', 100)->nullable();
    $table->string('tracking_code', 100)->nullable()->index();
    $table->string('recipient_name', 255);
    $table->string('recipient_phone', 20);
    $table->string('recipient_address', 255);
    $table->string('status', 50)->default('pending')->index();                // pending|processing|shipped|delivered|cancelled
    $table->text('note')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
