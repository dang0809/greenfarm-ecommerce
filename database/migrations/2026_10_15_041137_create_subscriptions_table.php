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
       Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();                                                             // PK
    $table->foreignId('user_id')
          ->constrained('users')
          ->restrictOnDelete();
    $table->string('recipient_name', 255);
    $table->string('recipient_phone', 20);
    $table->string('recipient_address', 255);
    $table->string('delivery_days', 50)->nullable();                          // VD: "T3,T6"
    $table->decimal('total_price', 12, 2)->default(0);

    $table->string('status', 50)->default('active')->index();                 // active|completed
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
