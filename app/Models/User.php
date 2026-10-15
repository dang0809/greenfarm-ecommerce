<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'avatar',
        'reward_points',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'reward_points' => 'integer',
        ];
    }

    // Quan hệ 1 User có nhiều địa chỉ nhận hàng
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    // Quan hệ 1 User có 1 giỏ hàng
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    // Quan hệ 1 User có nhiều gói subscription
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // Quan hệ 1 User có nhiều đơn hàng
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Quan hệ 1 User có nhiều khiếu nại
    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    // Quan hệ 1 User có nhiều đánh giá sản phẩm
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Quan hệ 1 User có nhiều thông báo
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
