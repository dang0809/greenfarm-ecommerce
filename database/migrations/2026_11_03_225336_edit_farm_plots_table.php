<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        Schema::table('product_batches', function (Blueprint $table) {
            // Một lần gửi form thu hoạch chỉ tạo một lô thương mại.
            $table->uuid('harvest_token')->nullable()->unique();
        });

        Schema::table('traceability_events', function (Blueprint $table) {
            // NULL: nhật ký cũ chưa dùng quy tắc hash mới.
            $table->unsignedTinyInteger('hash_version')->nullable();
            $table->char('image_sha256', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('traceability_events', function (Blueprint $table) {
            $table->dropColumn(['hash_version', 'image_sha256']);
        });

        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropUnique(['harvest_token']);
            $table->dropColumn('harvest_token');
        });

        Schema::table('farm_plots', function (Blueprint $table) {
            $table->string('area', 100)->nullable()->change();
        });
    }
};