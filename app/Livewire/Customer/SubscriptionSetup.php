<?php
namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Subscription;
use App\Models\Address;
use App\Models\Product;
use App\Models\Category;
use App\Models\DeliverySchedule;
use App\Models\SystemSetting;
use App\Livewire\Traits\WithShippingAddress;

#[Title('Thiết Lập Lịch & Theo Dõi Gói VIP | Nông Trại Đất Lành')]
class SubscriptionSetup extends Component
{
    use WithShippingAddress;

    public ?Subscription $subscription = null;
    public float $maxWeightTarget = 3.5;
    public int $activeTab = 1;
    public bool $isLocked = false;
    public bool $showAddressModal = false;

    public int $selectedAddressId = 0;
    public array $selectedDayIndexes = [2, 5]; 

    public string $searchVeggie = '';
    public ?int $selectedCategory = null;

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

    // khởi tạo dữ liệu ban đầu
    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            session()->flash('error', 'Vui lòng đăng nhập để thiết lập Gói VIP.');
            return $this->redirect(route('login'), navigate: true);
        }

        // 1. Đọc định lượng tối đa thời gian thực từ system_settings
        $settingMaxWeight = SystemSetting::where('key', 'vip_delivery_max_weight')->value('value');
        if ($settingMaxWeight) {
            $this->maxWeightTarget = (float) $settingMaxWeight;
        }

        // 2. Lấy hợp đồng Gói VIP gần nhất của user (active hoặc completed)
        $this->subscription = Subscription::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$this->subscription) {
            session()->flash('error', 'Bạn chưa có gói đăng ký VIP nào cần thiết lập.');
            return $this->redirect(route('customer.vip.checkout'), navigate: true);
        }

        // Tự động kiểm tra nếu hợp đồng active đã hoàn thành đủ 8/8 đợt giao -> chuyển completed
        if ($this->subscription->status === 'active') {
            $deliveredCount = (int) $this->subscription->deliverySchedules()->where('status', 'delivered')->count();
            if ($deliveredCount >= 8) {
                $this->subscription->update(['status' => 'completed']);
            }
        }

        // Hợp đồng được xem là đã khóa khi đã chốt ngày giao và có lịch trình trong DB
        $hasSchedules = DeliverySchedule::where('subscription_id', $this->subscription->id)->exists();
        $this->isLocked = in_array($this->subscription->status, ['active', 'completed']) && !empty($this->subscription->delivery_days) && $hasSchedules;

        // 3. Lấy địa chỉ giao hàng
        $defaultAddress = Address::where('user_id', $user->id)->where('is_default', true)->first()
            ?? Address::where('user_id', $user->id)->first();

        if ($defaultAddress) {
            $this->selectedAddressId = $defaultAddress->id;
        }

        // 5. Nếu đã có lịch trình trong DB -> nạp lại đầy đủ kèm trạng thái
        $savedSchedules = DeliverySchedule::where('subscription_id', $this->subscription->id)
            ->orderBy('delivery_order')
            ->get();

        if ($savedSchedules->isNotEmpty()) {
            $this->schedulePlan = [];
            foreach ($savedSchedules as $s) {
                $rawItems = $s->items ?? [];
                $items = [];
                foreach ($rawItems as $k => $v) {
                    if (is_array($v)) {
                        $pId = $v['product_id'] ?? null;
                        $q = $v['quantity'] ?? 1;
                        if ($pId) $items[(int) $pId] = (int) $q;
                    }
                }
                $dateObj = new \DateTime($s->delivery_date);
                $dayOfWeek = (int) $dateObj->format('w');
                $wObj = collect($this->weekdays)->firstWhere('dayIndex', $dayOfWeek);
                $code = $wObj['code'] ?? 'T' . ($dayOfWeek + 1);

                $this->schedulePlan[] = [
                    'delivery_order' => $s->delivery_order,
                    'date_label'     => $code . ' (' . $dateObj->format('d/m') . ')',
                    'delivery_date'  => $s->delivery_date instanceof \Illuminate\Support\Carbon ? $s->delivery_date->format('Y-m-d') : (string) $s->delivery_date,
                    'status'         => $s->status ?? 'pending',
                    'items'          => $items,
                ];
            }
        } else {
            // Khởi tạo khung 8 đợt giao
            $this->updateSchedulePlanDates();
        }
    }

    // tính toán 8 ngày giao hàng
    public function generate8DeliveryDates(array $daysArray): array
    {
        if (empty($daysArray)) {
            return [];
        }

        $dates = [];
        $current = new \DateTime();

        while (count($dates) < 8) {
            $current->modify('+1 day');
            $dayOfWeek = (int) $current->format('w');

            if (in_array($dayOfWeek, $daysArray)) {
                $weekdayObj = collect($this->weekdays)->firstWhere('dayIndex', $dayOfWeek);
                $dates[] = [
                    'code'          => $weekdayObj['code'],
                    'date_label'    => $weekdayObj['code'] . ' (' . $current->format('d/m') . ')',
                    'delivery_date' => $current->format('Y-m-d'),
                ];
            }
        }
        return $dates;
    }

    // cập nhật ngày giao cho kế hoạch
    public function updateSchedulePlanDates()
    {
        $calculatedDates = $this->generate8DeliveryDates($this->selectedDayIndexes);

        if (empty($this->schedulePlan)) {
            foreach ($calculatedDates as $idx => $d) {
                $this->schedulePlan[] = [
                    'delivery_order' => $idx + 1,
                    'date_label'     => $d['date_label'],
                    'delivery_date'  => $d['delivery_date'],
                    'status'         => 'pending',
                    'items'          => [],
                    'weight'         => 0.0,
                ];
            }
        } else {
            foreach ($this->schedulePlan as $idx => &$plan) {
                if (isset($calculatedDates[$idx])) {
                    $plan['date_label']    = $calculatedDates[$idx]['date_label'];
                    $plan['delivery_date'] = $calculatedDates[$idx]['delivery_date'];
                }
            }
        }
    }

    // chọn/bỏ chọn ngày giao trong tuần
    public function toggleDaySelection(int $dayIdx)
    {
        if ($this->isLocked) return;

        if (in_array($dayIdx, $this->selectedDayIndexes)) {
            if (count($this->selectedDayIndexes) === 1) {
                $this->dispatch('show-toast', type: 'warning', message: 'Bạn phải chọn đúng 2 ngày nhận hàng cố định trong tuần.');
                return;
            }
            $this->selectedDayIndexes = array_values(array_filter($this->selectedDayIndexes, fn($i) => $i !== $dayIdx));
        } else {
            if (count($this->selectedDayIndexes) >= 2) {
                array_shift($this->selectedDayIndexes);
            }
            $this->selectedDayIndexes[] = $dayIdx;
        }

        $this->updateSchedulePlanDates();
    }

    // đổi tab
    public function switchTab(int $order)
    {
        $this->activeTab = $order;
    }

    // tính toán trọng lượng
    public function calculateDeliveryWeight(array $itemsObj): float
    {
        if (empty($itemsObj)) {
            return 0.0;
        }

        static $productWeightCache = [];

        $productIds = [];
        foreach ($itemsObj as $pId => $qty) {
            if ($qty > 0 && !isset($productWeightCache[$pId])) {
                $productIds[] = (int) $pId;
            }
        }

        if (!empty($productIds)) {
            $fetched = Product::whereIn('id', array_unique($productIds))->get();
            foreach ($fetched as $p) {
                $productWeightCache[$p->id] = (float) ($p->weight_kg ?? 0.5);
            }
        }

        $total = 0.0;
        foreach ($itemsObj as $pId => $qty) {
            $q = (int) $qty;
            if ($q > 0) {
                $weightEach = $productWeightCache[$pId] ?? 0.5;
                $total += $q * $weightEach;
            }
        }

        return round($total, 2);
    }

    // cập nhật số lượng món rau củ
    public function updateQty(int $veggieId, int $delta)
    {
        if ($this->isLocked) return;

        $tabIndex = array_search($this->activeTab, array_column($this->schedulePlan, 'delivery_order'));
        if ($tabIndex === false) return;

        $currentItems = $this->schedulePlan[$tabIndex]['items'] ?? [];
        $currentWeight = $this->calculateDeliveryWeight($currentItems);

        $currentQty = $currentItems[$veggieId] ?? 0;
        $newQty = $currentQty + $delta;

        // Kiểm tra tồn kho và khống chế cân nặng khi khách bấm TĂNG (+)
        if ($delta > 0) {
            // 1. Kiểm tra tồn kho FEFO thực tế của sản phẩm
            $product = Product::withSum(['batches as total_stock' => function ($q) {
                $q->where('product_batches.status', 'active')
                  ->where('product_batches.current_quantity', '>', 0)
                  ->whereDate('product_batches.expiry_date', '>=', now());
            }], 'current_quantity')->find($veggieId);

            if (!$product) {
                $this->dispatch('show-toast', type: 'error', message: 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh!');
                return;
            }

            $availableStock = (int) ($product->total_stock ?? 0);
            if ($newQty > $availableStock) {
                $this->dispatch('show-toast', type: 'warning', message: "Món {$product->name} trong kho hiện chỉ còn {$availableStock} đơn vị khả dụng!");
                return;
            }

            // 2. Đồng bộ realtime định lượng tối đa từ DB
            $latestMaxWeight = SystemSetting::where('key', 'vip_delivery_max_weight')->value('value');
            if ($latestMaxWeight) {
                $this->maxWeightTarget = (float) $latestMaxWeight;
            }

            // 3. Khống chế nghiêm ngặt TUYỆT ĐỐI KHÔNG ĐƯỢC VƯỢT QUÁ định mức cài đặt (maxWeightTarget)
            $itemWeight = $product?->weight_kg ?? 0.5;
            if (($currentWeight + $itemWeight) > ($this->maxWeightTarget + 0.001)) {
                $this->dispatch('show-toast', type: 'warning', message: "Mỗi đợt giao nhận tối đa {$this->maxWeightTarget} kg rau củ. Món này ({$itemWeight} kg) sẽ làm vượt quá định mức cài đặt!");
                return;
            }
        }

        if ($newQty <= 0) {
            unset($this->schedulePlan[$tabIndex]['items'][$veggieId]);
        } else {
            $this->schedulePlan[$tabIndex]['items'][$veggieId] = $newQty;
        }
    }

    // sao chép thực đơn cho tất cả các đợt
    public function copyMenuToAllDeliveries()
    {
        if ($this->isLocked) return;

        $currentPlan = collect($this->schedulePlan)->firstWhere('delivery_order', $this->activeTab);
        if (!$currentPlan || empty($currentPlan['items'])) {
            $this->dispatch('show-toast', type: 'warning', message: 'Vui lòng chọn thực đơn cho đợt này trước khi sao chép!');
            return;
        }

        $itemsToCopy = $currentPlan['items'];
        foreach ($this->schedulePlan as &$plan) {
            $plan['items'] = $itemsToCopy;
        }

        $this->dispatch('show-toast', type: 'success', message: "Đã sao chép thực đơn Đợt {$currentPlan['delivery_order']} cho toàn bộ 8 đợt giao!");
    }

    // xác nhận và chốt lịch giao hàng
    public function confirmAndLockSchedule()
    {
        if ($this->isLocked || !$this->subscription) return;

        // 1. Kiểm tra ngày giao: Phải chọn đúng 2 ngày trong tuần
        if (count($this->selectedDayIndexes) !== 2) {
            $this->dispatch('show-toast', type: 'warning', message: 'Bạn phải chọn đúng 2 ngày nhận hàng cố định trong tuần!');
            return;
        }

        // 2. Kiểm tra địa chỉ giao hàng & bán kính giao hàng
        $address = $this->selectedAddress;
        $maxDistance = $this->getMaxShippingDistance();
        $distance = $this->getShippingDistance($address?->latitude, $address?->longitude);

        if (!$this->validateShippingAddress($address, $distance, $maxDistance)) {
            return;
        }

        // 3. VALIDATE BẮT BUỘC: CẢ 8 ĐỢT ĐỀU PHẢI CHỌN ĐỦ ĐÚNG ĐỊNH MỨC CÂN NẶNG
        foreach ($this->schedulePlan as $plan) {
            $weight = $this->calculateDeliveryWeight($plan['items']);
            
            // Nếu đợt rỗng chưa có món nào
            if ($weight <= 0) {
                $this->activeTab = $plan['delivery_order'];
                $this->dispatch('show-toast', type: 'warning', message: "Đợt {$plan['delivery_order']} ({$plan['date_label']}) chưa chọn món nào. Vui lòng chọn đủ {$this->maxWeightTarget}kg rau củ!");
                return;
            }

            // Nếu đợt chưa đủ định mức cân nặng
            if ($weight < $this->maxWeightTarget) {
                $this->activeTab = $plan['delivery_order'];
                $this->dispatch('show-toast', type: 'warning', message: "Đợt {$plan['delivery_order']} ({$plan['date_label']}) mới chọn {$weight}kg / {$this->maxWeightTarget}kg. Vui lòng chọn đủ {$this->maxWeightTarget}kg rau củ!");
                return;
            }
        }

        // 4. Chuẩn bị dữ liệu cập nhật
        $dayCodes = [];
        foreach ($this->selectedDayIndexes as $dIdx) {
            $wObj = collect($this->weekdays)->firstWhere('dayIndex', $dIdx);
            if ($wObj) $dayCodes[] = $wObj['code'];
        }
        $deliveryDaysStr = implode(',', $dayCodes);

        $recipientName = $address->recipient_name;
        $recipientPhone = $address->recipient_phone;
        $recipientAddress = $address->full_address;

        // Cập nhật hợp đồng Subscription trong DB
        $this->subscription->update([
            'recipient_name'    => $recipientName,
            'recipient_phone'   => $recipientPhone,
            'recipient_address' => $recipientAddress,
            'delivery_days'     => $deliveryDaysStr,
            'status'            => 'active',
        ]);

        // Gom tất cả product ID trong 8 đợt để query 1 lần (tối ưu tránh N+1)
        $allPlanProdIds = [];
        foreach ($this->schedulePlan as $plan) {
            foreach (($plan['items'] ?? []) as $pId => $qty) {
                if ((int) $qty > 0) $allPlanProdIds[] = (int) $pId;
            }
        }
        $productsKeyed = !empty($allPlanProdIds)
            ? Product::whereIn('id', array_unique($allPlanProdIds))->get()->keyBy('id')
            : collect();

        // Lưu lịch trình 8 đợt vào bảng DeliverySchedule
        foreach ($this->schedulePlan as $plan) {
            $formattedItems = [];
            foreach (($plan['items'] ?? []) as $pId => $qty) {
                $q = (int) $qty;
                if ($q > 0) {
                    $prod = $productsKeyed->get($pId);
                    $itemWeight = $prod?->weight_kg ?? 0.5;
                    $formattedItems[] = [
                        'product_id'   => (int) $pId,
                        'product_name' => $prod?->name ?? "Rau hữu cơ #$pId",
                        'quantity'     => $q,
                        'unit'         => $prod?->unit ?? 'kg',
                        'weight'       => round($q * $itemWeight, 2),
                    ];
                }
            }

            DeliverySchedule::updateOrCreate(
                [
                    'subscription_id' => $this->subscription->id,
                    'delivery_order'  => $plan['delivery_order'],
                ],
                [
                    'delivery_date'   => $plan['delivery_date'],
                    'items'           => $formattedItems,
                    'status'          => 'pending',
                ]
            );
        }

        $this->isLocked = true;

        $this->dispatch('show-toast', type: 'success', message: 'Đã chốt lịch nhận rau thành công! Lịch trình 8 đợt đã được lưu cố định vào hệ thống.');
    }

    // hiển thị giao diện và chuẩn bị dữ liệu
    public function render()
    {
        // Luôn đồng bộ định lượng tối đa thời gian thực từ DB
        $latestMaxWeight = SystemSetting::where('key', 'vip_delivery_max_weight')->value('value');
        if ($latestMaxWeight) {
            $this->maxWeightTarget = (float) $latestMaxWeight;
        }

        $user = Auth::user();
        $shippingData = $this->resolveShippingAddresses($user?->id);

        $addresses                = $shippingData['addresses'];
        $currentAddress           = $shippingData['address'];
        $currentDist              = $shippingData['distance'];
        $maxDistance              = $shippingData['maxDistance'];
        $isCurrentAddressUnknown  = $shippingData['isDistanceUnknown'];
        $isCurrentAddressExceeded = $shippingData['isDistanceExceeded'];

        // 2. Query Sản phẩm CHUẨN FEFO (chỉ lấy sản phẩm còn hàng trong các lô active và còn hạn)
        $productQuery = Product::with([
            'category',
            'productImages' => function ($q) {
                $q->where('is_primary', true);
            }
        ])
        ->withSum(['batches as total_stock' => function ($q) {
            $q->where('product_batches.status', 'active')
              ->where('product_batches.current_quantity', '>', 0)
              ->whereDate('product_batches.expiry_date', '>=', now());
        }], 'current_quantity')
        ->where('status', 'active')
        ->having('total_stock', '>', 0); // CHỈ HIỆN SẢN PHẨM CÒN HÀNG

        if ($this->selectedCategory) {
            $productQuery->where('category_id', $this->selectedCategory);
        }

        if (!empty(trim($this->searchVeggie))) {
            $productQuery->where('name', 'like', '%' . trim($this->searchVeggie) . '%');
        }

        $products = $productQuery->get();

        $veggieCatalog = $products->map(function ($p) {
            $rawImg = $p->productImages?->firstWhere('is_primary', true)?->image 
                ?? $p->productImages?->first()?->image;
            $imgUrl = $rawImg 
                ? (str_starts_with($rawImg, 'http') ? $rawImg : asset('storage/' . $rawImg))
                : 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=300';

            return [
                'id'         => $p->id,
                'name'       => $p->name,
                'slug'       => $p->slug,
                'image'      => $imgUrl,
                'stock'      => (int) ($p->total_stock ?? 0),
                'unit'       => $p->unit ?? 'kg',
                'unit_label' => $p->unit ?? 'kg',
                'weight'     => $p->weight_kg,
            ];
        })->toArray();

        // 3. Tính toán cân nặng cho từng đợt
        foreach ($this->schedulePlan as &$p) {
            $p['weight'] = $this->calculateDeliveryWeight($p['items'] ?? []);
        }
        unset($p);

        $currentDelivery = collect($this->schedulePlan)->firstWhere('delivery_order', $this->activeTab) ?? ($this->schedulePlan[0] ?? [
            'delivery_order' => 1,
            'date_label'     => 'Đợt 1',
            'items'          => [],
            'weight'         => 0.0,
            'status'         => 'pending',
        ]);

        $currentWeight = $this->calculateDeliveryWeight($currentDelivery['items'] ?? []);

        // 4. Nếu đã chốt lịch -> Nạp chi tiết 8 đợt để hiển thị bảng Tracker (Tối ưu gom query tránh N+1)
        $lockedSchedules = [];
        if ($this->isLocked) {
            $saved = DeliverySchedule::where('subscription_id', $this->subscription->id)
                ->orderBy('delivery_order')
                ->get();

            // Gom tất cả product_id để query 1 lần
            $allProdIds = [];
            foreach ($saved as $ds) {
                $rawItems = $ds->items ?? [];
                foreach ($rawItems as $it) {
                    $pId = $it['product_id'] ?? $it['id'] ?? null;
                    if ($pId) $allProdIds[] = (int) $pId;
                }
            }
            $productsKeyed = !empty($allProdIds) 
                ? Product::with('productImages')->whereIn('id', array_unique($allProdIds))->get()->keyBy('id')
                : collect();

            foreach ($saved as $ds) {
                $rawItems = $ds->items ?? [];
                $itemsDetail = [];
                $w = 0.0;
                foreach ($rawItems as $it) {
                    $pId = $it['product_id'] ?? $it['id'] ?? null;
                    $q = (int) ($it['quantity'] ?? $it['qty'] ?? 1);
                    $prod = $pId ? ($productsKeyed->get($pId)) : null;
                    $rawImg = $prod?->productImages?->firstWhere('is_primary', true)?->image 
                        ?? $prod?->productImages?->first()?->image;
                    $imgUrl = $rawImg 
                        ? (str_starts_with($rawImg, 'http') ? $rawImg : asset('storage/' . $rawImg))
                        : 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=100';

                    $itemWeight = isset($it['weight']) ? (float)$it['weight'] : round($q * ($prod?->weight_kg ?? 0.5), 2);
                    $itemsDetail[] = [
                        'name'     => $it['product_name'] ?? $prod?->name ?? 'Rau hữu cơ',
                        'quantity' => $q,
                        'unit'     => $it['unit'] ?? $prod?->unit ?? 'kg',
                        'weight'   => $itemWeight,
                        'image'    => $imgUrl,
                        'slug'     => $prod?->slug ?? '',
                    ];
                    $w += $itemWeight;
                }

                $lockedSchedules[] = [
                    'order'       => $ds->delivery_order,
                    'date'        => $ds->delivery_date?->format('d/m/Y') ?? (string)$ds->delivery_date,
                    'day_of_week' => $ds->delivery_date?->format('l') ?? '',
                    'status'      => $ds->status ?? 'pending',
                    'total_weight'=> round($w, 1),
                    'items'       => $itemsDetail,
                ];
            }
        }

        $categories = Category::all(['id', 'name']);

        return view('livewire.customer.subscription-setup', [
            'isLocked'                 => $this->isLocked,
            'selectedDayIndexes'       => $this->selectedDayIndexes,
            'schedulePlan'             => $this->schedulePlan,
            'activeTab'                => $this->activeTab,
            'weekdays'                 => $this->weekdays,
            'showAddressModal'         => $this->showAddressModal,
            'selectedAddressId'        => $this->selectedAddressId,
            'addresses'                => $addresses,
            'address'                  => $currentAddress,
            'distance'                 => $currentDist,
            'isDistanceUnknown'        => $isCurrentAddressUnknown,
            'currentAddress'           => $currentAddress,
            'veggieCatalog'            => $veggieCatalog,
            'currentDelivery'          => $currentDelivery,
            'currentWeight'            => $currentWeight,
            'maxWeightTarget'          => $this->maxWeightTarget,
            'categories'               => $categories,
            'selectedCategory'         => $this->selectedCategory,
            'searchVeggie'             => $this->searchVeggie,
            'lockedSchedules'          => $lockedSchedules,
            'deliveredCount'           => (int) ($this->subscription?->deliverySchedules()->where('status', 'delivered')->count() ?? 0),
            'maxDistance'              => $maxDistance,
            'isCurrentAddressExceeded' => $isCurrentAddressExceeded,
        ])->layout('layouts.customer.app');
    }
}