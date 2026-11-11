<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_batch_id',
        'batch_code',
        'status',
        'manufactured_date',
        'expiry_date',
        'initial_quantity',
        'current_quantity',
        'reserved_quantity',
        'spoiled_quantity',
        'discount_percent',
    ];

    protected function casts(): array
    {
        return [
            'manufactured_date' => 'date',
            'expiry_date' => 'date',
            'initial_quantity' => 'integer',
            'current_quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'spoiled_quantity' => 'integer',
            'discount_percent' => 'integer',
        ];
    }

    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            PlantingBatch::class,
            'id',
            'id',
            'planting_batch_id',
            'product_id'
        );
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class);
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class, 'batch_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'batch_id');
    }

    /**
     * Tự động đồng bộ giá sale_price của sản phẩm theo lô đang chạy Flash Sale còn hạn & còn hàng (FEFO).
     * Nếu không còn lô nào đang sale hoặc lô sale đã hết hàng, tự động trả sale_price về NULL (về giá gốc).
     */
    public static function syncProductSalePrice(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) {
            return;
        }

        $activeSaleBatch = static::whereHas('plantingBatch', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->where('status', 'active')
            ->whereRaw('current_quantity > COALESCE(reserved_quantity, 0)')
            ->whereDate('expiry_date', '>=', today())
            ->where('discount_percent', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->first();

        if ($activeSaleBatch) {
            $newSalePrice = round($product->price * (100 - $activeSaleBatch->discount_percent) / 100);
            $product->update(['sale_price' => $newSalePrice]);
        } else {
            $product->update(['sale_price' => null]);
        }
    }
}