<?php

namespace App\Livewire\Admin;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Layout('layouts.admin.app')]
#[Title('Cấu Hình Hệ Thống | GreenFarm Admin')]
class SettingManager extends Component
{
    public const TABS = [
        'contact' => [
            'label' => 'Thông tin thương hiệu',
            'icon' => 'fa-store',
            'description' => 'Thông tin liên hệ, thương hiệu và tọa độ dùng để tính khoảng cách giao hàng.',
        ],
        'shipping' => [
            'label' => 'Vận chuyển',
            'icon' => 'fa-truck-fast',
            'description' => 'Giới hạn khoảng cách từ nông trại đến địa chỉ nhận hàng.',
        ],
        'loyalty' => [
            'label' => 'Điểm thưởng thân thiết',
            'icon' => 'fa-coins',
            'description' => 'Chính sách tích điểm và đổi điểm khi khách hàng thanh toán.',
        ],
        'subscription' => [
            'label' => 'Gói rau định kỳ VIP',
            'icon' => 'fa-crown',
            'description' => 'Trạng thái mở bán, tên gói, giá gói và định lượng mỗi đợt giao.',
        ],
    ];

    #[Locked]
    public string $activeTab = 'contact';

    // Thương hiệu, liên hệ và tọa độ.
    public string $farm_name = SystemSetting::DEFAULT_FARM_NAME;
    public string $hotline = '0901 234 567';
    public string $email = 'contact@datlanhfarm.vn';
    public string $farm_address = 'Thung lũng Đạ Ròn, Huyện Đơn Dương, Tỉnh Lâm Đồng';
    public string $farm_latitude = '10.7769';
    public string $farm_longitude = '106.7009';

    // Vận chuyển.
    public string $max_shipping_distance_km = '25';

    // Điểm thưởng.
    public string $spending_per_point = '10000';
    public string $value_per_point = '100';
    public string $max_point_discount_percent = '10';

    // Gói VIP.
    public bool $vip_subscription_enabled = true;
    public string $vip_package_name = 'Gói Bếp Xanh VIP Gia Đình (1 Tháng)';
    public string $vip_package_price = '10000';
    public string $vip_delivery_max_weight = '3';

    #[Locked]
    public ?string $confirmationGroup = null;

    #[Locked]
    public ?string $confirmationHash = null;

    public function boot(): void
    {
        $user = Auth::user();

        abort_unless(
            $user && $user->role === 'admin' && $user->status === 'active',
            403
        );
    }

    public function mount(): void
    {
        $keys = [];

        foreach ($this->groupSpecs() as $spec) {
            $keys = array_merge($keys, array_keys($spec['rules']));
        }

        $settings = SystemSetting::whereIn('key', $keys)
            ->pluck('value', 'key');

        foreach ($settings as $key => $value) {
            if ($value === null) {
                continue;
            }

            $this->{$key} = $key === 'vip_subscription_enabled'
                ? filter_var($value, FILTER_VALIDATE_BOOLEAN)
                : (string) $value;
        }
    }

    public function switchTab(string $tab): void
    {
        abort_unless(isset(self::TABS[$tab]), 422);

        $this->activeTab = $tab;
        $this->cancelSave();
        $this->resetValidation();
    }

    public function updated(string $property): void
    {
        foreach ($this->groupSpecs() as $spec) {
            if (array_key_exists($property, $spec['rules'])) {
                $this->cancelSave();
                $this->resetValidation([$property, 'save']);

                return;
            }
        }
    }

    // Bước 1: kiểm tra dữ liệu của tab trước khi mở xác nhận.
    public function requestSave(string $group): void
    {
        $this->cancelSave();
        $this->resetValidation();

        abort_unless(
            isset(self::TABS[$group]) && $group === $this->activeTab,
            422
        );

        // Kiểm tra dữ liệu trước.
        // Nếu sai, Livewire hiển thị lỗi dưới từng trường.
        $data = $this->validatedSettings($group);

        // Không thay đổi: chỉ hiện thông báo thông tin.
        if (!$this->hasSettingsChanges($group, $data)) {
            $this->dispatch(
                'swal',
                icon: 'info',
                title: 'Không có thay đổi nào'
            );

            return;
        }

        // Có thay đổi: ghi nhận dữ liệu để chờ xác nhận.
        $this->confirmationGroup = $group;
        $this->confirmationHash = $this->settingsHash($group, $data);

        $this->dispatch(
            'confirm-setting-save',
            title: 'Xác nhận lưu: ' . self::TABS[$group]['label'] . '?'
        )->self();
    }

