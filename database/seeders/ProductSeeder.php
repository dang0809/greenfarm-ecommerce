<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $rauLa = Category::where('slug', 'rau-la-xanh')->first();
        $cuQua = Category::where('slug', 'cu-qua-tuoi')->first();
        $traiCay = Category::where('slug', 'trai-cay-theo-mua')->first();

        $products = [
            [
                'category_id' => $rauLa?->id,
                'name' => 'Cải Bó Xôi Hữu Cơ (Spinach)',
                'slug' => 'cai-bo-xoi-huu-co',
                'sku' => 'RAU-BO-XOI-01',
                'description' => 'Cải bó xôi hữu cơ tươi sạch thu hoạch mỗi sáng sớm tại trang trại.',
                'price' => 55000,
                'sale_price' => 45000, // Giá khuyến mãi
                'unit' => 'kg',
                'stock' => 50,
                'sold_count' => 230,
                'avg_rating' => 4.9,
                'status' => 'active',
            ],
            [
                'category_id' => $cuQua?->id,
                'name' => 'Cà Chua Cherry Đà Lạt Nguyên Chùm',
                'slug' => 'ca-chua-cherry-da-lat',
                'sku' => 'QUA-CA-CHERRY-01',
                'description' => 'Cà chua cherry Đà Lạt chín mọng, ngọt thanh nguyên chùm.',
                'price' => 65000,
                'sale_price' => null,
                'unit' => 'hộp 500g',
                'stock' => 30,
                'sold_count' => 112,
                'avg_rating' => 4.0,
                'status' => 'active',
            ],
            [
                'category_id' => $cuQua?->id,
                'name' => 'Khoai Lang Mật Hữu Cơ',
                'slug' => 'khoai-lang-mat-huu-co',
                'sku' => 'CU-KHOAI-LANG-01',
                'description' => 'Khoai lang mật dẻo ngọt, chuẩn canh tác hữu cơ không hóa chất.',
                'price' => 40000,
                'sale_price' => null,
                'unit' => 'kg',
                'stock' => 0, // Tồn kho = 0 (Tạm hết hàng)
                'sold_count' => 500,
                'avg_rating' => 5.0,
                'status' => 'active',
            ],
            [
                'category_id' => $traiCay?->id,
                'name' => 'Cam Sành Vắt Nước (Chuẩn VietGAP)',
                'slug' => 'cam-sanh-vat-nuoc',
                'sku' => 'TC-CAM-SANH-01',
                'description' => 'Cam sành mọng nước, vị chua ngọt tự nhiên đạt chuẩn VietGAP.',
                'price' => 35000,
                'sale_price' => null,
                'unit' => 'kg',
                'stock' => 100,
                'sold_count' => 89,
                'avg_rating' => 4.8,
                'status' => 'active',
            ],
        ];

        foreach ($products as $prod) {
            Product::updateOrCreate(['slug' => $prod['slug']], $prod);
        }
    }
}