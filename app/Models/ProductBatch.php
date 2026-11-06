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
public function getAvailableQuantityAttribute(): int
{
    return max(
        0,
        (int) $this->current_quantity
            - (int) $this->reserved_quantity
    );
}

public function getDisplayStatusAttribute(): string
{
    if (in_array($this->status, ['recalled', 'suspended'], true)) {
        return $this->status;
    }

    // HSD bằng hôm nay vẫn còn hạn trong ngày.
    if ($this->expiry_date->lt(today())) {
        return 'expired';
    }

    if ($this->current_quantity <= 0) {
        return 'out_of_stock';
    }

    if ($this->available_quantity <= 0) {
        return 'reserved';
    }

    return $this->expiry_date->lte(today()->addDays(3))
        ? 'near_expiry'
        : 'active';
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
}