<?php
namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;
use App\Models\Address;
use App\Models\Product;
use App\Models\DeliverySchedule;

#[Title('Thiết Lập Địa Chỉ & Lịch Giao VIP | Nông Trại Đất Lành')]
class SubscriptionSetup extends Component
{
    public ?Subscription $subscription = null;
    public string $contractCode = '';
    public float $maxWeightTarget = 3.5;
    public int $activeTab = 1;
    public bool $isLocked = false;
    public bool $showAddressModal = false;

    public int $selectedAddressId = 0;
    public array $selectedDayIndexes = [2, 5]; // Mặc định Thứ 3 & Thứ 6

    public array $weekdays = [
        ['id' => 1, 'label' => 'Thứ 2', 'code' => 'T2', 'dayIndex' => 1],
        ['id' => 2, 'label' => 'Thứ 3', 'code' => 'T3', 'dayIndex' => 2],
        ['id' => 3, 'label' => 'Thứ 4', 'code' => 'T4', 'dayIndex' => 3],
        ['id' => 4, 'label' => 'Thứ 5', 'code' => 'T5', 'dayIndex' => 4],
        ['id' => 5, 'label' => 'Thứ 6', 'code' => 'T6', 'dayIndex' => 5],
        ['id' => 6, 'label' => 'Thứ 7', 'code' => 'T7', 'dayIndex' => 6],
        ['id' => 0, 'label' => 'Chủ Nhật', 'code' => 'CN', 'dayIndex' => 0],
    ];

    public array $schedulePlan = [];

