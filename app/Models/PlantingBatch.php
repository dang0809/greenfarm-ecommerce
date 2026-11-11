<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantingBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_plot_id',
        'product_id',
        'planting_code',
        'planted_date',
        'min_manufactured_date',
        'status',
    ];

    // 'planting': Đang gieo trồng / Chăm sóc.
    // 'harvesting': Đang trong giai đoạn thu hoạch.
    // 'completed': Vụ mùa đã kết thúc.

    protected function casts(): array
    {
        return [
            'planted_date' => 'date',
            'min_manufactured_date' => 'date',
        ];
    }

    public function farmPlot(): BelongsTo
    {
        return $this->belongsTo(FarmPlot::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function traceabilityEvents(): HasMany
    {
        return $this->hasMany(TraceabilityEvent::class);
    }

    public function productBatches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function isHarvestReady(): bool
    {
        return $this->status === 'harvesting'
            && $this->min_manufactured_date !== null
            && $this->min_manufactured_date->toDateString()
            <= today()->toDateString();
    }
}