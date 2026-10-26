<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'order_code', 
        'order_type', 
        'subtotal', 
        'shipping_distance_km',
        'shipping_fee', 
        'points_used', 
        'points_earned', 
        'total', 
        'payment_method',
        'payment_status', 
        'shipping_provider', 
        'tracking_code', 
        'recipient_name',
        'recipient_phone', 
        'recipient_address', 
        'status', 
        'note',
        // Các trường phục vụ tính năng Hủy đơn & Hoàn tiền
        'cancel_reason',
        'cancelled_at',
        'cancelled_by',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'refund_status',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'             => 'decimal:2',
            'shipping_distance_km' => 'decimal:2',
            'shipping_fee'         => 'decimal:2',
            'total'                => 'decimal:2',
            'points_used'          => 'integer',
            'points_earned'        => 'integer',
            'cancelled_at'         => 'datetime',
            'refunded_at'          => 'datetime',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function statusHistories(): HasMany { return $this->hasMany(OrderStatusHistory::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function payment(): HasOne { return $this->hasOne(Payment::class); }
    public function complaint(): HasOne { return $this->hasOne(Complaint::class); }
    public function shippingAddress(): BelongsTo { return $this->belongsTo(Address::class, 'shipping_address_id'); }
    public function deliverySchedule(): HasOne { return $this->hasOne(DeliverySchedule::class, 'order_id'); }
}