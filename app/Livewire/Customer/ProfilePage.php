<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Address;

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
    public $newAvatar = null; // Biến bón file avatar

    // 2. Sổ địa chỉ
    public bool $showAddressForm = false;
    public ?int $editingAddressId = null;
    public string $recipient_name = '';
    public string $recipient_phone = '';
    public string $address_detail = '';
    public string $province = '';
    public string $district = '';
    public string $ward = '';
    public bool $is_default = false;

    // 3. Đổi mật khẩu
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $this->name = $user->name ?? '';
        $this->phone = $user->phone ?? '';
        $this->email = $user->email ?? '';
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    // TỰ ĐỘNG LƯU AVATAR NGAY KHI CHỌN FILE
    public function updatedNewAvatar()
    {
        $this->validate([
            'newAvatar' => ['required', 'image', 'max:2048'], // Tối đa 2MB
        ], [
            'newAvatar.image' => 'Tệp tải lên phải là hình ảnh (jpg, png, jpeg...).',
            'newAvatar.max'   => 'Dung lượng ảnh không được vượt quá 2MB.',
        ]);

        $user = Auth::user();

        // Lưu vào thư mục storage/app/public/avatars
        $avatarPath = $this->newAvatar->store('avatars', 'public');
        
        $user->update([
            'avatar' => $avatarPath,
        ]);

        $this->reset('newAvatar');

        // Bắn sự kiện làm mới Header & Thông báo
        $this->dispatch('profile-updated');
        $this->dispatch('show-toast', type: 'success', message: 'Đã đổi ảnh đại diện thành công!');
    }

    // Cập nhật Họ tên & Số điện thoại
    public function updateProfile()
    {
        $this->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^(0[35789])[0-9]{8}$/'],
        ], [
            'name.required'  => 'Vui lòng nhập họ và tên.',
            'name.max'       => 'Họ và tên không được vượt quá 255 ký tự.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex'    => 'Số điện thoại không đúng định dạng (VD: 0912345678).',
        ]);

        $user = Auth::user();
        $user->update([
            'name'  => $this->name,
            'phone' => $this->phone,
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
        $this->is_default      = (bool) $address->is_default;

        $this->showAddressForm = true;

        $this->dispatch('load-address-dropdowns', [
            'province' => $this->province,
            'district' => $this->district,
            'ward'     => $this->ward,
        ]);
    }

    // Lưu địa chỉ
    public function saveAddress()
    {
        $this->validate([
            'recipient_name'  => 'required|string|max:255',
            'recipient_phone' => ['required', 'string', 'regex:/^(0[35789])[0-9]{8}$/'],
            'address_detail'  => 'required|string|max:255',
            'province'        => 'required|string|max:100',
            'district'        => 'required|string|max:100',
            'ward'            => 'required|string|max:100',
        ], [
            'recipient_name.required'  => 'Vui lòng nhập tên người nhận.',
            'recipient_phone.required' => 'Vui lòng nhập số điện thoại.',
            'recipient_phone.regex'    => 'Số điện thoại không hợp lệ.',
            'address_detail.required'  => 'Vui lòng nhập địa chỉ chi tiết.',
            'province.required'        => 'Vui lòng chọn Tỉnh/Thành.',
            'district.required'        => 'Vui lòng chọn Quận/Huyện.',
            'ward.required'            => 'Vui lòng chọn Phường/Xã.',
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
                'is_default'      => $this->is_default || Address::where('user_id', $user->id)->count() === 0,
            ]);
            $this->dispatch('show-toast', type: 'success', message: 'Đã thêm địa chỉ giao hàng mới!');
        }

        $this->resetAddressForm();
    }

    public function resetAddressForm()
    {
        $this->reset(['recipient_name', 'recipient_phone', 'address_detail', 'province', 'district', 'ward', 'is_default', 'editingAddressId']);
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

    public function render()
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->orderByDesc('is_default')->get();

        return view('livewire.customer.profile-page', [
            'user'      => $user,
            'addresses' => $addresses,
        ])->layout('layouts.customer.app');
    }
}