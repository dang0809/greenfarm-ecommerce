<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Kênh phát sóng riêng tư cho tài khoản người dùng
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
    }

    /**
     * Tên sự kiện phát ra
     */
    public function broadcastAs(): string
    {
        return 'NotificationRead';
    }
}

