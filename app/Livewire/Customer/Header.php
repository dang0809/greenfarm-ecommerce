<?php
namespace App\Livewire\Customer;

use App\Events\NotificationRead;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Cart;
use App\Models\Notification;

class Header extends Component
{
    public int $cartCount = 0;
    public ?int $userId = null;
    public bool $showAllNotifs = false;

    /**
     * Khai báo danh sách sự kiện lắng nghe động (Chỉ nghe WebSocket khi đã đăng nhập)
     */
    public function getListeners(): array
    {
        $listeners = [
            'user-authenticated' => '$refresh',
            'cart-updated'       => 'updateCartCount',
            'profile-updated'    => '$refresh',
        ];

        if (Auth::check()) {
            $listeners['echo-private:App.Models.User.' . Auth::id() . ',.NotificationSent'] = 'onNotificationReceived';
            $listeners['echo-private:App.Models.User.' . Auth::id() . ',.NotificationRead'] = '$refresh';
        }

        return $listeners;
    }

    public function mount(): void
    {
        $this->updateCartCount();
    }

    /**
     * Tự động bắt sự kiện thông báo mới từ WebSocket
     */
    public function onNotificationReceived(array $payload = []): void
    {
        // 1. Livewire tự động re-render quả chuông và dropdown thông báo
        
        // 2. Xác định loại Toast chuẩn (success, error, warning, info) theo từng loại thông báo
        $notifData       = $payload['notificationData'] ?? [];
        $title           = $notifData['title'] ?? 'Bạn có thông báo mới!';
        $notifType       = $notifData['type'] ?? '';
        $customToastType = $notifData['data']['toast_type'] ?? null;

        $toastType = $customToastType ?? match ($notifType) {
            'complaint_rejected', 'order_cancelled'  => 'error',
            'delivery_reminder', 'low_stock'         => 'warning',
            'order_shipping', 'schedule_updated'     => 'info',
            default                                  => 'success',
        };

        $this->dispatch('show-toast', [
            'type'    => $toastType,
            'message' => $title,
        ]);
    }

    public function updateCartCount(): void
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            $this->cartCount = $cart ? $cart->items()->count() : 0;
        } else {
            $this->cartCount = 0;
        }
    }

    // Đánh dấu tất cả hoặc 1 thông báo là đã đọc (và chuyển hướng nếu có url)
    public function markAsRead(?int $notificationId = null)
    {
        if (!Auth::check()) return;

        $userId = (int) Auth::id();

        if ($notificationId) {
            $notif = Notification::where('user_id', $userId)
                ->where('id', $notificationId)
                ->first();

            if ($notif) {
                if (is_null($notif->read_at)) {
                    $notif->update(['read_at' => now()]);
                    // Phát sóng Realtime đồng bộ cho các tab khác biết đã đọc
                    broadcast(new NotificationRead($userId, $notificationId));
                }
                if (!empty($notif->data['url'])) {
                    return $this->redirect($notif->data['url']);
                }
            }
        } else {
            Notification::where('user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            // Phát sóng Realtime xóa số đỏ trên tất cả các tab khác
            broadcast(new NotificationRead($userId, null));
        }
    }

    public function toggleShowAllNotifs(): void
    {
        $this->showAllNotifs = !$this->showAllNotifs;
    }

    public function logout(): void
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        session()->flash('success', 'Đã đăng xuất tài khoản thành công!');

        $this->redirect(route('home'));
    }

    public function render()
    {
        $user = Auth::user();
        $notifications = collect();
        $unreadNotifCount = 0;
        $totalNotifsCount = 0;

        if ($user) {
            $totalNotifsCount = Notification::where('user_id', $user->id)->count();

            $query = Notification::where('user_id', $user->id)->latest();
            if (!$this->showAllNotifs) {
                $query->take(5);
            } else {
                $query->take(30);
            }
            $notifications = $query->get();

            // 🟢 Đếm các thông báo có read_at IS NULL
            $unreadNotifCount = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();
        }

        return view('livewire.customer.header', [
            'user' => $user,
            'notifications' => $notifications,
            'unreadNotifCount' => $unreadNotifCount,
            'totalNotifsCount' => $totalNotifsCount,
        ]);
    }
}