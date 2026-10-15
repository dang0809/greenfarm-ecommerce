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
       Schema::create('planting_batches', function (Blueprint $table) {
        $table->id();                                                             // PK
        $table->foreignId('farm_plot_id')
            ->constrained('farm_plots')
            ->restrictOnDelete();
        $table->foreignId('product_id')
            ->constrained('products')
            ->restrictOnDelete();
        $table->string('planting_code', 100)->unique();                           // UK
        $table->string('status', 50)->default('planting')->index();               // planting|harvesting|completed
        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planting_batches');
    }
};
