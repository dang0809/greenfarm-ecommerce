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
        $path = $this->coverImage?->image;

        if (! $path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : Storage::disk('public')->url(ltrim($path, '/'));
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
            ->where('product_batches.current_quantity', '>', 0)
            ->whereDate('product_batches.expiry_date', '>=', today())
            ->sum('product_batches.current_quantity');
            ->whereRaw('product_batches.current_quantity > COALESCE(product_batches.reserved_quantity, 0)')
            ->whereDate('product_batches.expiry_date', '>=', now())
            ->sum(DB::raw('current_quantity - COALESCE(reserved_quantity, 0)')));
    }
}