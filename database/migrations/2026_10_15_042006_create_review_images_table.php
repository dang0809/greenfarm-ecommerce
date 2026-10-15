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
       Schema::create('review_images', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('review_id')
          ->constrained('reviews')
          ->cascadeOnDelete();
    $table->string('image', 255);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_images');
    }
};
