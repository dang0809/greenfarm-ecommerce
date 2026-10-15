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
       Schema::create('order_status_histories', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('order_id')
          ->constrained('orders')
          ->cascadeOnDelete();
    $table->string('old_status', 50)->nullable();
    $table->string('new_status', 50)->index();
    $table->foreignId('changed_by')
          ->constrained('users')
          ->restrictOnDelete();
    $table->text('note')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
