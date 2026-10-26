<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Address;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
#[Title('Tài Khoản Của Tôi | Nông Trại Đất Lành')]
class ProfilePage extends Component
{
    use WithFileUploads;

    #[Url(as: 'tab')]
    public string $activeTab = 'info';

    // 1. Hồ sơ cá nhân
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public $newAvatar = null;

    // 2. Sổ địa chỉ
    public bool $showAddressForm = false;
    public ?int $editingAddressId = null;
    public string $recipient_name = '';
    public string $recipient_phone = '';
    public string $address_detail = '';
    public string $province = '';
    public string $district = '';
    public string $ward = '';
    public $latitude = null;
    public $longitude = null;
    public bool $is_default = false;

    // 3. Đổi mật khẩu
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public string $bank_name = '';
    public string $bank_account_number = '';
    public string $bank_account_name = '';
    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            $this->dispatch('open-auth-modal', 'login');
            return ;
        }
        $this->name = $user->name ?? '';
        $this->phone = $user->phone ?? '';
        $this->email = $user->email ?? '';
        $this->bank_name           = $user->default_bank_name ?? '';
        $this->bank_account_number = $user->default_bank_account_number ?? '';
        $this->bank_account_name   = $user->default_bank_account_name ?? '';
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    // TỰ ĐỘNG LƯU AVATAR NGAY KHI CHỌN FILE
    public function updatedNewAvatar()
    {
        $this->validate([
            'newAvatar' => ['required', 'image', 'max:2048'],
        ], [
            'newAvatar.image' => 'Tệp tải lên phải là hình ảnh (jpg, png, jpeg...).',
            'newAvatar.max'   => 'Dung lượng ảnh không được vượt quá 2MB.',
        ]);

        $user = Auth::user();
        $avatarPath = $this->newAvatar->store('avatars', 'public');
        
        $user->update([
            'avatar' => $avatarPath,
        ]);

        $this->reset('newAvatar');

        $this->dispatch('profile-updated');
        $this->dispatch('show-toast', type: 'success', message: 'Đã đổi ảnh đại diện thành công!');
    }

    // Cập nhật Họ tên & Số điện thoại
    public function updateProfile()
    {
        $this->validate([
        'name'                  => ['required', 'string', 'max:255'],
        'phone'                 => ['required', 'string', 'regex:/^(0[35789])[0-9]{8}$/'],
        'bank_name'             => ['nullable', 'string'],
        'bank_account_number'   => ['nullable', 'string', 'max:30'],
        'bank_account_name'     => ['nullable', 'string', 'max:100'],
    ], [
        'name.required' => 'Vui lòng nhập họ và tên.',
        'name.string'   => 'Họ và tên không hợp lệ.',
        'name.max'      => 'Họ và tên không được vượt quá 255 ký tự.',
        'phone.required' => 'Vui lòng nhập số điện thoại.',
        'phone.string'   => 'Số điện thoại không hợp lệ.',
        'phone.regex'    => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 03, 05, 07, 08 hoặc 09.',
        'bank_name.string' => 'Tên ngân hàng không hợp lệ.',
        'bank_account_number.string' => 'Số tài khoản phải là chuỗi ký tự.',
        'bank_account_number.max'    => 'Số tài khoản không được vượt quá 30 ký tự.',
        'bank_account_name.string' => 'Tên chủ tài khoản không hợp lệ.',
        'bank_account_name.max'    => 'Tên chủ tài khoản không được vượt quá 100 ký tự.',
    ]);

        $user = Auth::user();
        $user->update([
            'name'  => $this->name,
            'phone' => $this->phone,
            'default_bank_name'           => $this->bank_name,
            'default_bank_account_number' => $this->bank_account_number,
            'default_bank_account_name'   => mb_strtoupper($this->bank_account_name),
        ]);

        $this->dispatch('profile-updated');
        $this->dispatch('show-toast', type: 'success', message: 'Cập nhật thông tin cá nhân thành công!');
    }

    // Đổi mật khẩu
    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'different:current_password'
            ],
            'new_password_confirmation' => ['required', 'same:new_password'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required'     => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min'          => 'Mật khẩu mới phải từ 8 ký tự trở lên.',
            'new_password.regex'        => 'Mật khẩu mới phải gồm chữ hoa, chữ thường và chữ số.',
            'new_password.different'    => 'Mật khẩu mới không được trùng với mật khẩu hiện tại.',
            'new_password_confirmation.required' => 'Vui lòng xác nhận mật khẩu mới.',
            'new_password_confirmation.same'     => 'Mật khẩu xác nhận không khớp.',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Mật khẩu hiện tại không chính xác.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password)
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->dispatch('show-toast', type: 'success', message: 'Đổi mật khẩu thành công!');
    }

    // Mở form thêm địa chỉ mới
    public function openNewAddressForm()
    {
        $this->resetAddressForm();
        $this->showAddressForm = true;
    }

    // Mở form chỉnh sửa địa chỉ
    public function editAddress(int $addressId)
    {
        $address = Address::where('user_id', Auth::id())->find($addressId);
        if (!$address) return;

        $this->editingAddressId = $address->id;
        $this->recipient_name  = $address->recipient_name;
        $this->recipient_phone = $address->recipient_phone;
        $this->province        = $address->province;
        $this->district        = $address->district;
        $this->ward            = $address->ward;
        $this->address_detail  = $address->address_detail;
        $this->latitude        = $address->latitude;
        $this->longitude       = $address->longitude;
        $this->is_default      = (bool) $address->is_default;

        $this->showAddressForm = true;

        $this->dispatch('load-address-dropdowns', [
            'province' => $this->province,
            'district' => $this->district,
            'ward'     => $this->ward,
        ]);
    }

    // Lắng nghe sự kiện băm tọa độ từ Alpine.js
    #[On('address-coordinates-updated')]
    public function updateCoordinates($lat = null, $lng = null)
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
    }

    // Lưu địa chỉ vào CSDL
    public function saveAddress()
    {
        $this->validate([
        'recipient_name'  => 'required|string|max:255',
        'recipient_phone' => [
            'required',
            'string',
            'regex:/^(0[35789])[0-9]{8}$/',
        ],
        'address_detail'  => 'required|string|max:255',
        'province'        => 'required|string|max:100',
        'district'        => 'required|string|max:100',
        'ward'            => 'required|string|max:100',
        'latitude'        => 'nullable|numeric',
        'longitude'       => 'nullable|numeric',
    ], [
        'recipient_name.required' => 'Vui lòng nhập tên người nhận.',
        'recipient_name.string'   => 'Tên người nhận phải là chuỗi ký tự.',
        'recipient_name.max'      => 'Tên người nhận không được vượt quá 255 ký tự.',
        'recipient_phone.required' => 'Vui lòng nhập số điện thoại người nhận.',
        'recipient_phone.string'   => 'Số điện thoại không hợp lệ.',
        'recipient_phone.regex'    => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 03, 05, 07, 08 hoặc 09.',
        'address_detail.required' => 'Vui lòng nhập địa chỉ chi tiết.',
        'address_detail.string'   => 'Địa chỉ chi tiết không hợp lệ.',
        'address_detail.max'      => 'Địa chỉ chi tiết không được vượt quá 255 ký tự.',
        'province.required' => 'Vui lòng chọn Tỉnh/Thành phố.',
        'province.string'   => 'Tỉnh/Thành phố không hợp lệ.',
        'province.max'      => 'Tên Tỉnh/Thành phố không được vượt quá 100 ký tự.',
        'district.required' => 'Vui lòng chọn Quận/Huyện.',
        'district.string'   => 'Quận/Huyện không hợp lệ.',
        'district.max'      => 'Tên Quận/Huyện không được vượt quá 100 ký tự.',
        'ward.required' => 'Vui lòng chọn Phường/Xã.',
        'ward.string'   => 'Phường/Xã không hợp lệ.',
        'ward.max'      => 'Tên Phường/Xã không được vượt quá 100 ký tự.',

        // Tọa độ
        'latitude.numeric'  => 'Vĩ độ phải là một giá trị số.',
        'longitude.numeric' => 'Kinh độ phải là một giá trị số.',
    ]);

        $user = Auth::user();

        if ($this->is_default) {
            Address::where('user_id', $user->id)->update(['is_default' => false]);
        }

        if ($this->editingAddressId) {
            $address = Address::where('user_id', $user->id)->find($this->editingAddressId);
            if ($address) {
                
                $address->update([
                    'recipient_name'  => $this->recipient_name,
                    'recipient_phone' => $this->recipient_phone,
                    'address_detail'  => $this->address_detail,
                    'province'        => $this->province,
                    'district'        => $this->district,
                    'ward'            => $this->ward,
                    'latitude'        => $this->latitude,
                    'longitude'       => $this->longitude,
                    'is_default'      => $this->is_default,
                ]);
                $this->dispatch('show-toast', type: 'success', message: 'Đã cập nhật địa chỉ thành công!');
            }
        } else {
            Address::create([
                'user_id'         => $user->id,
                'recipient_name'  => $this->recipient_name,
                'recipient_phone' => $this->recipient_phone,
                'address_detail'  => $this->address_detail,
                'province'        => $this->province,
                'district'        => $this->district,
                'ward'            => $this->ward,
                'latitude'        => $this->latitude,
                'longitude'       => $this->longitude,
                'is_default'      => $this->is_default || Address::where('user_id', $user->id)->count() === 0,
            ]);
            $this->dispatch('show-toast', type: 'success', message: 'Đã thêm địa chỉ giao hàng mới!');
        }

        $this->resetAddressForm();
    }

    public function resetAddressForm()
    {
        $this->reset([
            'recipient_name', 
            'recipient_phone', 
            'address_detail', 
            'province', 
            'district', 
            'ward', 
            'latitude', 
            'longitude', 
            'is_default', 
            'editingAddressId'
        ]);
        $this->showAddressForm = false;
    }

    public function setDefaultAddress(int $addressId)
    {
        $user = Auth::user();
        Address::where('user_id', $user->id)->update(['is_default' => false]);
        Address::where('user_id', $user->id)->where('id', $addressId)->update(['is_default' => true]);

        $this->dispatch('show-toast', type: 'success', message: 'Đã cập nhật địa chỉ mặc định.');
    }

    public function deleteAddress(int $addressId)
    {
        $address = Address::where('user_id', Auth::id())->find($addressId);
        if ($address) {
            $address->delete();
            $this->dispatch('show-toast', type: 'success', message: 'Đã xóa địa chỉ thành công.');
        }
    }
#[Computed]
    public function banks()
    {
    return Cache::remember('vietnam_banks_list', 86400, function () {
        try {
            $response = Http::timeout(3)->get('https://api.vietqr.io/v2/banks');
            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }
        } catch (\Exception $e) {
            \Log::warning('Không thể lấy danh sách ngân hàng VietQR: ' . $e->getMessage());
        }

        return [];
    });
    }
    public function render()
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->orderByDesc('is_default')->get();

        // 👈 Lấy toàn bộ cấu hình thuộc nhóm 'loyalty' từ bảng settings
        $loyaltySettings = SystemSetting::where('group', 'loyalty')
            ->pluck('value', 'key')
            ->toArray();

        return view('livewire.customer.profile-page', [
            'user'            => $user,
            'addresses'       => $addresses,
            'loyaltySettings' => $loyaltySettings, // 👈 Truyền sang Blade
        ])->layout('layouts.customer.app');
    }
}