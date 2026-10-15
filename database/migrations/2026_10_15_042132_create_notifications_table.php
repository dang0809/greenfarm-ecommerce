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
        Schema::create('notifications', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('user_id')
          ->constrained('users')
          ->cascadeOnDelete();                                                           // FK
    $table->string('type', 100)->index();
    $table->string('title', 255);
    $table->text('content');
    $table->json('data')->nullable();
    $table->timestamp('read_at')->nullable()->index();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