    public function mount()
    {
        $user = Auth::user();

        // 1. LẤY HỢP ĐỒNG GÓI VIP THẬT TỪ DATABASE
        $this->subscription = Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'pending_setup'])
            ->latest()
            ->first();

        // Nếu chưa mua gói VIP, đẩy về trang mua gói
        if (!$this->subscription) {
            session()->flash('error', 'Bạn chưa có gói đăng ký VIP nào cần thiết lập.');
            return $this->redirect(route('customer.vip.checkout'), navigate: true);
        }

        $this->contractCode = $this->subscription->code ?? 'SUB-' . $this->subscription->id;
        $this->isLocked = $this->subscription->status === 'active' && $this->subscription->is_locked;

        // 2. LẤY ĐỊA CHỈ MẶC ĐỊNH THẬT
        $defaultAddress = Address::where('user_id', $user->id)->where('is_default', true)->first()
            ?? Address::where('user_id', $user->id)->first();

        if ($defaultAddress) {
            $this->selectedAddressId = $defaultAddress->id;
        }

        // 3. KHỞI TẠO LỊCH 8 ĐỢT
        $this->updateSchedulePlanDates();
    }

    public function generate8DeliveryDates(array $daysArray): array
    {
        $dates = [];
        $current = new \DateTime();

        while (count($dates) < 8) {
            $current->modify('+1 day');
            $dayOfWeek = (int) $current->format('w');

            if (in_array($dayOfWeek, $daysArray)) {
                $weekdayObj = collect($this->weekdays)->firstWhere('dayIndex', $dayOfWeek);
                $dates[] = [
                    'code' => $weekdayObj['code'],
                    'date_label' => $weekdayObj['code'] . ' (' . $current->format('d/m') . ')',
                    'delivery_date' => $current->format('Y-m-d'),
                ];
            }
        }
        return $dates;
    }

    public function updateSchedulePlanDates()
    {
        $calculatedDates = $this->generate8DeliveryDates($this->selectedDayIndexes);

        if (empty($this->schedulePlan)) {
            foreach ($calculatedDates as $idx => $d) {
                $this->schedulePlan[] = [
                    'delivery_order' => $idx + 1,
                    'date_label' => $d['date_label'],
                    'delivery_date' => $d['delivery_date'],
                    'items' => [],
                ];
            }
        } else {
            foreach ($this->schedulePlan as $idx => &$plan) {
                if (isset($calculatedDates[$idx])) {
                    $plan['date_label'] = $calculatedDates[$idx]['date_label'];
                    $plan['delivery_date'] = $calculatedDates[$idx]['delivery_date'];
                }
            }
        }
    }

    public function toggleDaySelection(int $dayIdx)
    {
        if ($this->isLocked) return;

        if (in_array($dayIdx, $this->selectedDayIndexes)) {
            if (count($this->selectedDayIndexes) === 1) {
                $this->dispatch('swal-warning', [
                    'title' => 'Cần chọn đủ 2 ngày!',
                    'text' => 'Bạn phải chọn đúng 2 ngày nhận hàng cố định trong tuần.'
                ]);
                return;
            }
            $this->selectedDayIndexes = array_values(array_filter($this->selectedDayIndexes, fn($i) => $i !== $dayIdx));
        } else {
            if (count($this->selectedDayIndexes) >= 2) {
                array_shift($this->selectedDayIndexes);
            }
            $this->selectedDayIndexes[] = $dayIdx;
        }

        usort($this->selectedDayIndexes, fn($a, $b) => (($a === 0) ? 7 : $a) <=> (($b === 0) ? 7 : $b));
        $this->updateSchedulePlanDates();
    }

    public function calculateDeliveryWeight(array $itemsObj, $veggieCatalog): float
    {
        $total = 0.0;
        foreach ($itemsObj as $productId => $qty) {
            $product = $veggieCatalog->firstWhere('id', (int) $productId);
            if ($product) {
                // Giả định định lượng sản phẩm lấy từ DB (VD: 0.5kg/bó)
                $weight = $product->weight_per_unit ?? 0.5;
                $total += $qty * $weight;
            }
        }
        return round($total, 2);
    }

    public function switchTab(int $tabNumber)
    {
        $this->activeTab = $tabNumber;
    }

    public function updateQty(int $veggieId, int $delta)
    {
        if ($this->isLocked) return;

        $tabIndex = array_search($this->activeTab, array_column($this->schedulePlan, 'delivery_order'));
        if ($tabIndex === false) return;

        $currentQty = $this->schedulePlan[$tabIndex]['items'][$veggieId] ?? 0;
        $newQty = $currentQty + $delta;

        if ($newQty <= 0) {
            unset($this->schedulePlan[$tabIndex]['items'][$veggieId]);
        } else {
            $this->schedulePlan[$tabIndex]['items'][$veggieId] = $newQty;
        }
    }

    public function copyMenuToAllDeliveries()
    {
        if ($this->isLocked) return;

        $currentPlan = collect($this->schedulePlan)->firstWhere('delivery_order', $this->activeTab);
        if (!$currentPlan) return;

        $itemsToCopy = $currentPlan['items'];
        foreach ($this->schedulePlan as &$plan) {
            $plan['items'] = $itemsToCopy;
        }

        $this->dispatch('swal-success', [
            'title' => 'Đã Sao Chép!',
            'text' => "Đã áp dụng thực đơn ngày {$currentPlan['date_label']} cho cả 8 đợt giao."
        ]);
    }

    public function selectAddress(int $id)
    {
        if ($this->isLocked) return;
        $this->selectedAddressId = $id;
        $this->showAddressModal = false;
    }

    // 🟢 LƯU VÀO DATABASE THẬT KHI BẤM CHỐT LỊCH
    public function confirmAndLockSchedule()
    {
        if ($this->isLocked || !$this->subscription) return;

        // 1. Cập nhật Subscription trong DB
        $this->subscription->update([
            'address_id' => $this->selectedAddressId,
            'selected_days' => json_encode($this->selectedDayIndexes),
            'is_locked' => true,
            'status' => 'active',
        ]);

        // 2. Lưu Lịch trình 8 đợt vào bảng DeliverySchedule
        foreach ($this->schedulePlan as $plan) {
            DeliverySchedule::updateOrCreate(
                [
                    'subscription_id' => $this->subscription->id,
                    'delivery_order' => $plan['delivery_order'],
                ],
                [
                    'delivery_date' => $plan['delivery_date'],
                    'items_payload' => json_encode($plan['items']),
                    'status' => 'pending',
                ]
            );
        }

        $this->isLocked = true;

        $this->dispatch('swal-success', [
            'title' => 'Đã Chốt Lịch Thành Công!',
            'text' => 'Lịch trình 8 đợt đã được ghim cố định vào hệ thống!'
        ]);
    }

    public function render()
    {
        $user = Auth::user();

        // Query Danh sách Địa chỉ THẬT của User
        $addresses = Address::where('user_id', $user->id)->get();
        $currentAddress = $addresses->firstWhere('id', $this->selectedAddressId) ?? $addresses->first();

        // Query Danh sách Sản phẩm Rau củ THẬT từ DB
        $veggieCatalog = Product::with('productImages')
            ->where('is_active', true)
            ->get();

        $currentDelivery = collect($this->schedulePlan)->firstWhere('delivery_order', $this->activeTab) ?? $this->schedulePlan[0];
        $currentWeight = $this->calculateDeliveryWeight($currentDelivery['items'] ?? [], $veggieCatalog);

        return view('livewire.customer.subscription-setup', [
            'addresses' => $addresses,
            'currentAddress' => $currentAddress,
            'veggieCatalog' => $veggieCatalog,
            'currentDelivery' => $currentDelivery,
            'currentWeight' => $currentWeight,
        ])->layout('layouts.customer.app');
    }
}