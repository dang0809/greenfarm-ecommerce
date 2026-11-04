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
        Schema::create('inventory_logs', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('batch_id')
          ->constrained('product_batches')
          ->cascadeOnDelete();
    $table->string('type', 50)->index();                                      // import|export_sale|export_spoilage
    $table->integer('quantity');
    $table->text('note')->nullable();
    $table->foreignId('created_by')
          ->constrained('users')
          ->restrictOnDelete();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');
    }
};
