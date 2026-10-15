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
        Schema::create('certifications', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->string('name', 255);
    $table->string('code', 50)->unique();                                     // UK
    $table->string('image', 255)->nullable();
    $table->text('description')->nullable();
    $table->date('issued_date')->nullable();
    $table->date('expiry_date')->nullable()->index();
    $table->string('status', 50)->default('active')->index();                 // active|expired
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
