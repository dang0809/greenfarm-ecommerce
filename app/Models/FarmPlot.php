<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FarmPlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'area',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'area' => 'decimal:2',
        ];
    }

    public function plantingBatches(): HasMany
    {
        return $this->hasMany(PlantingBatch::class);
    }

    public function activeBatch(): ?PlantingBatch
    {
        if ($this->relationLoaded('plantingBatches')) {
            return $this->plantingBatches
                ->sortByDesc('id')
                ->first(fn (PlantingBatch $batch) => in_array(
                    $batch->status,
                    ['planting', 'harvesting'],
                    true
                ));
        }

        return $this->plantingBatches()
            ->occupying()
            ->latest('id')
            ->first();
    }
}