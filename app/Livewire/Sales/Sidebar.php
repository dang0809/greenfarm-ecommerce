<?php

namespace App\Livewire\Sales;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Sidebar extends Component
{
    public string $activeRoute = '';

    public function mount(): void
    {
        $this->activeRoute = request()->route()?->getName() ?? 'sales.orders';
    }

    /**
     * Lắng nghe sự kiện nổ đơn từ WebSockets hoặc sự kiện duyệt/hủy đơn để cập nhật số đếm ở Sidebar
     */
    #[On('echo:sales-orders,.OrderPaid')]
    #[On('order-status-changed')]
    public function onOrderPaid(): void
    {
        // Livewire tự động re-render để cập nhật badge số lượng
    }

    /**
     * Xử lý đăng xuất tài khoản an toàn
     */
    public function logout(): void
    {
        Auth::guard('staff')->logout();

        // Giữ danh tính khách hàng trong guard web.
        session()->migrate(true);

        session()->flash(
            'success',
            'Đã đăng xuất tài khoản nội bộ!'
        );

        $this->redirect(route('login'));
    }

    public function render()
    {
        // Đếm số lượng đơn hàng đã thanh toán đang ở trạng thái pending (Chờ xác nhận)
        $pendingCount = Order::where(function ($q) {
                $q->whereHas('payment', fn($p) => $p->where('status', 'success'))
                  ->orWhere('total', 0);
            })
            ->where('order_type', '!=', 'subscription')
            ->where('status', 'pending')
            ->count();

        return view('livewire.sales.sidebar', [
            'user' => Auth::guard('staff')->user(),
            'user'         => Auth::user(),
            'pendingCount' => $pendingCount,
        ]);
    }
}