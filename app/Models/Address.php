<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'recipient_name', 'recipient_phone',
        'province', 'district', 'ward', 'address_detail' , 'is_default', 'latitude', 'longitude',
    ];

    public function getFullAddressAttribute(): string
    {
        return trim("{$this->address_detail}, {$this->ward}, {$this->district}, {$this->province}", ', ');
    }

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}