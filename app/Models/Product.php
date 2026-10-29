<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
        'sold_count',
        'avg_rating',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'sold_count' => 'integer',
            'avg_rating' => 'decimal:2',
        ];
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
     * Lấy số kg thực tế theo quy cách đóng gói (unit) của sản phẩm
     */
    public function getWeightKgAttribute(): float
    {
        $u = mb_strtolower(trim($this->unit ?? ''));
        if (preg_match('/(\d+(?:\.\d+)?)\s*kg/ui', $u, $m)) {
            return (float) $m[1];
        }
        if ($u === 'kg' || $u === 'ký') {
            return 1.0;
        }
        if (preg_match('/(\d+(?:\.\d+)?)\s*g/ui', $u, $m)) {
            return round(((float) $m[1]) / 1000, 2);
        }
        return 0.5;
    }

    /**
     * Tổng số đơn vị thành phẩm bán lẻ (hộp/gói/trái/kg) sẵn có trong kho
     * Quy đổi từ tổng số kg trong các lô còn hạn & còn hàng
     */
    public function getTotalStockUnitsAttribute(): float
    {
        $weightPerUnit = $this->weight_kg;
        if ($weightPerUnit <= 0) {
            $weightPerUnit = 1.0;
        }

        $totalKg = isset($this->attributes['total_stock']) 
            ? (float) $this->attributes['total_stock']
            : (float) $this->batches()
                ->where('product_batches.status', 'active')
                ->where('product_batches.current_quantity', '>', 0)
                ->whereDate('product_batches.expiry_date', '>=', now())
                ->sum('product_batches.current_quantity');

        $u = mb_strtolower(trim($this->unit ?? ''));
        if ($u === 'kg' || $u === 'ký') {
            return round($totalKg, 2);
        }

        return (float) floor(round($totalKg / $weightPerUnit, 4));
    }
}