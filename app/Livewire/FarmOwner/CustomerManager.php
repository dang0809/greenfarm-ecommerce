<?php

namespace App\Livewire\FarmOwner;

use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
class CustomerManager extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function boot(): void
    {
        $owner = Auth::id()
            ? User::find(Auth::id())
            : null;

        abort_unless(
            $owner
            && $owner->role === 'farm_owner'
            && $owner->status === 'active',
            403,
            'Bạn không có quyền truy cập khu vực chủ trang trại.'
        );
    }

    public function render()
    {
        $customers = User::query()
            ->select(
                'id',
                'name',
                'email',
                'phone',
                'status',
                'reward_points',
                'created_at'
            )
            ->where('role', 'customer')
            ->search($this->search)
            ->withCount([
                'orders',
                'subscriptions as active_subscriptions_count' => fn($query) =>
                    $query->where('status', 'active'),
            ])
            ->latest('id')
            ->paginate(15);

        return view(
            'livewire.farm-owner.customer-manager',
            compact('customers')
        )->layout('layouts.farm-owner.app', [
                    'title' => 'Danh sách khách hàng',
                ]);
    }
}