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
       Schema::create('reviews', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('user_id')
          ->constrained('users')
          ->restrictOnDelete();
    $table->foreignId('product_id')
          ->constrained('products')
          ->restrictOnDelete();
    $table->foreignId('order_item_id')
          ->unique()
          ->constrained('order_items')
          ->cascadeOnDelete();                                                 // FK + UK (1 lần mua = 1 review)
    $table->tinyInteger('rating')->default(5)->index();                       // 1→5 sao
    $table->text('content')->nullable();
    $table->string('status', 50)->default('approved')->index();               // approved|hidden
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
