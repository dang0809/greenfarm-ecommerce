<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'recipient_name', 'recipient_phone', 'recipient_address',
        'delivery_days', 'total_price', 'total_deliveries', 'delivered_count', 'status',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'total_deliveries' => 'integer',
            'delivered_count' => 'integer',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function deliverySchedules(): HasMany { return $this->hasMany(DeliverySchedule::class); }
}