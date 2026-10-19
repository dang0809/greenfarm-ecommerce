<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Thanh Toán Gói Rau VIP | Nông Trại Đất Lành')]
class VipCheckout extends Component
{
    public string $paymentMethod = 'vnpay';
    public string $packageName = 'Gói Bếp Xanh VIP Gia Đình (1 Tháng)';
    public int $totalDeliveries = 8;
    public int $price = 1250000;

    public function processPayment()
    {
        // Bắn event xuống JS để hiển thị SweetAlert2 kết nối cổng thanh toán
        $this->dispatch('start-payment-process', [
            'amount' => number_format($this->price, 0, ',', '.') . 'đ',
            'method' => strtoupper($this->paymentMethod)
        ]);
    }

    public function render()
    {
        // Nạp đúng Layout Customer đã có sẵn trong dự án
        return view('livewire.customer.vip-checkout')
            ->layout('layouts.customer.app');
    }
}
