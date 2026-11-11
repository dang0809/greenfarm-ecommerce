<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image',
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

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $search = trim($search);

        if ($search === '') {
            return $query;
        }

        $term = '%' . $search . '%';

        return $query->where(function (Builder $query) use ($term) {
            $query->where('name', 'like', $term)
                ->orWhere('slug', 'like', $term);
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        $path = trim((string) $this->image);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $r2Url = config('filesystems.disks.r2.url');
        if (!empty($r2Url)) {
            return rtrim($r2Url, '/') . '/' . ltrim($path, '/');
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}