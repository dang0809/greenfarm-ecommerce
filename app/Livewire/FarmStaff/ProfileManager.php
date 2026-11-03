<?php

namespace App\Livewire\FarmStaff;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.farm-staff.app')]
#[Title('Hồ Sơ Cá Nhân Nhân Viên | Nông Trại Đất Lành')]
class ProfileManager extends Component
{
    public function render()
    {
        $user = Auth::user();

        return view('livewire.farm-staff.profile-manager', [
            'user' => $user,
        ]);
    }
}