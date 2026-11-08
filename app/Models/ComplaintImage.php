<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'image',
    ];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        if (empty($this->image)) {
            return '';
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        $r2Url = config('filesystems.disks.r2.url');
        if (!empty($r2Url)) {
            return rtrim($r2Url, '/') . '/' . ltrim($this->image, '/');
        }

        return asset('storage/' . $this->image);
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }
}
