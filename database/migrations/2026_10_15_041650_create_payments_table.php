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
        Schema::create('payments', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('order_id')
          ->constrained('orders')
          ->cascadeOnDelete();
    $table->string('payment_code', 50)->unique();                             // UK
    $table->string('method', 50)->index();                                    // vnpay|momo
    $table->decimal('amount', 12, 2)->default(0);
    $table->string('status', 50)->default('pending')->index();                // pending|success|failed
    $table->string('transaction_code', 100)->nullable()->index();
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
