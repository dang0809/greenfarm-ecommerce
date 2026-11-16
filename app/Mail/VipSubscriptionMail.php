<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Subscription;
use App\Models\SystemSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VipSubscriptionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription $subscription,
        public Order $order
    ) {
        // Load thông tin user để lấy tên & email gửi thư
        $this->subscription->loadMissing('user');
    }

    /**
     * Tiêu đề Email xác nhận kích hoạt Gói Rau VIP
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '👑 Gói Rau VIP ' . SystemSetting::farmName()
            . ' đã kích hoạt thành công — Thiết lập lịch giao ngay!',
        );
    }

    /**
     * Nội dung Email sử dụng Blade template chuyên biệt cho VIP
     */
    public function content(): Content
    {
        $vipSettings = SystemSetting::vipSettings();
        $packageName = $vipSettings['package_name'];
        $deliveryMaxWeight = $vipSettings['delivery_max_weight'];

        return new Content(
            view: 'emails.vip_subscription',
            with: [
                'subscription' => $this->subscription,
                'order' => $this->order,
                'packageName' => $packageName,
                'deliveryMaxWeight' => $deliveryMaxWeight,
                'totalWeight' => round($deliveryMaxWeight * 8, 1),
                'contact' => SystemSetting::contactSettings(),
            ],
        );
    }
}