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
        Schema::create('traceability_events', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('planting_batch_id')
          ->constrained('planting_batches')
          ->cascadeOnDelete()
          ->index();                                                           // FK
    $table->string('event_name', 100)->index();
    $table->text('description')->nullable();
    $table->string('image', 255)->nullable();
    $table->string('recorded_by', 255);
    $table->timestamp('recorded_at')->nullable()->index();
    $table->string('data_hash', 255)->index();                                // SHA256 hash
    $table->string('blockchain_tx_hash', 255)->nullable()->unique();          // UK
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traceability_events');
    }
};
