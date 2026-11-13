<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
class Certification extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'image',
        'description',
        'issued_date',
        'expiry_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    protected function effectiveStatus(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if (
                    $this->status === 'expired'
                    || (
                        $this->expiry_date !== null
                        && $this->expiry_date->lt(today())
                    )
                ) {
                    return 'expired';
                }

                return 'active';
            }
        );
    }

    protected function isPdf(): Attribute
    {
        return Attribute::make(
            get: function (): bool {
                $path = parse_url(
                    trim((string) $this->image),
                    PHP_URL_PATH
                );

                return is_string($path)
                    && strtolower(
                        pathinfo($path, PATHINFO_EXTENSION)
                    ) === 'pdf';
            }
        );
    }

    public function scopeEffectiveStatus(
        Builder $query,
        string $status
    ): Builder {
        if ($status === 'active') {
            return $query
                ->where('status', 'active')
                ->where(function (Builder $query) {
                    $query->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', today());
                });
        }

        if ($status === 'expired') {
            return $query->where(function (Builder $query) {
                $query->where('status', 'expired')
                    ->orWhereDate('expiry_date', '<', today());
            });
        }

        return $query;
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
                ->orWhere('code', 'like', $term);
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