<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Danh sách các role được phép (VD: 'admin', 'sales')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Kiểm tra chưa đăng nhập
        if (!Auth::check()) {
            return redirect()->route('home')
                ->with('error', 'Vui lòng đăng nhập để tiếp tục.')
                ->with('open_auth_modal', true); // 🟢 Cờ tín hiệu mở Popup
        }

        $user = Auth::user();

        // 2. Kiểm tra tài khoản bị khóa
        if ($user->status === 'blocked') {
            Auth::logout();
            return redirect()->route('home')
                ->with('error', 'Tài khoản của bạn đã bị khóa.')
                ->with('open_auth_modal', true);
        }

        // 3. Kiểm tra Enum role (nếu có)
        $userRole = $user->role instanceof \BackedEnum ? $user->role->value : $user->role;

        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        abort(403, 'Bạn không có quyền truy cập vào khu vực này.');
    }
}