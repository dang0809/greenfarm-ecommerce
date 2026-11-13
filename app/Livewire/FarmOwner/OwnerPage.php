<?php

namespace App\Livewire\FarmOwner;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

abstract class OwnerPage extends Component
{
    public function boot(): void
    {
        $user = Auth::guard('web')->user();

        abort_unless(
            $user
            && $user->role === 'farm_owner'
            && $user->status === 'active',
            403,
            'Bạn không có quyền truy cập khu vực chủ trang trại.'
        );
    }
}