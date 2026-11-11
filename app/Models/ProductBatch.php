<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function availableQuantity(): Attribute
    {
        return Attribute::make(
            get: fn(): int => max(
                0,
                (int) $this->current_quantity
                - (int) $this->reserved_quantity
            )
        );
    }

    protected function displayStatus(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->status === 'recalled') {
                    return 'recalled';
                }

                if ((int) $this->current_quantity <= 0) {
                    return 'out_of_stock';
                }

                if (
                    in_array(
                        $this->status,
                        ['pending_recalled', 'suspended'],
                        true
                    )
                ) {
                    return $this->status;
                }

                if ($this->expiry_date?->lt(today())) {
                    return 'expired';
                }

                if ($this->available_quantity <= 0) {
                    return 'reserved';
                }

                return $this->expiry_date?->lte(today()->addDays(3))
                    ? 'near_expiry'
                    : 'active';
            }
        );
    }

    public function hasUnfinishedOrders(): bool
    {
        return $this->orderItems()
            ->whereHas(
                'order',
                fn($query) => $query->whereNotIn(
                    'status',
                    ['delivered', 'cancelled']
                )
            )
            ->exists();
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

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $search = trim($search);

        if ($search === '') {
            return $query;
        }

        $term = '%' . $search . '%';

        return $query->where(function (Builder $query) use ($term) {
            $query->where('batch_code', 'like', $term)
                ->orWhereHas(
                    'plantingBatch',
                    fn(Builder $query) => $query
                        ->where('planting_code', 'like', $term)
                )
                ->orWhereHas(
                    'plantingBatch.product',
                    function (Builder $query) use ($term) {
                        $query->where(function (Builder $query) use ($term) {
                            $query->where('name', 'like', $term)
                                ->orWhere('sku', 'like', $term);
                        });
                    }
                )
                ->orWhereHas(
                    'plantingBatch.farmPlot',
                    fn(Builder $query) => $query
                        ->where('name', 'like', $term)
                );
        });
    }
}