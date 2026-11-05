<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->image;

        if (! $path) {
            return null;
        }

        // Hỗ trợ URL ảnh từ seeder và đường dẫn ảnh upload.
        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : Storage::disk('public')->url(ltrim($path, '/'));
    }
}