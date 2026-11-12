<?php

namespace App\Livewire\Sales;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.sales.app')]
#[Title('Cài Đặt Hệ Thống')]
class SystemSettingManager extends Component
{
    public string $activeTab = 'shipping';

    // Nhóm shipping
    public string $max_shipping_distance_km = '25';

    // Nhóm loyalty
    public string $spending_per_point = '10000';
    public string $value_per_point = '100';
    public string $max_point_discount_percent = '10';

    // Nhóm subscription
    public bool $vip_subscription_enabled = true;
    public string $vip_package_name = 'Gói Bếp Xanh VIP Gia Đình (1 Tháng)';
    public string $vip_package_price = '10000';
    public string $vip_delivery_max_weight = '3';

    // Nhóm contact
    public string $hotline = '0987.654.321';
    public string $email = 'lienhe@datlanhfarm.vn';
    public string $farm_address = 'Số 86 đường Lê Thánh Tôn, Phường Sài Gòn, Quận 1, Thành phố Hồ Chí Minh';
    public string $farm_latitude = '10.7769';
    public string $farm_longitude = '106.7009';

    // Khởi tạo component, kiểm tra quyền Sales và nạp cấu hình từ DB.
    public function mount(): void
    {
        $this->ensureSales();
        $this->loadSettings();
    }

    // Chuyển tab nhóm cấu hình và xóa lỗi validation cũ.
    public function switchTab(string $tab): void
    {
        if (array_key_exists($tab, $this->groupSpecs())) {
            $this->activeTab = $tab;
            $this->resetValidation();
        }
    }

    // Validate, lưu toàn bộ tham số của một nhóm vào system_settings và xóa cache liên quan.
    public function saveGroup(string $group): void
    {
        $this->ensureSales();

        $specs = $this->groupSpecs();
        if (!isset($specs[$group])) {
            return;
        }

        $spec = $specs[$group];
        $validated = $this->validate($spec['rules'], $this->validationMessages());

        DB::transaction(function () use ($group, $spec, $validated) {
            foreach ($spec['keys'] as $key) {
                $value = $key === 'vip_subscription_enabled'
                    ? ($validated[$key] ? 'true' : 'false')
                    : trim((string) $validated[$key]);

                if ($key !== 'vip_subscription_enabled') {
                    $this->{$key} = $value;
                }

                SystemSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => $group]
                );
            }
        });

        foreach ($spec['caches'] as $cacheKey) {
            Cache::forget($cacheKey);
        }

        $this->dispatch('show-toast', type: 'success', message: $spec['successMessage']);
    }

    // Nạp toàn bộ giá trị hiện có từ bảng system_settings lên các biến form.
    private function loadSettings(): void
    {
        $settings = SystemSetting::pluck('value', 'key');

        foreach ([
            'max_shipping_distance_km',
            'spending_per_point',
            'value_per_point',
            'max_point_discount_percent',
            'vip_package_name',
            'vip_package_price',
            'vip_delivery_max_weight',
            'hotline',
            'email',
            'farm_address',
            'farm_latitude',
            'farm_longitude',
        ] as $key) {
            if ($settings->has($key) && $settings->get($key) !== null) {
                $this->{$key} = (string) $settings->get($key);
            }
        }

        if ($settings->has('vip_subscription_enabled')) {
            $this->vip_subscription_enabled = filter_var(
                $settings->get('vip_subscription_enabled'),
                FILTER_VALIDATE_BOOLEAN
            );
        }
    }

    // Đảm bảo chỉ nhân viên Sales đang hoạt động mới được truy cập và lưu cấu hình.
    private function ensureSales(): User
    {
        $user = Auth::user();

        abort_unless(
            $user && $user->status === 'active' && $user->role === 'sales',
            403
        );

        return $user;
    }

    // Khai báo danh sách key, luật validate, cache cần xóa và thông báo cho từng nhóm tab.
    private function groupSpecs(): array
    {
        return [
            'shipping' => [
                'keys' => ['max_shipping_distance_km'],
                'rules' => [
                    'max_shipping_distance_km' => ['required', 'numeric', 'min:1', 'max:500'],
                ],
                'caches' => ['system_max_shipping_distance'],
                'successMessage' => 'Đã lưu cấu hình vận chuyển!',
            ],
            'loyalty' => [
                'keys' => [
                    'spending_per_point',
                    'value_per_point',
                    'max_point_discount_percent',
                ],
                'rules' => [
                    'spending_per_point' => ['required', 'integer', 'min:1000', 'max:100000000'],
                    'value_per_point' => ['required', 'integer', 'min:1', 'max:10000000'],
                    'max_point_discount_percent' => ['required', 'numeric', 'min:1', 'max:100'],
                ],
                'caches' => ['system_loyalty_settings'],
                'successMessage' => 'Đã lưu cấu hình điểm thưởng!',
            ],
            'subscription' => [
                'keys' => [
                    'vip_subscription_enabled',
                    'vip_package_name',
                    'vip_package_price',
                    'vip_delivery_max_weight',
                ],
                'rules' => [
                    'vip_subscription_enabled' => ['boolean'],
                    'vip_package_name' => ['required', 'string', 'max:255'],
                    'vip_package_price' => ['required', 'integer', 'min:1000', 'max:100000000'],
                    'vip_delivery_max_weight' => ['required', 'numeric', 'min:0.5', 'max:50'],
                ],
                'caches' => [],
                'successMessage' => 'Đã lưu cấu hình Gói Rau VIP!',
            ],
            'contact' => [
                'keys' => [
                    'hotline',
                    'email',
                    'farm_address',
                    'farm_latitude',
                    'farm_longitude',
                ],
                'rules' => [
                    'hotline' => ['required', 'string', 'max:50'],
                    'email' => ['required', 'email', 'max:255'],
                    'farm_address' => ['required', 'string', 'max:500'],
                    'farm_latitude' => ['required', 'numeric', 'between:-90,90'],
                    'farm_longitude' => ['required', 'numeric', 'between:-180,180'],
                ],
                'caches' => ['system_farm_coordinates', 'system_contact_settings'],
                'successMessage' => 'Đã lưu thông tin & tọa độ nông trại!',
            ],
        ];
    }

    // Các thông báo lỗi validation tiếng Việt dùng chung cho form.
    private function validationMessages(): array
    {
        return [
            'required' => 'Vui lòng nhập thông tin này.',
            'numeric' => 'Giá trị phải là số hợp lệ.',
            'integer' => 'Giá trị phải là số nguyên.',
            'email' => 'Email liên hệ không đúng định dạng.',
            'between' => 'Giá trị nằm ngoài phạm vi hợp lệ.',
            'min' => 'Giá trị phải lớn hơn hoặc bằng :min.',
            'max' => 'Giá trị không được vượt quá :max.',
        ];
    }

    // Hiển thị giao diện Cài Đặt Hệ Thống của Sales.
    public function render()
    {
        return view('livewire.sales.system-setting-manager');
    }
}