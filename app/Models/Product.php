<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'sale_price',
        'unit',
        'weight_kg',
        'sold_count',
        'avg_rating',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'weight_kg' => 'float',
            'sold_count' => 'integer',
            'avg_rating' => 'decimal:2',
        ];
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->coverImage?->url;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function plantingBatches(): HasMany
    {
        return $this->hasMany(PlantingBatch::class);
    }

    public function batches(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProductBatch::class,
            PlantingBatch::class,
            'product_id',
            'planting_batch_id',
            'id',
            'id'
        );
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Scope tính tổng số lượng tồn kho khả dụng của các lô còn hạn & active (đã trừ phần giữ chỗ)
     */
    public function scopeWithTotalStock($query)
    {
        return $query->withSum(['batches as total_stock' => function ($q) {
            $q->where('product_batches.status', 'active')
              ->whereRaw('product_batches.current_quantity > COALESCE(product_batches.reserved_quantity, 0)')
              ->whereDate('product_batches.expiry_date', '>=', now());
        }], DB::raw('current_quantity - COALESCE(reserved_quantity, 0)'));
    }

    /**
     * Thuộc tính tổng tồn kho khả dụng (lấy từ SQL nếu có withTotalStock, hoặc tự động tính nếu gọi lẻ)
     */
    public function getTotalStockAttribute(): int
    {
        // SQL SUM có thể trả NULL khi không có lô phù hợp.
        if (array_key_exists('total_stock', $this->attributes)) {
            return (int) ($this->attributes['total_stock'] ?? 0);
        }

        return (int) $this->batches()
            ->where('product_batches.status', 'active')
            ->whereRaw('product_batches.current_quantity > COALESCE(product_batches.reserved_quantity, 0)')
            ->whereDate('product_batches.expiry_date', '>=', now())
            ->sum(DB::raw('current_quantity - COALESCE(reserved_quantity, 0)'));
    }

    /**
     * Phân tách giá theo nguyên tắc FEFO từng lô hàng:
     * - Số lượng lấy từ lô đang Flash Sale: tính theo giá giảm của lô đó.
     * - Số lượng lấy bù từ lô mới / hạn dài: tính theo giá gốc!
     */
    public function getPriceBreakdownForQuantity(int $quantity): array
    {
        $remainingNeeded = max(0, $quantity);
        $total = 0;
        $breakdown = [];

        $availableBatches = $this->batches()
            ->where('product_batches.status', 'active')
            ->whereDate('product_batches.expiry_date', '>=', now())
            ->whereRaw('product_batches.current_quantity > COALESCE(product_batches.reserved_quantity, 0)')
            ->orderBy('product_batches.expiry_date', 'asc')
            ->get();

        foreach ($availableBatches as $batch) {
            if ($remainingNeeded <= 0) break;

            $availableInBatch = max(0, (int) $batch->current_quantity - (int) ($batch->reserved_quantity ?? 0));
            $take = min($availableInBatch, $remainingNeeded);
            if ($take <= 0) continue;

            $isSale = $batch->discount_percent > 0;
            $unitPrice = $isSale 
                ? round($this->price * (100 - $batch->discount_percent) / 100)
                : (float) $this->price;

            $subtotalBatch = $unitPrice * $take;
            $total += $subtotalBatch;

            $breakdown[] = [
                'batch_id'         => $batch->id,
                'batch_code'       => $batch->batch_code,
                'quantity'         => $take,
                'unit_price'       => $unitPrice,
                'subtotal'         => $subtotalBatch,
                'is_sale'          => $isSale,
                'discount_percent' => $batch->discount_percent,
            ];

            $remainingNeeded -= $take;
        }

        // Dự phòng nếu thiếu lô, phần còn lại tính theo giá gốc
        if ($remainingNeeded > 0) {
            $unitPrice = (float) $this->price;
            $subtotalExtra = $unitPrice * $remainingNeeded;
            $total += $subtotalExtra;
            $breakdown[] = [
                'batch_id'         => null,
                'batch_code'       => null,
                'quantity'         => $remainingNeeded,
                'unit_price'       => $unitPrice,
                'subtotal'         => $subtotalExtra,
                'is_sale'          => false,
                'discount_percent' => 0,
            ];
        }

        // Gộp các phần có cùng đơn giá và cùng mức giảm giá để hiển thị gọn gàng trên Giỏ hàng & Thanh toán
        $mergedBreakdown = [];
        foreach ($breakdown as $item) {
            $key = $item['unit_price'] . '_' . ($item['discount_percent'] ?? 0);
            if (isset($mergedBreakdown[$key])) {
                $mergedBreakdown[$key]['quantity'] += $item['quantity'];
                $mergedBreakdown[$key]['subtotal'] += $item['subtotal'];
            } else {
                $mergedBreakdown[$key] = $item;
            }
        }

        return [
            'total'     => $total,
            'breakdown' => array_values($mergedBreakdown),
        ];
    }
}