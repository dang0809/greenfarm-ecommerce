<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuardIdentity
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $rolesByGuard = [
            'web' => ['customer'],
            'staff' => ['farm_staff', 'sales', 'admin'],
        ];

        foreach ($rolesByGuard as $name => $roles) {
            $guard = Auth::guard($name);
            $user = $guard->user();

            if (! $user) {
                continue;
            }

            $invalidIdentity =
                ! in_array($user->role, $roles, true)
                || $user->status !== 'active'
                || (
                    $name === 'web'
                    && ! $user->hasVerifiedEmail()
                );

            if ($invalidIdentity) {
                // Chỉ dọn danh tính/cookie của guard sai.
                // Không xóa toàn bộ session hoặc guard còn lại.
                $guard->logoutCurrentDevice();
            }
        }

        return $next($request);
    }
}