    public function cancelSave(): void
    {
        $this->confirmationGroup = null;
        $this->confirmationHash = null;
    }

    // Bước 2: lưu đúng nhóm và dữ liệu đã được xác nhận.
    public function saveConfirmed(): void
    {
        $group = $this->confirmationGroup;
        $expectedHash = $this->confirmationHash;

        $this->cancelSave();

        if (
            $group === null
            || $expectedHash === null
            || !isset(self::TABS[$group])
            || $group !== $this->activeTab
        ) {
            $this->addError(
                'save',
                'Vui lòng nhấn Lưu và xác nhận lại.'
            );

            return;
        }

        $data = $this->validatedSettings($group);

        if (!hash_equals($expectedHash, $this->settingsHash($group, $data))) {
            $this->addError(
                'save',
                'Dữ liệu đã thay đổi. Vui lòng nhấn Lưu để xác nhận lại.'
            );

            return;
        }

        if (!$this->hasSettingsChanges($group, $data)) {
            $this->dispatch(
                'swal',
                icon: 'info',
                title: 'Không có thay đổi nào'
            );

            return;
        }

        try {
            DB::transaction(function () use ($group, $data): void {
                foreach ($data as $key => $value) {
                    $storedValue = is_bool($value)
                        ? ($value ? 'true' : 'false')
                        : (string) $value;

                    SystemSetting::updateOrCreate(
                        ['key' => $key],
                        [
                            'value' => $storedValue,
                            'group' => $group,
                        ]
                    );
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            $this->addError(
                'save',
                'Chưa thể lưu cấu hình. Vui lòng thử lại.'
            );

            return;
        }

        $this->fill($data);
        $this->resetValidation();

        // Database đã lưu thành công; xử lý cache riêng.
        $cacheFailed = false;

        foreach ($this->groupSpecs()[$group]['caches'] as $cacheKey) {
            try {
                Cache::forget($cacheKey);
            } catch (Throwable $exception) {
                report($exception);
                $cacheFailed = true;
            }
        }

        $this->dispatch(
            'swal',
            icon: $cacheFailed ? 'warning' : 'success',
            title: 'Đã lưu ' . self::TABS[$group]['label'],
            text: $cacheFailed
            ? 'Dữ liệu đã lưu nhưng chưa cập nhật được cache. Vui lòng kiểm tra dịch vụ cache.'
            : 'Cấu hình đã được cập nhật.'
        );
    }

    private function hasSettingsChanges(string $group, array $data): bool
    {
        $rules = $this->groupSpecs()[$group]['rules'];

        $storedSettings = SystemSetting::whereIn('key', array_keys($data))
            ->get(['key', 'value', 'group'])
            ->keyBy('key');

        foreach ($data as $key => $value) {
            $setting = $storedSettings->get($key);

            // Chưa có cấu hình hoặc sai nhóm thì cần lưu.
            if (
                $setting === null
                || $setting->value === null
                || $setting->group !== $group
            ) {
                return true;
            }

            $stored = trim((string) $setting->value);

            // So sánh checkbox với các dạng boolean đang lưu trong DB.
            if (is_bool($value)) {
                $storedBoolean = filter_var(
                    $stored,
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );

                if ($storedBoolean === null || $storedBoolean !== $value) {
                    return true;
                }

                continue;
            }

            // Giá trị số: 25, 25.0 và 25.00 được coi là giống nhau.
            if (
                in_array('numeric', $rules[$key], true)
                || in_array('integer', $rules[$key], true)
            ) {
                if (
                    !is_numeric($stored)
                    || (float) $stored !== (float) $value
                ) {
                    return true;
                }

                continue;
            }

            // Các trường văn bản.
            if ($stored !== (string) $value) {
                return true;
            }
        }

        return false;
    }

    private function validatedSettings(string $group): array
    {
        $rules = $this->groupSpecs()[$group]['rules'];
        $values = [];

        foreach (array_keys($rules) as $key) {
            $values[$key] = is_bool($this->{$key})
                ? $this->{$key}
                : trim($this->{$key});
        }

        return Validator::make(
            $values,
            $rules,
            $this->messages(),
            $this->validationAttributes()
        )->validate();
    }

    private function settingsHash(string $group, array $data): string
    {
        return hash('sha256', serialize([$group, $data]));
    }

    private function groupSpecs(): array
    {
        return [
            'contact' => [
                'rules' => [
                    'farm_name' => [
                        'bail',
                        'required',
                        'string',
                        'max:255',
                    ],
                    'hotline' => [
                        'bail',
                        'required',
                        'string',
                        'max:50',

                        function (string $attribute, mixed $value, \Closure $fail): void {
                            $phone = preg_replace('/[\s().-]+/u', '', $value);

                            if (
                                preg_match('/^\+?[0-9]{6,15}$/D', $phone ?? '')
                                !== 1
                            ) {
                                $fail(
                                    'Hotline phải có 6–15 chữ số; có thể dùng dấu + ở đầu, khoảng trắng, dấu chấm, gạch ngang hoặc ngoặc.'
                                );
                            }
                        },
                    ],
                    'email' => [
                        'bail',
                        'required',
                        'string',
                        'email',
                        'max:255',
                    ],
                    'farm_address' => [
                        'bail',
                        'required',
                        'string',
                        'max:500',
                    ],
                    'farm_latitude' => [
                        'bail',
                        'required',
                        'numeric',
                        'between:-90,90',
                    ],
                    'farm_longitude' => [
                        'bail',
                        'required',
                        'numeric',
                        'between:-180,180',
                    ],
                ],
                'caches' => [
                    'system_contact_settings',
                    'system_farm_coordinates',
                ],
            ],

            'shipping' => [
                'rules' => [
                    'max_shipping_distance_km' => [
                        'bail',
                        'required',
                        'numeric',
                        'min:1',
                        'max:500',
                    ],
                ],
                'caches' => ['system_max_shipping_distance'],
            ],

            'loyalty' => [
                'rules' => [
                    'spending_per_point' => [
                        'bail',
                        'required',
                        'integer',
                        'min:1000',
                        'max:100000000',
                    ],
                    'value_per_point' => [
                        'bail',
                        'required',
                        'integer',
                        'min:1',
                        'max:10000000',
                    ],
                    'max_point_discount_percent' => [
                        'bail',
                        'required',
                        'numeric',
                        'min:1',
                        'max:100',
                    ],
                ],
                'caches' => ['system_loyalty_settings'],
            ],

            'subscription' => [
                'rules' => [
                    'vip_subscription_enabled' => [
                        'required',
                        'boolean',
                    ],
                    'vip_package_name' => [
                        'bail',
                        'required',
                        'string',
                        'max:255',
                    ],
                    'vip_package_price' => [
                        'bail',
                        'required',
                        'integer',
                        'min:1000',
                        'max:100000000',
                    ],
                    'vip_delivery_max_weight' => [
                        'bail',
                        'required',
                        'numeric',
                        'min:0.5',
                        'max:50',
                    ],
                ],
                'caches' => [],
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'Vui lòng nhập :attribute.',
            'string' => ':attribute phải là chuỗi ký tự.',
            'numeric' => ':attribute phải là số hợp lệ.',
            'integer' => ':attribute phải là số nguyên.',
            'boolean' => 'Trạng thái mở bán không hợp lệ.',
            'email' => 'Email liên hệ không đúng định dạng.',
            'between.numeric' => ':attribute phải từ :min đến :max.',
            'min.numeric' => ':attribute phải lớn hơn hoặc bằng :min.',
            'max.numeric' => ':attribute không được vượt quá :max.',
            'max.string' => ':attribute tối đa :max ký tự.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'farm_name' => 'tên thương hiệu nông trại',
            'hotline' => 'hotline',
            'email' => 'email liên hệ',
            'farm_address' => 'địa chỉ nông trại',
            'farm_latitude' => 'vĩ độ',
            'farm_longitude' => 'kinh độ',
            'max_shipping_distance_km' => 'bán kính giao hàng',
            'spending_per_point' => 'mức chi tiêu tích một điểm',
            'value_per_point' => 'giá trị quy đổi một điểm',
            'max_point_discount_percent' => 'tỷ lệ giảm tối đa bằng điểm',
            'vip_subscription_enabled' => 'trạng thái mở bán VIP',
            'vip_package_name' => 'tên gói VIP',
            'vip_package_price' => 'giá gói VIP',
            'vip_delivery_max_weight' => 'định lượng tối đa mỗi đợt giao',
        ];
    }

    public function render()
    {
        return view('livewire.admin.setting-manager', [
            'tabs' => self::TABS,
        ]);
    }
}