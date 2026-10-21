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
            $table->id();                                                           // PK
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();                                             // FK
            $table->string('order_code', 50)->unique();                             // UK
            $table->string('order_type', 50)->default('retail')->index();           // retail|subscription|compensation
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_distance_km', 8, 2)->nullable();
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->integer('points_used')->default(0);
            $table->integer('points_earned')->default(0);
            $table->decimal('total', 12, 2)->default(0)->index();
            $table->string('payment_method', 50)->default('vnpay')->index();        // vnpay|payos|momo
            $table->string('payment_status', 50)->default('pending')->index();      // pending|paid|failed
            $table->string('shipping_provider', 100)->nullable();
            $table->string('tracking_code', 100)->nullable()->index();
            $table->string('recipient_name', 255);
            $table->string('recipient_phone', 20);
            $table->string('recipient_address', 255);
            $table->string('status', 50)->default('pending')->index();              // pending|processing|shipped|delivered|cancelled

            // 8 trường Hủy đơn & Hoàn tiền Ngân hàng
            $table->string('cancel_reason', 255)->nullable();                       // Lý do hủy
            $table->timestamp('cancelled_at')->nullable();                          // Thời điểm hủy
            $table->string('cancelled_by', 50)->nullable();                         // customer|admin|system
            $table->string('bank_name', 100)->nullable();                           // Tên ngân hàng nhận refund
            $table->string('bank_account_number', 50)->nullable();                  // Số tài khoản
            $table->string('bank_account_name', 100)->nullable();                   // Tên chủ tài khoản (viết hoa không dấu)
            $table->string('refund_status', 50)->default('none')->index();          // none|pending|refunded
            $table->timestamp('refunded_at')->nullable();                           // Thời điểm hoàn tiền

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