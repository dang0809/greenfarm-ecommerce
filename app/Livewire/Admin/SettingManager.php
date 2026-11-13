<?php

namespace App\Livewire\Admin;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Layout('layouts.admin.app')]
#[Title('Cấu Hình Hệ Thống | GreenFarm Admin')]
class SettingManager extends Component
{
    // Giá trị mặc định khi database chưa có cấu hình.
    public string $farm_name = SystemSetting::DEFAULT_FARM_NAME;
    public string $hotline = '0901 234 567';
    public string $email = 'contact@datlanhfarm.vn';
    public string $farm_address = 'Thung lũng Đạ Ròn, Huyện Đơn Dương, Tỉnh Lâm Đồng';

    // Ghi nhận dữ liệu đã validate để chờ xác nhận.
    // Locked ngăn phía trình duyệt tự sửa thuộc tính này.
    #[Locked]
    public ?string $confirmationHash = null;

    // Kiểm tra quyền ở cả lần mở trang và các request Livewire.
    public function boot(): void
    {
        $user = Auth::user();

        abort_unless(
            $user && $user->role === 'admin' && $user->status === 'active',
            403
        );
    }

    // Chỉ nạp database khi khởi tạo component.
    public function mount(): void
    {
        $settings = SystemSetting::whereIn('key', [
            'farm_name',
            'hotline',
            'email',
            'farm_address',
        ])->pluck('value', 'key');

        foreach ($settings as $key => $value) {
            if ($value !== null) {
                $this->{$key} = (string) $value;
            }
        }
    }

    // Bước 1: validate rồi mới yêu cầu mở SweetAlert.
    public function requestSave(): void
    {
        $this->confirmationHash = null;
        $this->resetValidation();

        // Nếu không hợp lệ, Livewire trả lỗi và dừng tại đây.
        $data = $this->validatedSettings();

        $this->confirmationHash = hash('sha256', serialize($data));

        // Chưa ghi database tại bước này.
        $this->dispatch('confirm-setting-save')->self();
    }

    // Hủy chỉ xóa trạng thái xác nhận, không reset form.
    public function cancelSave(): void
    {
        $this->confirmationHash = null;
    }

    // Bước 2: được gọi khi chọn Xác nhận.
    public function saveConfirmed(): void
    {
        $expectedHash = $this->confirmationHash;
        $this->confirmationHash = null;

        if ($expectedHash === null) {
            $this->addError(
                'save',
                'Vui lòng nhấn Lưu thông tin và xác nhận lại.'
            );

            return;
        }

        // Luôn validate lại trước khi ghi database.
        $data = $this->validatedSettings();

        // Đảm bảo lưu đúng dữ liệu đã đưa vào bước xác nhận.
        if (!hash_equals($expectedHash, hash('sha256', serialize($data)))) {
            $this->addError(
                'save',
                'Dữ liệu đã thay đổi. Vui lòng nhấn Lưu thông tin để xác nhận lại.'
            );

            return;
        }

        try {
            // Bốn giá trị cùng thành công hoặc cùng rollback.
            DB::transaction(function () use ($data): void {
                foreach ($data as $key => $value) {
                    SystemSetting::updateOrCreate(
                        ['key' => $key],
                        ['value' => $value, 'group' => 'contact']
                    );
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            $this->dispatch(
                'swal',
                icon: 'error',
                title: 'Lưu thất bại',
                text: 'Chưa thể lưu thông tin. Vui lòng thử lại.'
            );

            return;
        }

        // Đồng bộ form với dữ liệu vừa lưu.
        $this->fill($data);
        $this->resetValidation();

        // contactSettings() đang cache bằng rememberForever.
        // Bắt lỗi riêng vì database đã lưu thành công ở phía trên.
        try {
            Cache::forget('system_contact_settings');
        } catch (Throwable $exception) {
            report($exception);

            $this->dispatch(
                'swal',
                icon: 'warning',
                title: 'Đã lưu thông tin',
                text: 'Dữ liệu đã lưu nhưng chưa thể cập nhật phần hiển thị. Vui lòng kiểm tra dịch vụ cache.'
            );

            return;
        }

        // Layout admin đã có listener cho event swal.
        $this->dispatch(
            'swal',
            icon: 'success',
            title: 'Lưu thành công',
            text: 'Đã cập nhật thông tin nông trại.'
        );
    }

    // Validate dữ liệu
    protected function rules(): array
    {
        return [
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
                    // Chỉ chuẩn hóa để kiểm tra, không sửa ô nhập.
                    $phone = preg_replace('/[\s().-]+/u', '', $value);

                    if (preg_match('/^\+?[0-9]{6,15}$/D', $phone ?? '') !== 1) {
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
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'Vui lòng nhập :attribute.',
            'string' => ':attribute phải là chuỗi ký tự.',
            'email' => 'Email liên hệ không đúng định dạng.',
            'max' => ':attribute không được vượt quá :max ký tự.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'farm_name' => 'tên thương hiệu nông trại',
            'hotline' => 'hotline',
            'email' => 'email liên hệ',
            'farm_address' => 'địa chỉ nông trại',
        ];
    }

    private function validatedSettings(): array
    {
        // Chỉ trim dữ liệu trả về, giữ nguyên dữ liệu trên form
        // trong lúc người dùng đang cân nhắc Xác nhận / Hủy.
        return array_map(
            static fn(string $value): string => trim($value),
            $this->validate()
        );
    }

    public function render()
    {
        return view('livewire.admin.setting-manager');
    }
}