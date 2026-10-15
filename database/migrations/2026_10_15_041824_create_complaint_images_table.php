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
        Schema::create('complaint_images', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('complaint_id')
          ->constrained('complaints')
          ->cascadeOnDelete();
    $table->string('image', 255);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_images');
    }
};
