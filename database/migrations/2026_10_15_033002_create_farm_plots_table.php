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
        $table->string('area', 100)->nullable();
        $table->string('status', 50)->default('active')->index();                 // active|maintenance
        $table->timestamps();
        });

        // Kiểm tra toàn bộ trước khi cập nhật dữ liệu.
        $normalized = [];

        foreach (DB::table('farm_plots')->get(['id', 'area']) as $plot) {
            $value = trim((string) $plot->area);

            if ($value === '') {
                $normalized[$plot->id] = null;
                continue;
            }

            // Nhận: 1000, 1000.50, 1000,50, 1000m2, 1000 m².
            // Không tự đoán dấu phân cách hàng nghìn.
            if (! preg_match(
                '/^(\d{1,10}(?:[.,]\d{1,2})?)\s*(?:m2|m²)?$/u',
                $value,
                $matches
            )) {
                throw new RuntimeException(
                    "Diện tích luống #{$plot->id} không hợp lệ: {$value}"
                );
            }

            $normalized[$plot->id] = number_format(
                (float) str_replace(',', '.', $matches[1]),
                2,
                '.',
                ''
            );
        }

        DB::transaction(function () use ($normalized) {
            foreach ($normalized as $id => $area) {
                DB::table('farm_plots')
                    ->where('id', $id)
                    ->update(['area' => $area]);
            }
        });

        Schema::table('farm_plots', function (Blueprint $table) {
            $table->decimal('area', 12, 2)->nullable()->change();
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