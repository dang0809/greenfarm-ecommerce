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

    

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function plantingBatches(): HasMany
    {
        return $this->hasMany(PlantingBatch::class);
    }

    public function productbatches(): HasManyThrough
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
     * Scope tính tổng số lượng tồn kho của các lô còn hạn & active (dùng cho query danh sách)
     */
    public function scopeWithTotalStock($query)
    {
        return $query->withSum(['batches as total_stock' => function ($q) {
            $q->where('product_batches.status', 'active')
              ->where('product_batches.current_quantity', '>', 0)
              ->whereDate('product_batches.expiry_date', '>=', now());
        }], 'current_quantity');
    }

    /**
     * Thuộc tính tổng tồn kho (lấy từ SQL nếu có withTotalStock, hoặc tự động tính nếu gọi lẻ)
     */
    public function getTotalStockAttribute(): int
    {
        return (int) ($this->attributes['total_stock'] ?? $this->batches()
            ->where('product_batches.status', 'active')
            ->where('product_batches.current_quantity', '>', 0)
            ->whereDate('product_batches.expiry_date', '>=', now())
            ->sum('product_batches.current_quantity'));
    }
}