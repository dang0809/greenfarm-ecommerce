<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,                     // 1. Dữ liệu User
            SystemContentSeeder::class,            // 2. Cấu hình & Banner
            CatalogCertificationSeeder::class,    // 3. Danh mục & Sản phẩm
            FarmBlockchainSeeder::class,          // 4. Lô nông trại & FEFO Kho
            OrderComplaintReviewSeeder::class,    // 5. TẠO ĐƠN HÀNG TRƯỚC (Để có id trong bảng orders)
            CustomerSubscriptionSeeder::class,    // 6. TẠO GÓI VIP & LỊCH GIAO (Mới tham chiếu order_id an toàn)
        ]);
    }
}
