<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public function __construct(
        public string $token,
        public string $email
    ) {
        $this->resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $this->email,
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔑 Yêu cầu đặt lại mật khẩu - Nông Trại Đất Lành',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '
                <div style="font-family: Arial, sans-serif; max-width: 560px; margin: 0 auto; padding: 28px; border: 1px solid #e5e7eb; border-radius: 16px; background-color: #ffffff;">
                    <div style="text-align: center; margin-bottom: 20px;">
                        <h2 style="color: #15803d; margin: 0; font-size: 24px; font-weight: 800;">🌱 Nông Trại Đất Lành</h2>
                        <p style="color: #6b7280; font-size: 12px; margin-top: 4px; text-transform: uppercase; letter-spacing: 1px;">Khôi phục tài khoản</p>
                    </div>

                    <p style="font-size: 15px; color: #1f2937; line-height: 1.6;">Xin chào <strong>' . htmlspecialchars($this->email) . '</strong>,</p>

                    <p style="font-size: 14px; color: #4b5563; line-height: 1.6;">
                        Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại hệ thống Nông Trại Đất Lành. Hãy nhấp vào nút bên dưới để tạo mật khẩu mới:
                    </p>

                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . $this->resetUrl . '" style="background-color: #15803d; color: #ffffff; padding: 14px 32px; text-decoration: none; font-weight: bold; font-size: 15px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 12px rgba(21, 128, 61, 0.25);">
                            Đặt Lại Mật Khẩu Ngay
                        </a>
                    </div>

                    <p style="font-size: 13px; color: #6b7280; line-height: 1.5; margin-top: 24px;">
                        ⏰ Liên kết này chỉ có hiệu lực trong <strong>60 phút</strong>.
                    </p>

                    <p style="font-size: 12px; color: #9ca3af; line-height: 1.5;">
                        Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này. Tài khoản của bạn vẫn được bảo mật tuyệt đối.
                    </p>

                    <hr style="border: none; border-top: 1px solid #f3f4f6; margin: 24px 0;">

                    <p style="font-size: 11px; color: #9ca3af; word-break: break-all; line-height: 1.4;">
                        Nếu nút trên không hoạt động, bạn có thể sao chép và dán liên kết sau vào trình duyệt:<br>
                        <a href="' . $this->resetUrl . '" style="color: #15803d;">' . $this->resetUrl . '</a>
                    </p>
                </div>
            '
        );
    }
}

