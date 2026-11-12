<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'button_text',
        'image',
        'link',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = trim((string) $this->image);

        if ($path === '') {
            return null;
        }

        // URL đầy đủ: ảnh seed, ảnh từ nguồn ngoài...
        if (preg_match('#\Ahttps?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        // Đường dẫn R2 do BannerManager hiện tại tạo.
        if (str_starts_with($path, 'content/banners/')) {
            $baseUrl = trim(
                (string) config('filesystems.disks.r2.url')
            );

            return $baseUrl !== ''
                ? rtrim($baseUrl, '/') . '/' . $path
                : null;
        }

        // Giữ khả năng hiển thị ảnh local cũ.
        return asset('storage/' . $path);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}