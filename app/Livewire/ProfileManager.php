<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;
use Throwable;

class ProfileManager extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $phone = '';
    public $newAvatar = null;
    public int $avatarInputKey = 0;

    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = $this->staff();

        $this->name = $user->name;
        $this->phone = $user->phone ?? '';
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'phone' => [
                'required',
                'string',
                'regex:/^(0[35789])[0-9]{8}$/',
            ],

            'newAvatar' => [
                'bail',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'current_password' => [
                'required',
                'string',
            ],

            'new_password' => [
                'required',
                'string',
                'min:8',
                'max:72',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'confirmed',
                'different:current_password',
            ],

            'new_password_confirmation' => [
                'required',
                'string',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.string' => 'Họ và tên không hợp lệ.',
            'name.max' => 'Họ và tên tối đa 255 ký tự.',

            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.string' => 'Số điện thoại không hợp lệ.',
            'phone.regex' => 'Số điện thoại gồm 10 số, bắt đầu bằng 03, 05, 07, 08 hoặc 09.',

            'newAvatar.image' => 'Tệp được chọn không phải định dạng ảnh hợp lệ.',
            'newAvatar.mimes' => 'Chỉ chấp nhận ảnh JPG, JPEG, PNG hoặc WEBP.',
            'newAvatar.max' => 'Ảnh đại diện không được vượt quá 2 MB.',

            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.string' => 'Mật khẩu hiện tại không hợp lệ.',

            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.string' => 'Mật khẩu mới không hợp lệ.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.max' => 'Mật khẩu mới tối đa 72 ký tự.',
            'new_password.regex' => 'Mật khẩu mới cần có chữ hoa, chữ thường và chữ số.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'new_password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',

            'new_password_confirmation.required' => 'Vui lòng xác nhận mật khẩu mới.',
            'new_password_confirmation.string' => 'Xác nhận mật khẩu không hợp lệ.',
        ];
    }

    public function updatedNewAvatar(): void
    {
        $this->staff();

        try {
            $this->validateOnly('newAvatar');
        } catch (ValidationException $exception) {
            // Không preview file không hợp lệ.
            $this->reset('newAvatar');
            $this->avatarInputKey++;

            throw $exception;
        }
    }

    public function cancelAvatar(): void
    {
        $this->staff();

        $this->reset('newAvatar');
        $this->avatarInputKey++;
        $this->resetValidation('newAvatar');
    }

    private function discardDuplicateAvatar(User $user): void
    {
        if ($this->newAvatar === null) {
            return;
        }

        // Chỉ đọc nội dung sau khi ảnh vượt qua validation.
        $this->validateOnly('newAvatar');

        $path = trim((string) $user->avatar);

        // Chỉ so sánh với ảnh đang lưu trên disk public.
        if (
            $path === ''
            || Str::startsWith($path, ['https://', 'http://'])
        ) {
            return;
        }

        try {
            $disk = Storage::disk($this->getAvatarDisk());

            // Ảnh cũ bị thiếu: cho phép lưu ảnh thay thế.
            if (!$disk->exists($path)) {
                return;
            }

            $oldContents = $disk->get($path);
            $newContents = $this->newAvatar->get();

            if (
                !is_string($oldContents)
                || !is_string($newContents)
            ) {
                throw new RuntimeException('Không đọc được nội dung ảnh.');
            }

            $identical = hash_equals(
                hash('sha256', $oldContents),
                hash('sha256', $newContents)
            );
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'newAvatar' => 'Không thể kiểm tra ảnh đại diện. Vui lòng chọn lại ảnh.',
            ]);
        }

        if ($identical) {
            // Loại ảnh trùng khỏi dữ liệu chuẩn bị lưu.
            $this->reset('newAvatar');
            $this->avatarInputKey++;
            $this->resetValidation('newAvatar');
        }
    }

    public function cancelProfileChanges(): void
    {
        $user = $this->staff();

        Auth::setUser($user);

        $this->name = $user->name;
        $this->phone = $user->phone ?? '';

        // Bỏ ảnh mới và dữ liệu mật khẩu đang nhập.
        $this->reset([
            'newAvatar',
            'current_password',
            'new_password',
            'new_password_confirmation',
        ]);

        $this->avatarInputKey++;
        $this->resetValidation();

        // Đồng bộ lại thông tin sidebar.
        $this->dispatch('profile-updated');
    }

    public function requestSaveProfile(): void
    {
        $this->staff();
        $this->dispatch('confirm-profile-save');
    }

    public function requestSavePassword(): void
    {
        $this->staff();
        $this->dispatch('confirm-password-save');
    }

    public function requestSaveAvatar(): void
    {
        $this->staff();
        $this->dispatch('confirm-avatar-save');
    }

    public function saveProfile(): void
    {
        $user = $this->staff();

        $this->name = trim($this->name);
        $this->phone = trim($this->phone);

        $validated = $this->validate(
            Arr::only($this->rules(), ['name', 'phone'])
        );

        if (
            $this->name === (string) $user->name
            && $this->phone === (string) $user->phone
        ) {
            $this->notify(
                'info',
                'Không có thay đổi nào',
                'Thông tin tài khoản chưa có thay đổi.'
            );
            return;
        }

        try {
            $user->forceFill([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
            ]);

            if (!$user->saveOrFail()) {
                throw new RuntimeException('Không lưu được thông tin tài khoản.');
            }
        } catch (Throwable $exception) {
            report($exception);

            $this->notify(
                'error',
                'Lưu thông tin thất bại',
                'Không thể lưu thông tin tài khoản. Vui lòng thử lại.'
            );
            return;
        }

        Auth::setUser($user);

        $this->dispatch('profile-updated');

        $this->notify(
            'success',
            'Đã lưu thông tin!',
            'Thông tin tài khoản của bạn đã được cập nhật.'
        );
    }

    public function savePassword(): void
    {
        $user = $this->staff();

        $validated = $this->validate(
            Arr::only($this->rules(), [
                'current_password',
                'new_password',
                'new_password_confirmation',
            ])
        );

        if (!Hash::check($validated['current_password'], $user->password)) {
            $this->addError(
                'current_password',
                'Mật khẩu hiện tại không chính xác.'
            );
            return;
        }

        try {
            $user->forceFill([
                'password' => $validated['new_password'],
                'remember_token' => Str::random(60),
            ]);

            if (!$user->saveOrFail()) {
                throw new RuntimeException('Không cập nhật được mật khẩu.');
            }
        } catch (Throwable $exception) {
            report($exception);

            $this->notify(
                'error',
                'Cập nhật mật khẩu thất bại',
                'Không thể cập nhật mật khẩu. Vui lòng thử lại.'
            );
            return;
        }

        Auth::setUser($user);

        $this->reset([
            'current_password',
            'new_password',
            'new_password_confirmation',
        ]);

        $this->notify(
            'success',
            'Đã cập nhật mật khẩu!',
            'Mật khẩu của bạn đã được thay đổi.'
        );
    }

    public function saveAvatar(): void
    {
        $user = $this->staff();

        $this->discardDuplicateAvatar($user);

        if ($this->newAvatar === null) {
            $this->notify(
                'info',
                'Không có ảnh mới cần lưu',
                'Bạn chưa chọn ảnh mới hoặc ảnh đã chọn trùng với ảnh đang lưu.'
            );
            return;
        }

        $oldAvatar = $user->avatar;
        $newPath = null;
        $roleFolder = $user->role === 'sales' ? 'sales' : 'farm-staff';
        $diskName = $this->getAvatarDisk();

        try {
            $storedPath = $this->newAvatar->store(
                "avatars/{$roleFolder}/{$user->id}",
                $diskName
            );

            if (!is_string($storedPath) || $storedPath === '') {
                throw new RuntimeException('Không lưu được ảnh đại diện.');
            }

            $newPath = $storedPath;

            $user->forceFill([
                'avatar' => $newPath,
            ]);

            if (!$user->saveOrFail()) {
                throw new RuntimeException('Không cập nhật được ảnh đại diện.');
            }
        } catch (Throwable $exception) {
            // Nếu DB lưu thất bại, dọn file mới vừa tạo.
            $this->deleteManagedAvatar($newPath, $user->id);

            report($exception);

            $this->notify(
                'error',
                'Lưu ảnh thất bại',
                'Không thể lưu ảnh đại diện. Vui lòng thử lại.'
            );
            return;
        }

        // Chỉ xóa ảnh cũ sau khi DB đã cập nhật thành công.
        $this->deleteManagedAvatar($oldAvatar, $user->id);

        Auth::setUser($user);

        $this->reset('newAvatar');
        $this->avatarInputKey++;
        $this->resetValidation('newAvatar');

        $this->dispatch('profile-updated');

        $this->notify(
            'success',
            'Đã lưu ảnh!',
            'Ảnh đại diện của bạn đã được cập nhật.'
        );
    }

    private function notify(string $icon, string $title, string $text): void
    {
        $this->dispatch(
            'swal',
            icon: $icon,
            title: $title,
            text: $text
        );
    }

    protected function staff(): User
    {
        $id = Auth::id();
        abort_unless($id, 401);

        $user = User::find($id);

        abort_unless(
            $user
            && in_array($user->role, ['farm_staff', 'sales'])
            && $user->status === 'active',
            403,
            'Bạn không có quyền truy cập chức năng này.'
        );

        return $user;
    }

    private function getAvatarDisk(): string
    {
        return config('filesystems.disks.r2.key') ? 'r2' : 'public';
    }

    private function deleteManagedAvatar(?string $path, int $userId): void
    {
        if (
            !$path
            || (!str_starts_with($path, "avatars/farm-staff/{$userId}/")
                && !str_starts_with($path, "avatars/sales/{$userId}/")
                && !str_starts_with($path, 'avatars/'))
            || str_contains($path, '..')
            || str_contains($path, '\\')
        ) {
            return;
        }

        try {
            $disk = $this->getAvatarDisk();
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function render()
    {
        $user = $this->staff();
        $isSales = $user->role === 'sales';

        $layout = $isSales ? 'layouts.sales.app' : 'layouts.farm-staff.app';
        $roleTitle = $isSales ? 'Nhân Viên Bán Hàng' : 'Nhân Viên Nông Trại';

        return view('livewire.profile-manager', [
            'user' => $user,
            'roleTitle' => $roleTitle,
        ])->layout($layout)->title("Hồ Sơ {$roleTitle}");
    }
}