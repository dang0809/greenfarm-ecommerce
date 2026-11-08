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
       Schema::create('farm_plots', function (Blueprint $table) {
        $table->id();                                                             // PK
        $table->string('name', 255);
        $table->decimal('area', 12, 2)->nullable();
        $table->string('status', 50)->default('available')->index();                 // available: Sẵn sàng xuống giống|in-progress: Đang canh tác|maintenance: Đang cải tạo
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_plots');
    }
};