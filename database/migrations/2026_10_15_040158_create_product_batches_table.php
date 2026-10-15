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
        Schema::create('product_batches', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('planting_batch_id')
                  ->constrained('planting_batches')
                  ->restrictOnDelete();                                            // FK
    $table->string('batch_code', 100)->unique();                              // UK — dùng sinh QR
    $table->string('status', 50)->default('active')->index();                 // active|suspended|recalled
    $table->date('manufactured_date')->nullable()->index();
    $table->date('expiry_date')->index();                                     // INDEX FEFO — cực quan trọng
    $table->integer('initial_quantity')->default(0);
    $table->integer('current_quantity')->default(0)->index();
    $table->integer('spoiled_quantity')->default(0);
    $table->integer('discount_percent')->default(0);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
