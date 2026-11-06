<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FarmBlockchainSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('inventory_logs')->truncate();
        DB::table('product_batches')->truncate();
        DB::table('traceability_events')->truncate();
        DB::table('planting_batches')->truncate();
        DB::table('farm_plots')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $farmStaff = User::where('role', 'farm_staff')->first();
        $pBoxoi = Product::where('slug', 'cai-bo-xoi-huu-co')->first();
        $pCachua = Product::where('slug', 'ca-chua-cherry-da-lat')->first();
        $pDuaLuoi = Product::where('slug', 'dua-luoi-tach-mat')->first();

        // 1. Khu đất canh tác
        $p1 = DB::table('farm_plots')->insertGetId(['name' => 'Nhà Màng A1 - Hữu Cơ', 'area' => '1000', 'status' => 'active', 'created_at' => now()]);
        $p2 = DB::table('farm_plots')->insertGetId(['name' => 'Nhà Màng A2 - Công Nghệ Cao', 'area' => '1200', 'status' => 'active', 'created_at' => now()]);
        $p3 = DB::table('farm_plots')->insertGetId(['name' => 'Luống B1 - Ngoài Trời', 'area' => '800', 'status' => 'active', 'created_at' => now()]);

        // 2. Lô canh tác
        $b1 = DB::table('planting_batches')->insertGetId([
            'farm_plot_id' => $p1,
            'product_id' => $pBoxoi?->id ?? 1,
            'planting_code' => 'TRONG-BOXOI-L1',
            'planted_date' => now()->subDays(60)->toDateString(),
            'status' => 'completed',
            'created_at' => now()->subDays(60),
            'updated_at' => now(),
        ]);

        $b2 = DB::table('planting_batches')->insertGetId([
            'farm_plot_id' => $p2,
            'product_id' => $pCachua?->id ?? 2,
            'planting_code' => 'TRONG-CACHUA-L2',
            'planted_date' => now()->subDays(45)->toDateString(),
            'status' => 'harvesting',
            'created_at' => now()->subDays(45),
            'updated_at' => now(),
        ]);

        $b3 = DB::table('planting_batches')->insertGetId([
            'farm_plot_id' => $p3,
            'product_id' => $pDuaLuoi?->id ?? 7,
            'planting_code' => 'TRONG-DUALUOI-L3',
            'planted_date' => now()->subDays(20)->toDateString(),
            'status' => 'planting',
            'created_at' => now()->subDays(20),
            'updated_at' => now(),
        ]);

        // 3. Nhật ký (Traceability Events)
        $events = [
            ['planting_batch_id' => $b1, 'event_name' => 'Gieo Hạt Hữu Cơ', 'description' => 'Hạt giống thuần chủng F1 nhập khẩu Hà Lan, kiểm định xơ dừa và khay ươm.', 'image' => 'https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?w=500', 'recorded_by' => $farmStaff?->name, 'recorded_at' => now()->subDays(60)],
            ['planting_batch_id' => $b1, 'event_name' => 'Bón Phân Trùn Quế', 'description' => 'Bón 50kg phân trùn quế vi sinh kết hợp tưới đạm cá lên men.', 'image' => 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=500', 'recorded_by' => $farmStaff?->name, 'recorded_at' => now()->subDays(35)],
            ['planting_batch_id' => $b1, 'event_name' => 'Thu Hoạch Sáng Sớm', 'description' => 'Thu hoạch cắt gốc từ 5h30 sáng, đưa ngay vào phòng mát 18 độ C.', 'image' => 'https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?w=500', 'recorded_by' => $farmStaff?->name, 'recorded_at' => now()->subDays(2)],
            ['planting_batch_id' => $b2, 'event_name' => 'Tỉa Chồi & Thụ Phấn Ong', 'description' => 'Thả ong mật tự nhiên thụ phấn nhà màng A2.', 'image' => 'https://images.unsplash.com/photo-1592282139728-28040d96a922?w=500', 'recorded_by' => $farmStaff?->name, 'recorded_at' => now()->subDays(15)],
        ];
        foreach ($events as $e) {
            DB::table('traceability_events')->insert($e);
        }

        // 4. Lô Thu Hoạch Kho (Product Batches) & Nhật ký biến động
        $pb1 = DB::table('product_batches')->insertGetId(['planting_batch_id' => $b1, 'batch_code' => 'DL-20261015-K1', 'status' => 'active', 'manufactured_date' => now()->subDays(2)->format('Y-m-d'), 'expiry_date' => now()->addDays(5)->format('Y-m-d'), 'initial_quantity' => 100, 'current_quantity' => 45, 'reserved_quantity' => 0, 'spoiled_quantity' => 5, 'discount_percent' => 15]);
        $pb2 = DB::table('product_batches')->insertGetId(['planting_batch_id' => $b2, 'batch_code' => 'DL-20261016-K2', 'status' => 'active', 'manufactured_date' => now()->subDays(1)->format('Y-m-d'), 'expiry_date' => now()->addDays(7)->format('Y-m-d'), 'initial_quantity' => 80, 'current_quantity' => 60, 'reserved_quantity' => 0, 'spoiled_quantity' => 0, 'discount_percent' => 0]);

        DB::table('inventory_logs')->insert([
            ['batch_id' => $pb1, 'type' => 'import', 'quantity' => 100, 'note' => 'Thu hoạch nhập kho sáng sớm', 'created_by' => $farmStaff?->id ?? 2, 'created_at' => now()->subDays(2)],
            ['batch_id' => $pb1, 'type' => 'export_spoilage', 'quantity' => 5, 'note' => 'Xuất hủy 5kg dập nát do rớt thùng sơ chế', 'created_by' => $farmStaff?->id ?? 2, 'created_at' => now()->subDays(1)],
        ]);
    }
}