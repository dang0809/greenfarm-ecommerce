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
        $farmStaff = User::where('role', 'farm_staff')->first() ?? User::first();

        // 1. Khu đất canh tác (Tạo hoặc cập nhật)
        $plotsData = [
            'A1' => ['name' => 'Nhà Màng A1 - Hữu Cơ', 'area' => '1000', 'status' => 'active'],
            'A2' => ['name' => 'Nhà Màng A2 - Công Nghệ Cao', 'area' => '1200', 'status' => 'active'],
            'A3' => ['name' => 'Nhà Màng A3 - Trồng Rau Ăn Lá', 'area' => '1500', 'status' => 'active'],
            'B1' => ['name' => 'Luống B1 - Ngoài Trời', 'area' => '800', 'status' => 'active'],
            'N1' => ['name' => 'Nhà Nấm Hữu Cơ N1', 'area' => '600', 'status' => 'active'],
            'C1' => ['name' => 'Vườn Cây Ăn Trái C1', 'area' => '2500', 'status' => 'active'],
        ];

        $plotIds = [];
        foreach ($plotsData as $key => $plot) {
            $existing = DB::table('farm_plots')->where('name', $plot['name'])->first();
            if ($existing) {
                $plotIds[$key] = $existing->id;
            } else {
                $plotIds[$key] = DB::table('farm_plots')->insertGetId(array_merge($plot, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 2. Danh sách cấu hình Lô trồng & Lô thu hoạch kho cho toàn bộ 12 sản phẩm
        $inventorySetup = [
            [
                'product_slug'   => 'cai-bo-xoi-huu-co',
                'plot_key'       => 'A1',
                'planting_code'  => 'TRONG-BOXOI-L1',
                'planted_days'   => 60,
                'planting_status'=> 'harvesting',
                'batches' => [
                    [
                        'batch_code'    => 'DL-20261015-K1',
                        'mfg_days_ago'  => 4,
                        'exp_days_next' => 1, // Còn 1 ngày (dưới 24h) -> Flash Sale 50%
                        'initial_qty'   => 10,
                        'current_qty'   => 10,
                        'discount'      => 50,
                    ],
                    [
                        'batch_code'    => 'DL-BOXOI-NEAR-K2',
                        'mfg_days_ago'  => 3,
                        'exp_days_next' => 2, // Còn 2 ngày (24h - 48h) -> Flash Sale 30%
                        'initial_qty'   => 10,
                        'current_qty'   => 10,
                        'discount'      => 30,
                    ],
                    [
                        'batch_code'    => 'DL-BOXOI-FRESH-K3',
                        'mfg_days_ago'  => 1,
                        'exp_days_next' => 7, // Còn 7 ngày -> Hàng mới hạn dài (Giá gốc)
                        'initial_qty'   => 10,
                        'current_qty'   => 10,
                        'discount'      => 0,
                    ],
                ],
            ],
            [
                'product_slug'   => 'ca-chua-cherry-da-lat',
                'plot_key'       => 'A2',
                'planting_code'  => 'TRONG-CACHUA-L2',
                'planted_days'   => 45,
                'planting_status'=> 'harvesting',
                'batches' => [
                    [
                        'batch_code'    => 'DL-20261016-K2',
                        'mfg_days_ago'  => 1,
                        'exp_days_next' => 12,
                        'initial_qty'   => 100,
                        'current_qty'   => 70,
                        'discount'      => 0,
                    ],
                ],
            ],
            [
                'product_slug'   => 'xa-lach-lo-lo-xanh',
                'plot_key'       => 'A3',
                'planting_code'  => 'TRONG-XALACH-L1',
                'planted_days'   => 35,
                'planting_status'=> 'harvesting',
                'batches' => [
                    [
                        'batch_code'    => 'DL-XALACH-K1',
                        'mfg_days_ago'  => 1,
                        'exp_days_next' => 10,
                        'initial_qty'   => 80,
                        'current_qty'   => 75,
                        'discount'      => 0,
                    ],
                ],
            ],
            [
                'product_slug'   => 'ca-rot-organic-don-duong',
                'plot_key'       => 'B1',
                'planting_code'  => 'TRONG-CAROT-L1',
                'planted_days'   => 75,
                'planting_status'=> 'harvesting',
                'batches' => [
                    [
                        'batch_code'    => 'DL-CAROT-K1',
                        'mfg_days_ago'  => 2,
                        'exp_days_next' => 16,
                        'initial_qty'   => 100,
                        'current_qty'   => 90,
                        'discount'      => 0,
                    ],
                ],
            ],
            [
                'product_slug'   => 'dua-luoi-tach-mat',
                'plot_key'       => 'A2',
                'planting_code'  => 'TRONG-DUALUOI-L3',
                'planted_days'   => 50,
                'planting_status'=> 'harvesting',
                'batches' => [
                    [
                        'batch_code'    => 'DL-DUALUOI-K1',
                        'mfg_days_ago'  => 1,
                        'exp_days_next' => 14,
                        'initial_qty'   => 60,
                        'current_qty'   => 50,
                        'discount'      => 0,
                    ],
                ],
            ],
            [
                'product_slug'   => 'bap-cai-trai-tim',
                'plot_key'       => 'B1',
                'planting_code'  => 'TRONG-BAPCAI-L1',
                'planted_days'   => 55,
                'planting_status'=> 'harvesting',
                'batches' => [
                    [
                        'batch_code'    => 'DL-BAPCAI-K1',
                        'mfg_days_ago'  => 2,
                        'exp_days_next' => 14,
                        'initial_qty'   => 90,
                        'current_qty'   => 80,
                        'discount'      => 0,
                    ],
                ],
            ],
            [
                'product_slug'   => 'rau-can-tay-my',
                'plot_key'       => 'A3',
                'planting_code'  => 'TRONG-CANTAY-L1',
                'planted_days'   => 40,
                'planting_status'=> 'harvesting',
                'batches' => [
                    [
                        'batch_code'    => 'DL-CANTAY-K1',
                        'mfg_days_ago'  => 1,
                        'exp_days_next' => 9,
                        'initial_qty'   => 70,
                        'current_qty'   => 60,
                        'discount'      => 0,
                    ],
                ],
            ],
            [
                'product_slug'   => 'khoai-lang-mat-huu-co',
                'plot_key'       => 'B1',
                'planting_code'  => 'TRONG-KHOAI-L1',
                'planted_days'   => 90,
                'planting_status'=> 'completed',
                'batches' => [
                    [
                        'batch_code'    => 'DL-KHOAI-K1',
                        'mfg_days_ago'  => 3,
                        'exp_days_next' => 30,
                        'initial_qty'   => 150,
                        'current_qty'   => 130,
                        'discount'      => 0,
                    ],
                ],
            ],
            [
                'product_slug'   => 'nam-dui-ga-huu-co',
                'plot_key'       => 'N1',
                'planting_code'  => 'TRONG-NAMDUIGA-L1',
                'planted_days'   => 25,
                'planting_status'=> 'harvesting',
                'batches' => [
                    [
                        'batch_code'    => 'DL-NAMDUIGA-K1',
                        'mfg_days_ago'  => 1,
                        'exp_days_next' => 10,
                        'initial_qty'   => 75,
                        'current_qty'   => 65,
                        'discount'      => 0,
                    ],
                ],
            ],
            [
                'product_slug'   => 'nam-bao-ngu-xam',
                'plot_key'       => 'N1',
                'planting_code'  => 'TRONG-NAMBAONGU-L1',
                'planted_days'   => 20,
                'planting_status'=> 'harvesting',
                'batches' => [
                    [
                        'batch_code'    => 'DL-NAMBAONGU-K1',
                        'mfg_days_ago'  => 1,
                        'exp_days_next' => 8,
                        'initial_qty'   => 60,
                        'current_qty'   => 50,
                        'discount'      => 0,
                    ],
                ],
            ],
            [
                'product_slug'   => 'cam-sanh-vat-nuoc',
                'plot_key'       => 'C1',
                'planting_code'  => 'TRONG-CAMSANH-L1',
                'planted_days'   => 180,
                'planting_status'=> 'harvesting',
                'batches' => [
                    [
                        'batch_code'    => 'DL-CAMSANH-K1',
                        'mfg_days_ago'  => 2,
                        'exp_days_next' => 20,
                        'initial_qty'   => 120,
                        'current_qty'   => 100,
                        'discount'      => 0,
                    ],
                ],
            ],
            [
                'product_slug'   => 'dau-tay-moc-chau',
                'plot_key'       => 'A2',
                'planting_code'  => 'TRONG-DAUTAY-L1',
                'planted_days'   => 60,
                'planting_status'=> 'harvesting',
                'batches' => [
                    [
                        'batch_code'    => 'DL-DAUTAY-K1',
                        'mfg_days_ago'  => 1,
                        'exp_days_next' => 7,
                        'initial_qty'   => 50,
                        'current_qty'   => 40,
                        'discount'      => 0,
                    ],
                ],
            ],
        ];

        // 3. Tiến hành Seed an toàn (UpdateOrInsert để không phá vỡ liên kết đơn hàng cũ)
        foreach ($inventorySetup as $item) {
            $product = Product::where('slug', $item['product_slug'])->first();
            if (!$product) {
                continue;
            }

            $plotId = $plotIds[$item['plot_key']] ?? reset($plotIds);

            // A. Tạo hoặc cập nhật Lô trồng (Planting Batch)
            $plantingBatch = DB::table('planting_batches')->where('planting_code', $item['planting_code'])->first();
            if ($plantingBatch) {
                $pbId = $plantingBatch->id;
                DB::table('planting_batches')->where('id', $pbId)->update([
                    'status'       => $item['planting_status'],
                    'updated_at'   => now(),
                ]);
            } else {
                $pbId = DB::table('planting_batches')->insertGetId([
                    'farm_plot_id' => $plotId,
                    'product_id'   => $product->id,
                    'planting_code'=> $item['planting_code'],
                    'planted_date' => now()->subDays($item['planted_days'])->toDateString(),
                    'status'       => $item['planting_status'],
                    'created_at'   => now()->subDays($item['planted_days']),
                    'updated_at'   => now(),
                ]);
            }

            // B. Nhật ký canh tác (Traceability Event) nếu chưa có
            $eventExists = DB::table('traceability_events')->where('planting_batch_id', $pbId)->exists();
            if (!$eventExists) {
                DB::table('traceability_events')->insert([
                    [
                        'planting_batch_id' => $pbId,
                        'event_name'        => 'Gieo Trồng Chuẩn Hữu Cơ',
                        'description'       => "Gieo trồng giống chuẩn sạch trên vùng đất hữu cơ tại {$plotsData[$item['plot_key']]['name']}.",
                        'image'             => 'https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?w=500',
                        'recorded_by'       => $farmStaff?->name ?? 'Kỹ sư nông nghiệp',
                        'recorded_at'       => now()->subDays($item['planted_days']),
                    ],
                    [
                        'planting_batch_id' => $pbId,
                        'event_name'        => 'Thu Hoạch Sớm & Kiểm Định',
                        'description'       => 'Thu hoạch đúng độ chín lý tưởng vào sáng sớm, kiểm tra dư lượng đạt chuẩn 100%.',
                        'image'             => 'https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?w=500',
                        'recorded_by'       => $farmStaff?->name ?? 'Kỹ sư nông nghiệp',
                        'recorded_at'       => now()->subDays(1),
                    ],
                ]);
            }

            // Đảm bảo các lô cũ không nằm trong danh sách seeder được reset current_quantity = 0 để tổng tồn kho chuẩn xác
            $configuredCodes = array_column($item['batches'], 'batch_code');
            DB::table('product_batches')->where('planting_batch_id', $pbId)
                ->whereNotIn('batch_code', $configuredCodes)
                ->update(['current_quantity' => 0, 'reserved_quantity' => 0, 'discount_percent' => 0]);

            // C. Tạo hoặc cập nhật Lô thu hoạch kho (Product Batches)
            foreach ($item['batches'] as $bData) {
                $existingBatch = DB::table('product_batches')->where('batch_code', $bData['batch_code'])->first();
                if ($existingBatch) {
                    // Nếu lô đã tồn tại, đảm bảo status active và cập nhật số lượng tồn khả dụng
                    DB::table('product_batches')->where('id', $existingBatch->id)->update([
                        'status'            => 'active',
                        'expiry_date'       => now()->addDays($bData['exp_days_next'])->format('Y-m-d'),
                        'current_quantity'  => $bData['current_qty'],
                        'reserved_quantity' => 0,
                        'discount_percent'  => $bData['discount'],
                        'updated_at'        => now(),
                    ]);
                    $batchId = $existingBatch->id;
                } else {
                    $batchId = DB::table('product_batches')->insertGetId([
                        'planting_batch_id' => $pbId,
                        'batch_code'        => $bData['batch_code'],
                        'status'            => 'active',
                        'manufactured_date' => now()->subDays($bData['mfg_days_ago'])->format('Y-m-d'),
                        'expiry_date'       => now()->addDays($bData['exp_days_next'])->format('Y-m-d'),
                        'initial_quantity'  => $bData['initial_qty'],
                        'current_quantity'  => $bData['current_qty'],
                        'reserved_quantity' => 0,
                        'spoiled_quantity'  => 0,
                        'discount_percent'  => $bData['discount'],
                        'created_at'        => now()->subDays($bData['mfg_days_ago']),
                        'updated_at'        => now(),
                    ]);
                }

                // D. Ghi log nhập kho nếu chưa có log cho lô này
                $logExists = DB::table('inventory_logs')->where('batch_id', $batchId)->where('type', 'import')->exists();
                if (!$logExists) {
                    DB::table('inventory_logs')->insert([
                        'batch_id'   => $batchId,
                        'type'       => 'import',
                        'quantity'   => $bData['initial_qty'],
                        'note'       => "Thu hoạch nhập kho nông sản sạch ({$product->name})",
                        'created_by' => $farmStaff?->id ?? 2,
                        'created_at' => now()->subDays($bData['mfg_days_ago']),
                    ]);
                }
            }

            // Đồng bộ lại giá sale_price của sản phẩm theo lô đang giảm giá sớm nhất (FEFO)
            \App\Models\ProductBatch::syncProductSalePrice($product->id);
        }
    }
}
