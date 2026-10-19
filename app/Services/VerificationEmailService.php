<?php

namespace App\Services;

use App\Mail\VerifyEmailMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordOtpMail;
class VerificationEmailService
{
    public function send(User $user): bool
    {
        try {
            // Tự động push vào Queue ngầm nhờ VerifyEmailMail implements ShouldQueue
            Mail::to($user->email)->send(new VerifyEmailMail($user));
            return true;
        } catch (\Exception $e) {
            Log::error('Verification Mail Error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendResetOtp(string $email, string $otp): bool
    {
        try {
            // Tự động đẩy vào Queue nhờ ResetPasswordOtpMail implements ShouldQueue
            Mail::to($email)->send(new ResetPasswordOtpMail($otp));
            return true;
        } catch (\Exception $e) {
            Log::error('Reset OTP Mail Error: ' . $e->getMessage());
            return false;
        }
    }
}