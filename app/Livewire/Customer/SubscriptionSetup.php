<?php
namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

    // =========================================================================
    // KHAI BÁO CÁC STATE (THUỘC TÍNH QUẢN LÝ TRẠNG THÁI GIAO DIỆN)
    // =========================================================================
    public ?Subscription $subscription = null; // Đối tượng hợp đồng VIP hiện tại của người dùng
    public float $maxWeightTarget = 3.5;       // Định mức trọng lượng tối đa cho mỗi đợt giao (kg)
    public int $activeTab = 1;                 // Tab đợt giao đang được mở để chọn rau (giá trị từ 1 đến 8)
    public bool $isLocked = false;             // Trạng thái khóa (true: đã chốt lịch thành công, hiển thị chế độ theo dõi)
    public bool $showAddressModal = false;     // Trạng thái bật/tắt modal quản lý và chọn địa chỉ giao hàng

    public int $selectedAddressId = 0;         // ID địa chỉ nhận hàng được khách chọn
    public array $selectedDayIndexes = [2, 5]; // Mảng chứa 2 thứ nhận hàng cố định trong tuần (mặc định: 2 = Thứ 3, 5 = Thứ 6)

    public string $searchVeggie = '';          // Từ khóa tìm kiếm nhanh tên rau củ trong danh mục
    public ?int $selectedCategory = null;      // ID danh mục rau củ đang lọc (null: hiển thị tất cả)

    // Danh mục 7 ngày trong tuần dùng để hiển thị các nút chọn ngày trên giao diện
    public array $weekdays = [
        ['id' => 1, 'label' => 'Thứ 2', 'code' => 'T2', 'dayIndex' => 1],
        ['id' => 2, 'label' => 'Thứ 3', 'code' => 'T3', 'dayIndex' => 2],
        ['id' => 3, 'label' => 'Thứ 4', 'code' => 'T4', 'dayIndex' => 3],
        ['id' => 4, 'label' => 'Thứ 5', 'code' => 'T5', 'dayIndex' => 4],
        ['id' => 5, 'label' => 'Thứ 6', 'code' => 'T6', 'dayIndex' => 5],
        ['id' => 6, 'label' => 'Thứ 7', 'code' => 'T7', 'dayIndex' => 6],
        ['id' => 0, 'label' => 'Chủ Nhật', 'code' => 'CN', 'dayIndex' => 0],
    ];

    // Mảng lưu kế hoạch 8 đợt giao (gồm ngày giao, danh sách món và số lượng) đang dựng trên giao diện
    public array $schedulePlan = [];

    /**
     * Hàm khởi tạo dữ liệu ban đầu khi component được tải
     * - Kiểm tra đăng nhập
     * - Lấy cấu hình định mức cân nặng thời gian thực từ database
     * - Nạp hợp đồng gói VIP gần nhất
     * - Tự động cập nhật hoàn thành nếu đã nhận đủ 8/8 đợt
     * - Nạp lịch trình 8 đợt đã lưu (nếu có) hoặc tạo mới khung 8 đợt
     */
    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            $this->dispatch('open-auth-modal', 'login');
            return;
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

        // 3. Lấy địa chỉ giao hàng mặc định của user
        $defaultAddress = Address::where('user_id', $user->id)->where('is_default', true)->first()
            ?? Address::where('user_id', $user->id)->first();

        if ($defaultAddress) {
            $this->selectedAddressId = $defaultAddress->id;
        }

        // 4. Nếu đã có lịch trình trong DB -> nạp lại đầy đủ kèm trạng thái để hiển thị
        $savedSchedules = DeliverySchedule::where('subscription_id', $this->subscription->id)
            ->orderBy('delivery_order')
            ->get();

        if ($savedSchedules->isNotEmpty()) {
            $this->schedulePlan = [];
            foreach ($savedSchedules as $s) {
                $rawItems = $s->items ?? [];
                $items = [];
                // Chuẩn hóa danh sách items từ JSON sang dạng [product_id => quantity]
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
                    'delivery_date'  => $s->delivery_date->format('Y-m-d'),
                    'status'         => $s->status ?? 'pending',
                    'items'          => $items,
                ];
            }
        } else {
            // Chưa có lịch trong DB -> Khởi tạo khung 8 đợt giao ban đầu
            $this->updateSchedulePlanDates();
        }
    }

    /**
     * Hàm tính toán ra danh sách 8 ngày giao hàng cụ thể sắp tới
     * Dựa trên mảng các thứ trong tuần được chọn (ví dụ: [2, 5] tương đương Thứ 3 và Thứ 6)
     * Vòng lặp tăng dần từ ngày mai, ngày nào khớp với thứ đã chọn thì gom lại cho đến khi đủ 8 ngày
     */
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

    /**
     * Hàm cập nhật ngày giao cho kế hoạch 8 đợt
     * - Nếu chưa có kế hoạch (lần đầu vào trang): Tạo mới khung 8 đợt giao trắng tinh
     * - Nếu đã có kế hoạch (đã chọn rau dở): Dùng tham chiếu &$plan chỉ đổi ngày tháng,
     *   GIỮ NGUYÊN danh sách rau củ đã chọn để khách không bị mất công chọn lại
     */
    public function updateSchedulePlanDates()
    {
        $calculatedDates = $this->generate8DeliveryDates($this->selectedDayIndexes);

        if (empty($this->schedulePlan)) {
            // Nhánh 1: Khởi tạo khung 8 đợt mới tinh
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
            // Nhánh 2: Đã có kế hoạch -> Chỉ cập nhật lại ngày giao mới, giữ nguyên rau đã chọn
            foreach ($this->schedulePlan as $idx => &$plan) {
                if (isset($calculatedDates[$idx])) {
                    $plan['date_label']    = $calculatedDates[$idx]['date_label'];
                    $plan['delivery_date'] = $calculatedDates[$idx]['delivery_date'];
                }
            }
        }
    }

    /**
     * Hàm chọn hoặc bỏ chọn ngày nhận hàng cố định trong tuần
     * - Chặn thao tác nếu hợp đồng đã bị khóa
     * - Nếu bấm vào ngày đang chọn: Cho phép bỏ chọn nhưng chặn nếu chỉ còn 1 ngày (bắt buộc duy trì đủ ngày)
     * - Nếu bấm vào ngày mới: Nếu đã đủ 2 ngày thì cơ chế cuốn chiếu array_shift() sẽ xóa ngày cũ nhất
     *   và nạp ngày mới vào mượt mà
     */
    public function toggleDaySelection(int $dayIdx)
    {
        if ($this->isLocked) return;

        if (in_array($dayIdx, $this->selectedDayIndexes)) {
            // Người dùng muốn bỏ chọn ngày này
            if (count($this->selectedDayIndexes) === 1) {
                $this->dispatch('show-toast', type: 'warning', message: 'Bạn phải chọn đúng 2 ngày nhận hàng cố định trong tuần.');
                return;
            }
            $this->selectedDayIndexes = array_values(array_filter($this->selectedDayIndexes, fn($i) => $i !== $dayIdx));
        } else {
            // Người dùng muốn chọn thêm ngày này
            if (count($this->selectedDayIndexes) >= 2) {
                array_shift($this->selectedDayIndexes); // Bỏ ngày cũ nhất nếu đã đủ 2 ngày
            }
            $this->selectedDayIndexes[] = $dayIdx;
        }

        // Tự động đồng bộ lại lịch 8 đợt theo các thứ vừa chọn
        $this->updateSchedulePlanDates();
    }

    /**
     * Hàm chuyển đổi qua lại giữa các tab đợt giao (từ Đợt 1 đến Đợt 8)
     */
    public function switchTab(int $order)
    {
        $this->activeTab = $order;
    }

    /**
     * Hàm tính toán tổng trọng lượng (kg) của một đợt giao hàng
     * @param array $itemsObj Mảng có cấu trúc [product_id => quantity]
     * @return float Tổng cân nặng làm tròn 2 chữ số thập phân
     * Sử dụng biến static $productWeightCache để tránh query lại cân nặng của cùng một món nhiều lần
     */
    public function calculateDeliveryWeight(array $itemsObj): float
    {
        if (empty($itemsObj)) {
            return 0.0;
        }

        static $productWeightCache = [];

        // Gom các product_id chưa có trong cache để query 1 lần
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

        // Tính tổng cân nặng = số lượng * cân nặng mỗi món
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

    /**
     * Hàm cập nhật số lượng rau củ trong đợt giao hiện tại (Tăng +1 hoặc Giảm -1)
     * @param int $veggieId ID sản phẩm rau củ
     * @param int $delta Biến động số lượng (+1 hoặc -1)
     * - Khi TĂNG (+1):
     *   1. Kiểm tra tồn kho khả dụng theo chuẩn FEFO (chỉ tính các lô còn hạn và active)
     *   2. Đọc định lượng tối đa thời gian thực từ system_settings
     *   3. Chặn nghiêm ngặt nếu món thêm vào làm tổng cân nặng vượt quá $maxWeightTarget
     * - Khi GIẢM (-1):
     *   Nếu số lượng về <= 0 thì tự động xóa món khỏi đợt giao
     */
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
            $product = Product::withTotalStock()->find($veggieId);

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

        // Cập nhật mảng items: nếu về 0 thì gỡ bỏ món, ngược lại ghi nhận số lượng mới
        if ($newQty <= 0) {
            unset($this->schedulePlan[$tabIndex]['items'][$veggieId]);
        } else {
            $this->schedulePlan[$tabIndex]['items'][$veggieId] = $newQty;
        }
    }

    /**
     * Hàm sao chép thực đơn rau củ của đợt hiện tại sang cho toàn bộ 8 đợt giao
     * Giúp khách hàng tiện lợi, không phải bấm chọn lại từng đợt nếu muốn nhận thực đơn giống nhau
     */
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

        $this->dispatch('show-toast', type: 'info', message: "Đã sao chép thực đơn Đợt {$currentPlan['delivery_order']} cho toàn bộ 8 đợt giao!");
    }

    /**
     * Hàm xác nhận và chốt lịch giao hàng cho toàn bộ 8 đợt của gói VIP
     * Quy trình xử lý:
     * 1. Validate bắt buộc phải chọn đủ đúng 2 ngày nhận hàng trong tuần
     * 2. Validate địa chỉ giao hàng và kiểm tra bán kính giao hàng cho phép
     * 3. Validate bắt buộc: Cả 8 đợt giao đều phải chọn đủ định mức cân nặng ($maxWeightTarget kg)
     * 4. Tối ưu gom tất cả product_id trong 8 đợt để query DB đúng 1 lần tránh lỗi N+1
     * 5. Bọc toàn bộ thao tác ghi DB trong DB::transaction để đảm bảo tính toàn vẹn (Commit khi đủ cả 9 bản ghi, Rollback nếu có lỗi)
     */
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

        // Gom tất cả product ID trong 8 đợt để query 1 lần (tối ưu tránh N+1 câu query)
        $allPlanProdIds = [];
        foreach ($this->schedulePlan as $plan) {
            foreach (($plan['items'] ?? []) as $pId => $qty) {
                if ((int) $qty > 0) $allPlanProdIds[] = (int) $pId;
            }
        }
        $productsKeyed = !empty($allPlanProdIds)
            ? Product::whereIn('id', array_unique($allPlanProdIds))->get()->keyBy('id')
            : collect();

        // 5. Bọc Transaction đảm bảo tính toàn vẹn dữ liệu
        try {
            DB::transaction(function () use ($recipientName, $recipientPhone, $recipientAddress, $deliveryDaysStr, $productsKeyed) {
                // A. Cập nhật hợp đồng Subscription trong DB (chuyển sang 'active')
                $this->subscription->update([
                    'recipient_name'    => $recipientName,
                    'recipient_phone'   => $recipientPhone,
                    'recipient_address' => $recipientAddress,
                    'delivery_days'     => $deliveryDaysStr,
                    'status'            => 'active',
                ]);

                // B. Lưu lịch trình 8 đợt vào bảng DeliverySchedule
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
            });

            // Sau khi commit DB thành công -> khóa chỉnh sửa và hiển thị thông báo
            $this->isLocked = true;
            $this->dispatch('show-toast', type: 'success', message: 'Đã chốt lịch nhận rau thành công! Lịch trình 8 đợt đã được lưu cố định vào hệ thống.');
        } catch (\Throwable $e) {
            report($e); // Ghi log lỗi để truy vết khi cần
            $this->dispatch('show-toast', type: 'error', message: 'Có lỗi xảy ra khi lưu lịch nhận rau. Vui lòng thử lại!');
        }
    }

    /**
     * Hàm render: Hiển thị giao diện và chuẩn bị toàn bộ dữ liệu cần thiết
     * - Luôn đọc định lượng tối đa mới nhất từ database
     * - Lấy địa chỉ giao hàng và kiểm tra khoảng cách / bán kính vận chuyển
     * - Query danh mục rau củ khả dụng theo nguyên tắc FEFO (chỉ lấy hàng còn tồn trong lô active và còn hạn)
     * - Tính toán cân nặng cho từng đợt
     * - Khi lịch đã chốt ($isLocked): Tối ưu gom query tránh N+1 để hiển thị chi tiết 8 đợt giao trong bảng theo dõi
     */
    public function render()
    {
        // 1. Luôn đồng bộ định lượng tối đa thời gian thực từ DB
        $latestMaxWeight = SystemSetting::where('key', 'vip_delivery_max_weight')->value('value');
        if ($latestMaxWeight) {
            $this->maxWeightTarget = (float) $latestMaxWeight;
        }

        // Lấy thông tin địa chỉ và khoảng cách giao hàng
        $user = Auth::user();
        $shippingData = $this->resolveShippingAddresses($user?->id);

        $addresses                = $shippingData['addresses'];
        $currentAddress           = $shippingData['address'];
        $currentDist              = $shippingData['distance'];
        $maxDistance              = $shippingData['maxDistance'];
        $isCurrentAddressUnknown  = $shippingData['isDistanceUnknown'];
        $isCurrentAddressExceeded = $shippingData['isDistanceExceeded'];

        // 2. Query Sản phẩm CHUẨN FEFO (chỉ lấy sản phẩm còn hàng trong các lô active và còn hạn sử dụng)
        $productQuery = Product::with([
            'category',
            'productImages' => function ($q) {
                $q->where('is_primary', true);
            }
        ])
        ->withTotalStock()
        ->where('status', 'active')
        ->having('total_stock', '>', 0); // CHỈ HIỆN SẢN PHẨM CÒN HÀNG TRONG KHO

        if ($this->selectedCategory) {
            $productQuery->where('category_id', $this->selectedCategory);
        }

        if (!empty(trim($this->searchVeggie))) {
            $productQuery->where('name', 'like', '%' . trim($this->searchVeggie) . '%');
        }

        $products = $productQuery->get();

        // Chuẩn hóa danh sách sản phẩm để render lên danh mục chọn rau củ
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

        // 3. Tính toán cân nặng cho từng đợt giao
        foreach ($this->schedulePlan as &$p) {
            $p['weight'] = $this->calculateDeliveryWeight($p['items'] ?? []);
        }
        unset($p);

        // Lấy thông tin đợt giao hiện đang được chọn hiển thị
        $currentDelivery = collect($this->schedulePlan)->firstWhere('delivery_order', $this->activeTab) ?? ($this->schedulePlan[0] ?? [
            'delivery_order' => 1,
            'date_label'     => 'Đợt 1',
            'items'          => [],
            'weight'         => 0.0,
            'status'         => 'pending',
        ]);

        $currentWeight = $this->calculateDeliveryWeight($currentDelivery['items'] ?? []);

        // 4. Nếu đã chốt lịch -> Nạp chi tiết 8 đợt để hiển thị bảng Tracker theo dõi tiến độ
        $lockedSchedules = [];
        if ($this->isLocked) {
            $saved = DeliverySchedule::where('subscription_id', $this->subscription->id)
                ->orderBy('delivery_order')
                ->get();

            // =========================================================================
            // ĐOẠN TỐI ƯU TRÁNH LỖI N+1 QUERY KHI LẤY DỮ LIỆU CẢ 8 ĐỢT GIAO:
            // Thay vì duyệt từng đợt rồi chạy query DB tìm sản phẩm cho từng món (hàng chục câu query),
            // ta gom tất cả product_id của 8 đợt vào 1 mảng duy nhất $allProdIds.
            // =========================================================================
            $allProdIds = [];
            foreach ($saved as $ds) {
                $rawItems = $ds->items ?? [];
                foreach ($rawItems as $it) {
                    $pId = $it['product_id'] ?? null;
                    if ($pId) $allProdIds[] = (int) $pId;
                }
            }

            // Thực hiện đúng 1 câu query duy nhất lấy thông tin kèm ảnh của tất cả sản phẩm,
            // dùng keyBy('id') để biến mảng thành dạng [product_id => ProductModel], giúp truy xuất cực nhanh O(1)
            $productsKeyed = !empty($allProdIds) 
                ? Product::with('productImages')->whereIn('id', array_unique($allProdIds))->get()->keyBy('id')
                : collect();

            // Duyệt lại 8 đợt giao để ghép đầy đủ thông tin chi tiết (tên, ảnh, cân nặng, đơn vị)
            foreach ($saved as $ds) {
                $rawItems = $ds->items ?? [];
                $itemsDetail = [];
                $w = 0.0;
                foreach ($rawItems as $it) {
                    $pId = $it['product_id'] ?? null;
                    $q = (int) ($it['quantity'] ?? 1);
                    $prod = $pId ? ($productsKeyed->get($pId)) : null;

                    // Xử lý lấy ảnh đại diện của món rau:
                    // 1. Ưu tiên ảnh chính (is_primary = true)
                    // 2. Nếu không có thì lấy ảnh đầu tiên trong danh sách ảnh của sản phẩm
                    $rawImg = $prod?->productImages?->firstWhere('is_primary', true)?->image 
                        ?? $prod?->productImages?->first()?->image;

                    // Chuẩn hóa link ảnh:
                    // - Nếu link bắt đầu bằng 'http' (link ngoài / CDN) thì giữ nguyên
                    // - Nếu là đường dẫn tương đối trong storage thì chuyển qua asset('storage/...')
                    // - Nếu sản phẩm chưa có ảnh thì dùng ảnh placeholder mặc định từ Unsplash
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