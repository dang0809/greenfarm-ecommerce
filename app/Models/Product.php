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
        'stock',
        'sold_count',
        'avg_rating',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock' => 'integer',
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
            ProductBatch::class,    // Model đích (product_batches)
            PlantingBatch::class,   // Model trung gian (planting_batches)
            'product_id',           // Khóa ngoại trên bảng planting_batches
            'planting_batch_id',    // Khóa ngoại trên bảng product_batches
            'id',                   // Khóa chính trên bảng products
            'id'                    // Khóa chính trên bảng planting_batches
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
}
