<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class VerifyEmailMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public User $user;
    public string $verificationUrl;

    public function __construct(User $user)
    {
        $this->user = $user;

        // Tạo Signed URL xác thực tài khoản (hạn 60 phút)
        $this->verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🌱 Xác thực địa chỉ Email tài khoản Nông Trại Đất Lành',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 16px; background-color: #ffffff;">
                    <h2 style="color: #16a34a; text-align: center;">🌱 Nông Trại Đất Lành</h2>
                    <p style="font-size: 15px; color: #1f2937;">Xin chào <strong>' . htmlspecialchars($this->user->name) . '</strong>,</p>
                    <p style="font-size: 14px; color: #4b5563;">Vui lòng nhấp vào nút dưới đây để kích hoạt tài khoản của bạn:</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . $this->verificationUrl . '" style="background-color: #16a34a; color: #ffffff; padding: 14px 30px; text-decoration: none; font-weight: bold; border-radius: 10px; display: inline-block;">Kích Hoạt Tài Khoản Ngay</a>
                    </div>
                    <p style="font-size: 12px; color: #9ca3af; text-align: center;">Link xác thực có hiệu lực trong 60 phút.</p>
                </div>
            '
        );
    }
}