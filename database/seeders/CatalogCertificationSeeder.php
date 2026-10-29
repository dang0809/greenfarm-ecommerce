<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogCertificationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Chứng nhận & Kiểm định
        $certs = [
            ['name' => 'Chứng Nhận VietGAP', 'code' => 'VG-2026-LD-098', 'image' => 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=400', 'description' => 'Quy trình thực hành sản xuất nông nghiệp tốt.', 'issued_date' => '2026-01-10', 'expiry_date' => '2028-01-10', 'status' => 'active'],
            ['name' => 'Tiêu Chuẩn Organic 100%', 'code' => 'ORGANIC-VINACERT-2026', 'image' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=400', 'description' => 'Tuyệt đối không phân bón hóa học & không GMO.', 'issued_date' => '2026-02-15', 'expiry_date' => '2027-02-15', 'status' => 'active'],
            ['name' => 'Phiếu Kiểm Nghiệm Đất & Nước', 'code' => 'EUROFINS-Q3-2026', 'image' => 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?w=400', 'description' => 'Không tồn dư kim loại nặng Chì, Cadmium, Asen.', 'issued_date' => '2026-07-01', 'expiry_date' => '2026-12-31', 'status' => 'active'],
        ];
        foreach ($certs as $c) {
            DB::table('certifications')->updateOrInsert(['code' => $c['code']], $c);
        }

        // 2. Danh mục sản phẩm
        $categories = [
            ['name' => 'Rau Lá Xanh', 'slug' => 'rau-la-xanh', 'image' => 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=300', 'description' => 'Các loại rau ăn lá tươi giòn', 'is_active' => true],
            ['name' => 'Củ Quả Tươi', 'slug' => 'cu-qua-tuoi', 'image' => 'https://images.unsplash.com/photo-1592282139728-28040d96a922?w=300', 'description' => 'Củ quả canh tác tự nhiên', 'is_active' => true],
            ['name' => 'Trái Cây', 'slug' => 'trai-cay-theo-mua', 'image' => 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?w=300', 'description' => 'Trái cây ngọt mát theo mùa', 'is_active' => true],
            ['name' => 'Nấm Tươi', 'slug' => 'nam-organic', 'image' => 'https://images.unsplash.com/photo-1509358271058-acd22cc93898?w=300', 'description' => 'Nấm hữu cơ các loại', 'is_active' => true],
            ['name' => 'Rau Gia Vị', 'slug' => 'rau-gia-vi', 'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300', 'description' => 'Hành, ngò, tía tô, húng lủi', 'is_active' => true],
            ['name' => 'Hạt Dinh Dưỡng', 'slug' => 'hat-dinh-duong', 'image' => 'https://images.unsplash.com/photo-1536922246289-88c42f957773?w=300', 'description' => 'Các loại hạt bổ dưỡng', 'is_active' => true],
        ];
        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 3. Danh sách 12+ Sản phẩm đầy đủ
        $cRau = Category::where('slug', 'rau-la-xanh')->first()->id;
        $cCu = Category::where('slug', 'cu-qua-tuoi')->first()->id;
        $cTrai = Category::where('slug', 'trai-cay-theo-mua')->first()->id;
        $cNam = Category::where('slug', 'nam-organic')->first()->id;

        $products = [
            ['category_id' => $cRau, 'name' => 'Cải Bó Xôi Hữu Cơ (Spinach)', 'slug' => 'cai-bo-xoi-huu-co', 'sku' => 'RAU-BOXOI-01', 'price' => 55000, 'sale_price' => 45000, 'unit' => 'kg', 'stock' => 50, 'sold_count' => 230, 'avg_rating' => 4.90, 'image' => 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?w=600'],
            ['category_id' => $cCu, 'name' => 'Cà Chua Cherry Đà Lạt Nguyên Chùm', 'slug' => 'ca-chua-cherry-da-lat', 'sku' => 'QUA-CACHUA-01', 'price' => 65000, 'sale_price' => null, 'unit' => 'hộp 500g', 'stock' => 30, 'sold_count' => 112, 'avg_rating' => 4.00, 'image' => 'https://images.unsplash.com/photo-1546470427-e26264be0b11?w=600'],
            ['category_id' => $cCu, 'name' => 'Khoai Lang Mật Hữu Cơ', 'slug' => 'khoai-lang-mat-huu-co', 'sku' => 'CU-KHOAI-01', 'price' => 40000, 'sale_price' => null, 'unit' => 'kg', 'stock' => 0, 'sold_count' => 500, 'avg_rating' => 5.00, 'image' => 'https://images.unsplash.com/photo-1604152006506-03f478ec1f99?w=600'],
            ['category_id' => $cTrai, 'name' => 'Cam Sành Vắt Nước (Chuẩn VietGAP)', 'slug' => 'cam-sanh-vat-nuoc', 'sku' => 'TC-CAMSANH-01', 'price' => 35000, 'sale_price' => null, 'unit' => 'kg', 'stock' => 100, 'sold_count' => 89, 'avg_rating' => 4.80, 'image' => 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?w=600'],
            ['category_id' => $cRau, 'name' => 'Xà Lách Lô Lô Xanh Thủy Canh', 'slug' => 'xa-lach-lo-lo-xanh', 'sku' => 'RAU-XALACH-02', 'price' => 48000, 'sale_price' => 39000, 'unit' => 'kg', 'stock' => 40, 'sold_count' => 150, 'avg_rating' => 4.70, 'image' => 'https://images.unsplash.com/photo-1556801712-76c8eb07ebb1?w=600'],
            ['category_id' => $cCu, 'name' => 'Cà Rốt Organic Đơn Dương', 'slug' => 'ca-rot-organic-don-duong', 'sku' => 'CU-CAROT-02', 'price' => 32000, 'sale_price' => null, 'unit' => 'kg', 'stock' => 80, 'sold_count' => 95, 'avg_rating' => 4.50, 'image' => 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=600'],
            ['category_id' => $cTrai, 'name' => 'Dưa Lưới Tách Mật Nhà Màng', 'slug' => 'dua-luoi-tach-mat', 'sku' => 'TC-DUALUOI-01', 'price' => 85000, 'sale_price' => 75000, 'unit' => 'trái 1.5kg', 'stock' => 25, 'sold_count' => 64, 'avg_rating' => 4.95, 'image' => 'https://images.unsplash.com/photo-1571575173700-afb9492e6a50?w=600'],
            ['category_id' => $cNam, 'name' => 'Nấm Đùi Gà Hữu Cơ Tươi', 'slug' => 'nam-dui-ga-huu-co', 'sku' => 'NAM-DUIGA-01', 'price' => 52000, 'sale_price' => null, 'unit' => 'gói 500g', 'stock' => 35, 'sold_count' => 180, 'avg_rating' => 4.85, 'image' => 'https://images.unsplash.com/photo-1509358271058-acd22cc93898?w=600'],
            ['category_id' => $cRau, 'name' => 'Rau Cần Tây Mỹ Ép Nước', 'slug' => 'rau-can-tay-my', 'sku' => 'RAU-CANTAY-01', 'price' => 60000, 'sale_price' => 50000, 'unit' => 'kg', 'stock' => 20, 'sold_count' => 310, 'avg_rating' => 4.60, 'image' => 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=600'],
            ['category_id' => $cCu, 'name' => 'Bắp Cải Trái Tim Đà Lạt', 'slug' => 'bap-cai-trai-tim', 'sku' => 'CU-BAPCAI-01', 'price' => 28000, 'sale_price' => null, 'unit' => 'kg', 'stock' => 60, 'sold_count' => 78, 'avg_rating' => 4.40, 'image' => 'https://images.unsplash.com/photo-1584270354949-c26b0d5b4a0c?w=600'],
            ['category_id' => $cTrai, 'name' => 'Dâu Tây Mộc Châu Hái Tay', 'slug' => 'dau-tay-moc-chau', 'sku' => 'TC-DAUTAY-01', 'price' => 180000, 'sale_price' => 150000, 'unit' => 'hộp 500g', 'stock' => 15, 'sold_count' => 420, 'avg_rating' => 4.90, 'image' => 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=600'],
            ['category_id' => $cNam, 'name' => 'Nấm Bào Ngư Xám Tươi', 'slug' => 'nam-bao-ngu-xam', 'sku' => 'NAM-BAONGU-01', 'price' => 38000, 'sale_price' => null, 'unit' => 'gói 500g', 'stock' => 45, 'sold_count' => 210, 'avg_rating' => 4.75, 'image' => 'https://images.unsplash.com/photo-1541832676-9b763b0239ab?w=600'],
        ];

        foreach ($products as $pData) {
            $imgUrl = $pData['image'];
            unset($pData['image'], $pData['stock']);
            $product = Product::updateOrCreate(['slug' => $pData['slug']], array_merge($pData, ['status' => 'active', 'description' => 'Sản phẩm tươi sạch được canh tác theo hướng hữu cơ, thu hoạch trực tiếp tại Nông trại Đất Lành Lâm Đồng.']));

            DB::table('product_images')->updateOrInsert(['product_id' => $product->id, 'is_primary' => true], [
                'image' => $imgUrl, 'sort_order' => 1,
            ]);
        }
    }
}