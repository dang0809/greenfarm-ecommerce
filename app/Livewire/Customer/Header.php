<?php
namespace App\Livewire\Customer;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Cart;
use App\Models\Notification;

class Header extends Component
{
    public int $cartCount = 0;

    // Bắt sự kiện khi giỏ hàng thay đổi hoặc đăng nhập thành công để tự động reload
    protected $listeners = [
        'user-authenticated' => '$refresh',
        'cart-updated' => 'updateCartCount',
    ];

    public function mount(): void
    {
        $this->updateCartCount();
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

    // Đánh dấu tất cả hoặc 1 thông báo là đã đọc
    public function markAsRead(?int $notificationId = null): void
    {
        if (!Auth::check()) return;

        if ($notificationId) {
            Notification::where('user_id', Auth::id())
                ->where('id', $notificationId)
                ->update(['read_at' => now()]);
            } else {
                Notification::where('user_id', Auth::id())
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
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

    if ($user) {
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // 🟢 Đếm các thông báo có read_at IS NULL
        $unreadNotifCount = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

        return view('livewire.customer.header', [
            'user' => $user,
            'notifications' => $notifications,
            'unreadNotifCount' => $unreadNotifCount,
        ]);
    }
}