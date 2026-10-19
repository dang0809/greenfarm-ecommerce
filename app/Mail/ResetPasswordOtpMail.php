<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public string $otp) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔑 Mã xác nhận đặt lại mật khẩu - Nông Trại Đất Lành',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '
                <div style="font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 16px; background-color: #ffffff;">
                    <h2 style="color: #16a34a; text-align: center;">🌱 Nông Trại Đất Lành</h2>
                    <p style="font-size: 15px; color: #1f2937;">Xin chào,</p>
                    <p style="font-size: 14px; color: #4b5563;">Bạn vừa yêu cầu đặt lại mật khẩu. Đây là mã OTP xác thực của bạn:</p>
                    <div style="text-align: center; margin: 24px 0;">
                        <span style="font-size: 32px; font-weight: 800; letter-spacing: 6px; color: #16a34a; background-color: #f0fdf4; padding: 12px 28px; border-radius: 12px; border: 1px dashed #22c55e; display: inline-block;">
                            ' . $this->otp . '
                        </span>
                    </div>
                    <p style="font-size: 12px; color: #9ca3af; text-align: center;">Mã OTP có hiệu lực trong 10 phút. Vui lòng không chia sẻ mã này cho bất kỳ ai.</p>
                </div>
            '
        );
    }
}