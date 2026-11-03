<?php

namespace App\Livewire\FarmStaff;

use App\Models\FarmPlot;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.farm-staff.app')]
#[Title('Quản Lý Luống Đất & Canh Tác | Nông Trại Đất Lành')]
class FarmPlotManager extends Component
{
    public function render()
    {
        $plots = FarmPlot::with(['plantingBatches' => function ($query) {
            $query->where('status', 'active')->with('product');
            }])->get();

        return view('livewire.farm-staff.farm-plot-manager', [
            'plots' => $plots,
        ]);
    }
}