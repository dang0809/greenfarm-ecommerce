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
            'spoiled_quantity' => 'integer',
            'discount_percent' => 'integer',
        ];
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