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
        Schema::create('addresses', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('user_id')
          ->constrained('users')
          ->cascadeOnDelete();
    $table->string('recipient_name', 255);
    $table->string('recipient_phone', 20);
    $table->string('province', 255);
    $table->string('district', 255);
    $table->string('ward', 255);
    $table->string('address_detail', 255);
    $table->boolean('is_default')->default(false)->index();
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
