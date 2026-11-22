<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;
use App\Models\DeliverySchedule;
use App\Models\Product;

#[Title('Lịch Trình & Tiến Độ Giao Rau VIP')]
class SubscriptionTracker extends Component
{
    public ?Subscription $subscription = null;

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            return $this->redirect(route('login'), navigate: true);
        }

        $this->subscription = Subscription::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$this->subscription) {
            session()->flash('error', 'Bạn chưa có gói đăng ký VIP nào.');
            return $this->redirect(route('customer.vip.checkout'), navigate: true);
        }

        $hasSchedules = DeliverySchedule::where('subscription_id', $this->subscription->id)->exists();
        // Nếu chưa chốt lịch -> Điều hướng sang trang thiết lập
        if (empty($this->subscription->delivery_days) || !$hasSchedules) {
            return $this->redirect(route('customer.vip.setup'), navigate: true);
        }
    }

    public function render()
    {
        $saved = DeliverySchedule::where('subscription_id', $this->subscription->id)
            ->orderBy('delivery_order')
            ->get();

        $allProdIds = [];
        foreach ($saved as $ds) {
            $rawItems = $ds->items ?? [];
            foreach ($rawItems as $it) {
                $pId = $it['product_id'] ?? null;
                if ($pId) {
                    $allProdIds[] = (int) $pId;
                }
            }
        }

        $productsKeyed = !empty($allProdIds)
            ? Product::with('productImages')->whereIn('id', array_unique($allProdIds))->get()->keyBy('id')//keyBy đánh lại key của Collection theo cột id.
            : collect();

        $lockedSchedules = [];
        foreach ($saved as $ds) {
            $rawItems = $ds->items ?? [];
            $itemsDetail = [];
            $w = 0.0;
            foreach ($rawItems as $it) {
                $pId = $it['product_id'] ?? null;
                $q = (int) ($it['quantity'] ?? 1);
                $prod = $pId ? ($productsKeyed->get($pId)) : null;

                $imgUrl = $prod?->cover_image_url
                    ?? 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=100';

                $itemWeight = isset($it['weight']) ? (float) $it['weight'] : round($q * ($prod?->weight_kg ?? 0.5), 2);
                $itemsDetail[] = [
                    'name' => $it['product_name'] ?? $prod?->name ?? 'Rau hữu cơ',
                    'quantity' => $q,
                    'unit' => $it['unit'] ?? $prod?->unit,
                    'weight' => $itemWeight,
                    'image' => $imgUrl,
                    'slug' => $prod?->slug ?? '',
                ];
                $w += $itemWeight;
            }

            $lockedSchedules[] = [
                'order' => $ds->delivery_order,
                'date' => $ds->delivery_date?->format('d/m/Y') ?? (string) $ds->delivery_date,
                'status' => $ds->status ?? 'pending',
                'total_weight' => round($w, 1),
                'items' => $itemsDetail,
            ];
        }

        $deliveredCount = (int) $this->subscription->deliverySchedules()->where('status', 'delivered')->count();

        return view('livewire.customer.subscription-tracker', [
            'lockedSchedules' => $lockedSchedules,
            'deliveredCount' => $deliveredCount,
        ])->layout('layouts.customer.app');
    }
}
