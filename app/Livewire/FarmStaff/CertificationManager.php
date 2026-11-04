<?php

namespace App\Livewire\FarmStaff;

use App\Models\Certification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.farm-staff.app')]
#[Title('Hồ Sơ Kiểm Định & Chứng Nhận | Nông Trại Đất Lành')]
class CertificationManager extends Component
{
    public function render()
    {
        $certifications = Certification::orderBy('id', 'desc')->get();

        return view('livewire.farm-staff.certification-manager', [
            'certifications' => $certifications,
        ]);
    }
}