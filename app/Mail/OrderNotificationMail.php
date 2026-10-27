<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param Order $order
     * @param string $type ('paid' | 'delivered')
     */
    public function __construct(
        public Order $order,
        public string $type = 'paid'
    ) {
        if ($this->order->exists) {
            $this->order->loadMissing(['items.product', 'user']);
        }
    }

    /**
     * Tiêu đề Email dựa theo loại thông báo
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->type) {
            'delivered' => '🎉 Đơn hàng #' . $this->order->order_code . ' đã được giao thành công - Nông Trại Đất Lành',
            default     => '🌱 Xác nhận đơn hàng #' . $this->order->order_code . ' đã thanh toán thành công - Nông Trại Đất Lành',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Nội dung Email sử dụng Blade template
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_notification',
            with: [
                'order' => $this->order,
                'type'  => $this->type,
            ],
        );
    }
}
