<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(
        Request $request,
        Closure $next,
        string $guard,
        string ...$roles
    ): Response {
        abort_unless(
            in_array($guard, ['web', 'staff'], true)
                && count($roles) > 0,
            500
        );

        $auth = Auth::guard($guard);
        $user = $auth->user();

        if (! $user) {
            return $this->loginRedirect($guard);
        }

        if (
            $user->status !== 'active'
            || (
                $guard === 'web'
                && ! $user->hasVerifiedEmail()
            )
        ) {
            $auth->logoutCurrentDevice();

            return $this->loginRedirect($guard);
        }

        abort_unless(
            in_array($user->role, $roles, true),
            403,
            'Bạn không có quyền truy cập khu vực này.'
        );

        return $next($request);
    }

    private function loginRedirect(string $guard): Response
    {
        if ($guard === 'web') {
            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'Vui lòng đăng nhập tài khoản khách hàng.'
                )
                ->with('open_auth_modal', true);
        }

        return redirect()
            ->route('login')
            ->with(
                'error',
                'Vui lòng đăng nhập tài khoản nội bộ.'
            );
    }
}