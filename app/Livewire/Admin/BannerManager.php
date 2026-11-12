<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin.app')]
#[Title('Nội Dung & Banner | GreenFarm Admin')]
class BannerManager extends Component
{
    public function render()
    {
        return view('livewire.admin.banner-manager');
    }
}