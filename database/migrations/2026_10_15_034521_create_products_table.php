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
       Schema::create('products', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('category_id')
          ->constrained('categories')
          ->restrictOnDelete();
    $table->string('name', 255)->index();
    $table->string('slug', 255)->unique();                                    // UK
    $table->string('sku', 100)->nullable()->unique();                         // UK
    $table->text('description')->nullable();
    $table->decimal('price', 12, 2)->default(0)->index();
    $table->decimal('sale_price', 12, 2)->nullable()->index();
    $table->string('unit', 50)->default('Kg');
    $table->integer('stock')->default(0)->index();
    $table->integer('sold_count')->default(0);
    $table->decimal('avg_rating', 3, 2)->default(0);
    $table->string('status', 50)->default('active')->index();                 // active|hidden
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
