<?php

namespace App\Livewire\FarmOwner;

use App\Models\User;
use Livewire\WithPagination;

class CustomerManager extends OwnerPage
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
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