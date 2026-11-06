<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotificationSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $notificationData;
    public int $userId;

    public function __construct(Notification $notification)
    {
        $this->userId = (int) $notification->user_id;

        $unreadCount = Notification::where('user_id', $this->userId)
            ->whereNull('read_at')
            ->count();

        $this->notificationData = [
            'id'           => $notification->id,
            'title'        => $notification->title,
            'content'      => $notification->content,
            'data'         => $notification->data,
            'unread_count' => $unreadCount,
            'created_at'   => $notification->created_at ? $notification->created_at->diffForHumans() : 'Vừa xong',
        ];
    }

    /**
     * Kênh phát sóng riêng tư cho người dùng (Chỉ đúng user đó mới nhận được)
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
        return 'NotificationSent';
    }
}

