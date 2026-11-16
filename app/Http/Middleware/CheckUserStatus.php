<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->status === 'blocked') {
            $isCustomer = Auth::user()->role === 'customer';

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $redirectRoute = $isCustomer ? 'home' : 'login';
            $message = $isCustomer
                ? 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ ban quản trị để được hỗ trợ.'
                : 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ ban quản lý.';

            return redirect()->route($redirectRoute)->with('error', $message);
        }

        return $next($request);
    }
}

