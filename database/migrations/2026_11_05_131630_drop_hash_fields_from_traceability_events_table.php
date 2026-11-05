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
        Schema::table('traceability_events', function (Blueprint $table) {
            $table->dropColumn(['data_hash', 'blockchain_tx_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('traceability_events', function (Blueprint $table) {
            $table->string('data_hash', 255)->index();
            $table->string('blockchain_tx_hash', 255)->nullable()->unique();
        });
    }
};