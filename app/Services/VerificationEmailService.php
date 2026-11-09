<?php

namespace App\Services;

use App\Mail\ResetPasswordMail;
use App\Mail\VerifyEmailMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    public function sendResetLink(string $email, string $token): bool
    {
        try {
            // Tự động đẩy vào Queue nhờ ResetPasswordMail implements ShouldQueue
            Mail::to($email)->send(new ResetPasswordMail($token, $email));
            return true;
        } catch (\Exception $e) {
            Log::error('Reset Password Mail Error: ' . $e->getMessage());
            return false;
        }
    }
}