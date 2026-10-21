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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();                                                           // PK
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->restrictOnDelete();                                             // FK
            $table->foreignId('order_id')
                  ->unique()
                  ->constrained('orders')
                  ->restrictOnDelete();                                             // FK + UK (1 đơn 1 khiếu nại)
            $table->decimal('refund_amount', 12, 2)->nullable();                     // Số tiền duyệt hoàn
            $table->text('reason');                                                 // Lý do khiếu nại
            $table->string('status', 50)->default('pending')->index();              // pending|resolved|rejected
            $table->foreignId('resolved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();                                                 // FK NV CSKH/Admin duyệt
            $table->text('resolution_note')->nullable();                            // Ghi chú phản hồi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};