<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_url',
        'caption',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function getDisplayUrlAttribute(): ?string
    {
        $path = trim((string) $this->image_url);

        if ($path === '') {
            return null;
        }

        if (preg_match('#\Ahttps?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'content/farm-images/')) {
            $baseUrl = trim(
                (string) config('filesystems.disks.r2.url')
            );

            return $baseUrl !== ''
                ? rtrim($baseUrl, '/') . '/' . $path
                : null;
        }

        return asset('storage/' . $path);
    }
}