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
            // Nếu là khu vực của Khách hàng -> Về trang chủ và mở Popup đăng nhập
            if (in_array('customer', $roles)) {
                return redirect()->route('home')
                    ->with('error', 'Vui lòng đăng nhập để tiếp tục.')
                    ->with('open_auth_modal', true);
            }

            // Nếu là khu vực Cán bộ nhân viên (sales, farm_staff, admin) -> Về trang /login riêng
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập tài khoản quản trị để tiếp tục.');
        }

        $user = Auth::user();

        // 2. Kiểm tra tài khoản bị khóa
        if ($user->status === 'blocked') {
            Auth::logout();

            if (in_array('customer', $roles)) {
                return redirect()->route('home')
                    ->with('error', 'Tài khoản của bạn đã bị khóa.')
                    ->with('open_auth_modal', true);
            }

            return redirect()->route('login')
                ->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ ban quản lý.');
        }

        // 3. Kiểm tra phân quyền truy cập
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Bạn không có quyền truy cập vào khu vực này.');
}
}