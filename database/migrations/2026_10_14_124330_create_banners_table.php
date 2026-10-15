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
      Schema::create('banners', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->string('title', 255);
    $table->text('description')->nullable();
    $table->string('button_text', 50)->nullable();
    $table->string('image', 255);
    $table->string('link', 255)->nullable();
    $table->integer('sort_order')->default(0)->index();
    $table->string('status', 50)->default('active')->index();                 // active|inactive
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
