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

    // Các trạng thái của lô đất
    // empty => Đất trống (mới tạo)
    // in-progress => Đang canh tác (có cây đang trồng)
    // maintenance => Đang cải tạo (Sau khi trồng xong kết thúc vụ)

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
}