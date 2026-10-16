<?php

namespace Database\Seeders;

use App\Models\PlantingBatch;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlantingBatchSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lấy ID sản phẩm mẫu (Cải Bó Xôi)
        $product = Product::where('slug', 'cai-bo-xoi-huu-co')->first();

        // 2. Lấy ID luống đất mẫu (nếu chưa có trong bảng farm_plots thì tự tạo 1 luống đất mẫu)
        $farmPlot = DB::table('farm_plots')->first();
        if (!$farmPlot) {
            $farmPlotId = DB::table('farm_plots')->insertGetId([
                'name' => 'Luống đất A1 - Nhà màng 1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $farmPlotId = $farmPlot->id;
        }

        // 3. Khởi tạo lô gieo trồng
        PlantingBatch::updateOrCreate(
            ['planting_code' => 'DL-20261015-K1'],
            [
                'farm_plot_id' => $farmPlotId,
                'product_id' => $product?->id ?? 1,
                'status' => 'harvesting', // planting | harvesting | completed
            ]
        );
    }
}