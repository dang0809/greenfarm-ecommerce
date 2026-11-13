<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TraceabilityEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_batch_id',
        'event_name',
        'description',
        'image',
        'recorded_by',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    public function plantingBatch(): BelongsTo
    {
        return $this->belongsTo(PlantingBatch::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = trim((string) $this->image);

        if ($path === '') {
            return null;
        }

        // Giữ nguyên dữ liệu đang lưu bằng URL đầy đủ.
        if (preg_match('/\Ahttps?:\/\//i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        // Ảnh mới được lưu trên R2.
        if (str_starts_with($path, 'farm-logs/r2/')) {
            $baseUrl = rtrim(
                (string) config('filesystems.disks.r2.url'),
                '/'
            );

            return $baseUrl !== ''
                ? $baseUrl . '/' . $path
                : null;
        }

        // Ảnh cũ đang nằm trong public Storage.
        return asset('storage/' . $path);
    }
}