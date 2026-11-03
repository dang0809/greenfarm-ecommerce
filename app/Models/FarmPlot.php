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
        return ['area' => 'decimal:2'];
    }
    
    public function activeBatch(): ?PlantingBatch
    {
        return $this->plantingBatches
            ->first(fn (PlantingBatch $b) => in_array($b->status, ['planting', 'harvesting'], true));
    }

    public function plantingBatches(): HasMany { return $this->hasMany(PlantingBatch::class); }
}