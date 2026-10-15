<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliverySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'delivery_order',
        'delivery_date',
        'items',
        'order_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'delivery_order' => 'integer',
            'delivery_date' => 'date',
            'items' => 'array',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
