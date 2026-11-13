<?php
namespace App\Livewire\Admin;
use App\Models\User;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

use RuntimeException;
use Throwable;

#[Layout('layouts.admin.app')]
#[Title('Quản Lý Tài Khoản | GreenFarm Admin')]
class AccountManager extends Component
{
    use WithPagination, WithFileUploads;

    private const ROLES = [
        'admin',
        'farm_owner',
        'farm_staff',
        'sales',
        'customer',
    ];

    // Bộ lọc danh sách.
    public string $tab = 'all';
    public string $search = '';
    public string $roleFilter = '';
    public string $statusFilter = '';

    #[Locked]
    public bool $showUserModal = false;

    #[Locked]
    public bool $showPasswordModal = false;

    #[Locked]
    public ?int $resetUserId = null;

    public string $resetUserName = '';
    public string $resetUserEmail = '';
    public string $resetPassword = '';

    #[Locked]
    public ?string $resetPasswordConfirmationHash = null;

    #[Locked]
    public ?int $editingId = null;

    #[Locked]
    public ?string $confirmationHash = null;

    // Dữ liệu form.
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $role = 'farm_staff';
    public string $rewardPoints = '0';

    public $newAvatar = null;

    #[Locked]
    public ?string $currentAvatarUrl = null;

    public int $avatarInputKey = 0;

    #[Locked]
    public array $pendingStatus = [];

    #[Locked]
    public array $pendingPoints = [];

    public function boot(): void
    {
        $admin = Auth::id()
            ? User::find(Auth::id())
            : null;

        abort_unless(
            $admin
            && $admin->role === 'admin'
            && $admin->status === 'active',
            403
        );
    }

    public function updated(string $property): void
    {
        if (
            in_array(
                $property,
                ['search', 'roleFilter', 'statusFilter'],
                true
            )
        ) {
            $this->resetPage();
        }

        // Form thay đổi thì phải xác nhận lại.
        $this->confirmationHash = null;

        $this->resetPasswordConfirmationHash = null;
    }

    public function updatedResetPassword(): void
    {
        // Nếu sửa mật khẩu sau khi đã mở SweetAlert thì phải xác nhận lại.
        $this->resetPasswordConfirmationHash = null;
    }

    public function updatedNewAvatar(): void
    {
        $this->confirmationHash = null;

        try {
            $this->validateOnly(
                'newAvatar',
                ['newAvatar' => $this->avatarRules()],
                $this->avatarMessages()
            );
        } catch (ValidationException $exception) {
            // Không tạo preview cho tệp không hợp lệ.
            $this->reset('newAvatar');
            $this->avatarInputKey++;

            throw $exception;
        }
    }

    // Bộ lọc danh sách
    public function setTab(string $tab): void
    {
        if (
            !in_array(
                $tab,
                ['all', 'internal', 'customer', 'blocked'],
                true
            )
        ) {
            return;
        }

        $this->tab = $tab;

        // Tránh bộ lọc của tab trước làm tab mới không có kết quả.
        $this->reset(['roleFilter', 'statusFilter']);
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'tab',
            'search',
            'roleFilter',
            'statusFilter',
        ]);

        $this->resetPage();
    }

    // Tạo và sửa tài khoản, avatar
    public function openCreate(): void
    {
        $this->closeResetPassword();
        $this->resetForm();

        $this->showUserModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->closeResetPassword();
        $this->resetForm();

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = (string) $user->phone;
        $this->role = $user->role;
        $this->currentAvatarUrl = $user->avatar_url;

        // Không đọc mật khẩu từ database vào form.
        $this->showUserModal = true;
    }

    public function closeUserModal(): void
    {
        $this->resetForm();
    }

    public function generateRandomPassword(): void
    {
        if (!$this->showUserModal || $this->editingId !== null) {
            return;
        }

        $this->password = Str::password(10) . '@Farm';
        $this->confirmationHash = null;
        $this->resetValidation('password');
    }

    public function cancelAvatar(): void
    {
        // Chỉ bỏ ảnh đang chọn; ảnh hiện tại trong DB vẫn được giữ.
        $this->reset('newAvatar');

        $this->avatarInputKey++;
        $this->confirmationHash = null;

        $this->resetValidation('newAvatar');
    }

    public function requestSaveAccount(): void
    {
        if (!$this->showUserModal) {
            return;
        }

        $this->confirmationHash = null;

        // Validate trước khi mở SweetAlert.
        $data = $this->validatedData();

        if (
            $this->editingId !== null
            && !$this->hasChanges(
                User::findOrFail($this->editingId),
                $data
            )
        ) {
            $this->notify('info', 'Không có sự thay đổi nào.');

            return;
        }

        $this->confirmationHash = $this->signature($data);

        // Chưa lưu database tại bước này.
        $this->dispatch(
            'confirm-account-save',
            editing: $this->editingId !== null
        )->self();
    }

    public function cancelSaveAccount(): void
    {
        // Chỉ hủy xác nhận, không đóng hoặc reset form.
        $this->confirmationHash = null;
    }

    public function saveAccount(): void
    {
        if (!$this->showUserModal) {
            return;
        }

        $data = $this->validatedData();

        if (
            $this->confirmationHash === null
            || !hash_equals(
                $this->confirmationHash,
                $this->signature($data)
            )
        ) {
            $this->confirmationHash = null;

            $this->notify(
                'info',
                'Dữ liệu đã thay đổi. Vui lòng nhấn Lưu tài khoản lại.'
            );

            return;
        }

        $this->confirmationHash = null;

        $editing = $this->editingId !== null;
        $newPath = null;
        $oldPath = null;

        try {
            $saved = DB::transaction(function () use ($data, $editing, &$newPath, &$oldPath) {
                $user = $editing
                    ? User::whereKey($this->editingId)
                        ->lockForUpdate()
                        ->firstOrFail()
                    : new User();

                if ($editing && !$this->hasChanges($user, $data)) {
                    return false;
                }

                $replaceAvatar = $this->newAvatar !== null
                    && (!$editing || $this->avatarHasChanges($user));

                $user->fill(Arr::except($data, ['password']));

                if (!$editing) {
                    $user->password = $data['password'];
                    $user->status = 'active';
                    $user->email_verified_at = now();
                } elseif ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }

                // Khi tạo mới, cần lưu trước để có user ID.
                $user->saveOrFail();

                if ($replaceAvatar) {
                    $oldPath = $user->avatar;

                    $folder = "avatars/accounts/{$user->id}";
                    $filename = Str::uuid()
                        . '.'
                        . $this->newAvatar->extension();

                    $newPath = "{$folder}/{$filename}";

                    $stored = $this->newAvatar->storeAs(
                        $folder,
                        $filename,
                        'r2'
                    );

                    if ($stored !== $newPath) {
                        throw new RuntimeException(
                            'Không tải được ảnh lên R2.'
                        );
                    }

                    // DB lưu object path, không lưu temporaryUrl().
                    $user->avatar = $newPath;
                    $user->saveOrFail();
                }

                return true;
            });
        } catch (ValidationException $exception) {
            $this->deleteManagedAvatar($newPath);

            throw $exception;
        } catch (UniqueConstraintViolationException $exception) {
            $this->deleteManagedAvatar($newPath);

            $this->addError(
                'email',
                'Email vừa được sử dụng. Vui lòng chọn email khác.'
            );

            return;
        } catch (Throwable $exception) {
            // DB rollback thì dọn ảnh mới, giữ ảnh cũ.
            $this->deleteManagedAvatar($newPath);

            report($exception);

            $this->addError(
                'form',
                'Chưa lưu được tài khoản hoặc ảnh đại diện. Vui lòng thử lại.'
            );

            return;
        }

        if (!$saved) {
            $this->notify('info', 'Không có sự thay đổi nào.');

            return;
        }

        // Chỉ dọn ảnh cũ sau khi transaction thành công.
        $this->deleteManagedAvatar($oldPath);

        $this->closeUserModal();
        $this->resetPage();

        $this->notify(
            'success',
            $editing
            ? 'Cập nhật tài khoản thành công.'
            : 'Cấp tài khoản thành công.'
        );
    }

    // Đặt lại mật khẩu nhanh
    public function openResetPassword(int $id): void
    {
        $user = User::find($id);

        if (!$user) {
            $this->notify('error', 'Tài khoản không còn tồn tại.');

            return;
        }

        $this->closeUserModal();
        $this->resetPasswordForm();

        $this->resetUserId = $user->id;
        $this->resetUserName = $user->name;
        $this->resetUserEmail = $user->email;
        $this->showPasswordModal = true;
    }

    public function closeResetPassword(): void
    {
        $this->resetPasswordForm();
    }

    public function setPresetPassword(): void
    {
        if (!$this->showPasswordModal) {
            return;
        }

        $this->resetPassword = '123456@GreenFarm';
        $this->resetPasswordConfirmationHash = null;
        $this->resetValidation('resetPassword');
    }

    public function requestResetPassword(): void
    {
        if (
            !$this->showPasswordModal
            || !$this->resetUserId
        ) {
            return;
        }

        $this->resetPasswordConfirmationHash = null;

        // Validate trước khi hiển thị SweetAlert.
        $password = $this->validatedResetPassword();

        $user = User::find($this->resetUserId);

        if (!$user) {
            $this->notify('error', 'Tài khoản không còn tồn tại.');

            return;
        }

        if (Hash::check($password, (string) $user->password)) {
            $this->notify(
                'info',
                'Mật khẩu mới giống mật khẩu hiện tại.'
            );

            return;
        }

        $this->resetPasswordConfirmationHash =
            $this->resetPasswordSignature($password);

        $this->dispatch(
            'confirm-password-reset',
            name: $user->name
        )->self();
    }

    public function cancelResetPassword(): void
    {
        // Hủy SweetAlert nhưng giữ nguyên modal và mật khẩu đã nhập.
        $this->resetPasswordConfirmationHash = null;
    }

    public function saveResetPassword(): void
    {
        if (
            !$this->showPasswordModal
            || !$this->resetUserId
        ) {
            return;
        }

        // Validate lại trước khi lưu.
        $password = $this->validatedResetPassword();

        if (
            $this->resetPasswordConfirmationHash === null
            || !hash_equals(
                $this->resetPasswordConfirmationHash,
                $this->resetPasswordSignature($password)
            )
        ) {
            $this->resetPasswordConfirmationHash = null;

            $this->notify(
                'info',
                'Dữ liệu đã thay đổi. Vui lòng nhấn xác nhận lại.'
            );

            return;
        }

        $this->resetPasswordConfirmationHash = null;

        try {
            $saved = DB::transaction(function () use ($password) {
                $user = User::whereKey($this->resetUserId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (Hash::check($password, (string) $user->password)) {
                    return false;
                }

                // User model đã cast password => hashed.
                $user->password = $password;
                $user->saveOrFail();

                return true;
            });
        } catch (Throwable $exception) {
            report($exception);

            $this->addError(
                'resetPassword',
                'Không thể đặt lại mật khẩu. Vui lòng thử lại.'
            );

            return;
        }

        if (!$saved) {
            $this->notify(
                'info',
                'Mật khẩu mới giống mật khẩu hiện tại.'
            );

            return;
        }

        $this->closeResetPassword();

        $this->notify(
            'success',
            'Đặt lại mật khẩu thành công.'
        );
    }

    // Khóa/ mở khóa tài khoản
    public function requestToggleStatus(int $id): void
    {
        $this->pendingStatus = [];

        $user = User::findOrFail($id);

        if ($id === (int) Auth::id()) {
            $this->notify(
                'info',
                'Bạn không thể tự khóa tài khoản đang đăng nhập.'
            );

            return;
        }

        if (!in_array($user->status, ['active', 'blocked'], true)) {
            $this->notify(
                'info',
                'Tài khoản đang chờ xác thực email.'
            );

            return;
        }

        $this->pendingStatus = [
            'id' => $user->id,
            'from' => $user->status,
            'to' => $user->status === 'active'
                ? 'blocked'
                : 'active',
        ];

        $this->dispatch(
            'confirm-account-status',
            name: $user->name,
            blocking: $this->pendingStatus['to'] === 'blocked'
        )->self();
    }

    public function cancelToggleStatus(): void
    {
        $this->pendingStatus = [];
    }

    public function saveToggleStatus(): void
    {
        $pending = $this->pendingStatus;
        $this->pendingStatus = [];

        if (!$pending || $pending['id'] === (int) Auth::id()) {
            return;
        }

        try {
            $saved = DB::transaction(function () use ($pending) {
                $user = User::whereKey($pending['id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($user->status !== $pending['from']) {
                    return false;
                }

                $user->status = $pending['to'];
                $user->saveOrFail();

                return true;
            });
        } catch (Throwable $exception) {
            report($exception);

            $this->notify(
                'error',
                'Chưa cập nhật được trạng thái tài khoản.'
            );

            return;
        }

        if ($saved) {
            $this->dispatch(
                'account-status-updated',
                id: (int) $pending['id'],
                status: $pending['to']
            );
        }

        $this->notify(
            $saved ? 'success' : 'info',
            $saved
            ? 'Đã cập nhật trạng thái tài khoản.'
            : 'Trạng thái đã thay đổi. Vui lòng thao tác lại.'
        );
    }

    // Chỉnh điểm tích lũy
    public function requestQuickPoints(int $id): void
    {
        $this->pendingPoints = [];

        $user = User::findOrFail($id);

        if ($user->role !== 'customer') {
            return;
        }

        $this->pendingPoints = [
            'id' => $user->id,
            'points' => (int) $user->reward_points,
        ];

        $this->dispatch(
            'edit-account-points',
            name: $user->name,
            points: (int) $user->reward_points
        )->self();
    }

    public function cancelQuickPoints(): void
    {
        $this->pendingPoints = [];
    }

    public function saveQuickPoints(string $points, string $reason): array
    {
        $validator = Validator::make(
            [
                'points' => $points,
                'reason' => trim($reason),
            ],
            [
                'points' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:2147483647',
                ],
                'reason' => ['nullable', 'string', 'max:500'],
            ],
            [
                'points.required' => 'Vui lòng nhập số điểm.',
                'points.integer' => 'Điểm phải là số nguyên.',
                'points.min' => 'Điểm không được âm.',
                'points.max' => 'Điểm tối đa 2147483647.',
                'reason.max' => 'Lý do tối đa 500 ký tự.',
            ]
        );

        if ($validator->fails()) {
            return [
                'ok' => false,
                'message' => $validator->errors()->first(),
            ];
        }

        $pending = $this->pendingPoints;

        if (!$pending) {
            return [
                'ok' => false,
                'message' => 'Vui lòng mở lại hộp chỉnh điểm.',
            ];
        }

        $data = $validator->validated();

        try {
            $result = DB::transaction(function () use ($pending, $data) {
                $user = User::whereKey($pending['id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $user->role !== 'customer'
                    || (int) $user->reward_points !== $pending['points']
                ) {
                    return [
                        'ok' => false,
                        'message' => 'Dữ liệu đã thay đổi. Vui lòng mở lại hộp chỉnh điểm.',
                    ];
                }

                $points = (int) $data['points'];

                if ($points === (int) $user->reward_points) {
                    return [
                        'ok' => true,
                        'changed' => false,
                        'message' => 'Không có sự thay đổi nào.',
                    ];
                }

                $user->reward_points = $points;
                $user->saveOrFail();

                return [
                    'ok' => true,
                    'changed' => true,
                    'message' => 'Đã cập nhật điểm tích lũy.',
                ];
            });
        } catch (Throwable $exception) {
            report($exception);

            return [
                'ok' => false,
                'message' => 'Chưa cập nhật được điểm. Vui lòng thử lại.',
            ];
        }

        if ($result['ok']) {
            $this->pendingPoints = [];

            if ($result['changed']) {
                try {
                    Log::info('Admin adjusted reward points', [
                        'admin_id' => Auth::id(),
                        'user_id' => $pending['id'],
                        'old_points' => $pending['points'],
                        'new_points' => (int) $data['points'],
                        'reason' => $data['reason'] ?? '',
                    ]);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        }

        return $result;
    }

    // Form tài khoản
    private function resetForm(): void
    {
        $this->reset([
            'showUserModal',
            'editingId',
            'confirmationHash',
            'name',
            'email',
            'phone',
            'password',
            'role',
            'rewardPoints',
            'newAvatar',
            'currentAvatarUrl',
        ]);

        // Đổi key để trình duyệt xóa lựa chọn trong input file.
        $this->avatarInputKey++;

        $this->resetValidation();
    }

    private function validatedData(): array
    {
        $this->name = trim($this->name);
        $this->email = strtolower(trim($this->email));

        // Chấp nhận nhập số điện thoại có khoảng trắng, dấu chấm/gạch.
        $this->phone = preg_replace(
            '/[\s.\-]+/u',
            '',
            trim($this->phone)
        ) ?? '';

        if ($this->editingId === null) {
            $this->rewardPoints = $this->role === 'customer'
                ? trim($this->rewardPoints)
                : '0';
        }

        $data = $this->validate([
            'newAvatar' => $this->avatarRules(),
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($this->editingId),
            ],

            'phone' => [
                'required',
                'string',
                'regex:/^0[35789][0-9]{8}$/',
            ],

            'password' => $this->editingId === null
                ? ['required', 'string', 'min:8', 'max:72']
                : ['exclude'],

            'role' => [
                'required',
                Rule::in(self::ROLES),
            ],

            'rewardPoints' => $this->editingId === null
                ? [
                    'required',
                    'integer',
                    'min:0',
                    'max:2147483647',
                ]
                : ['exclude'],
        ], [
            ...$this->avatarMessages(),
            'required' => 'Vui lòng nhập :attribute.',

            'name.max' => 'Họ tên tối đa 255 ký tự.',

            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email tối đa 255 ký tự.',
            'email.unique' => 'Email đã được sử dụng.',

            'phone.regex' => 'Số điện thoại gồm 10 số, bắt đầu bằng 03, 05, 07, 08 hoặc 09.',

            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.max' => 'Mật khẩu tối đa 72 ký tự.',

            'role.in' => 'Vai trò không hợp lệ.',

            'rewardPoints.integer' => 'Điểm thưởng phải là số nguyên.',
            'rewardPoints.min' => 'Điểm thưởng không được âm.',
            'rewardPoints.max' => 'Điểm thưởng tối đa 2147483647.',
        ], [
            'name' => 'họ tên',
            'email' => 'email',
            'phone' => 'số điện thoại',
            'password' => 'mật khẩu',
            'role' => 'vai trò',
            'rewardPoints' => 'điểm thưởng',
        ]);

        if (
            $this->editingId === (int) Auth::id()
            && $data['role'] !== 'admin'
        ) {
            throw ValidationException::withMessages([
                'role' => 'Bạn không thể tự hạ quyền tài khoản đang đăng nhập.',
            ]);
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role'],
        ];

        if ($this->editingId === null) {
            $payload['password'] = $data['password'];
            $payload['reward_points'] = (int) $data['rewardPoints'];
        }

        return $payload;
    }

    private function hasChanges(User $user, array $data): bool
    {
        $copy = clone $user;

        $copy->fill(Arr::except($data, ['password']));

        return $copy->isDirty([
            'name',
            'email',
            'phone',
            'role',
        ]) || $this->avatarHasChanges($user);
    }

    private function signature(array $data): string
    {
        return hash_hmac(
            'sha256',
            json_encode(
                [
                    $this->editingId,
                    $data,
                    $this->avatarChecksum(),
                ],
                JSON_THROW_ON_ERROR
            ),
            (string) config('app.key')
        );
    }

    // Helper form tài khoản
    private function resetPasswordForm(): void
    {
        $this->reset([
            'showPasswordModal',
            'resetUserId',
            'resetUserName',
            'resetUserEmail',
            'resetPassword',
            'resetPasswordConfirmationHash',
        ]);

        $this->resetValidation();
    }

    private function validatedResetPassword(): string
    {
        return $this->validate([
            'resetPassword' => [
                'required',
                'string',
                'min:8',
                'max:72',
            ],
        ], [
            'resetPassword.required' => 'Vui lòng nhập mật khẩu mới.',
            'resetPassword.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'resetPassword.max' => 'Mật khẩu mới tối đa 72 ký tự.',
        ])['resetPassword'];
    }

    private function resetPasswordSignature(string $password): string
    {
        return hash_hmac(
            'sha256',
            json_encode(
                [$this->resetUserId, $password],
                JSON_THROW_ON_ERROR
            ),
            (string) config('app.key')
        );
    }

    // Helper avatar
    private function avatarRules(): array
    {
        return [
            'bail',
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
        ];
    }

    private function avatarMessages(): array
    {
        return [
            'newAvatar.image' => 'Tệp tải lên phải là ảnh.',
            'newAvatar.mimes' => 'Chỉ chấp nhận JPG, JPEG, PNG hoặc WEBP.',
            'newAvatar.max' => 'Ảnh đại diện tối đa 2 MB.',
        ];
    }

    private function avatarHasChanges(User $user): bool
    {
        if ($this->newAvatar === null) {
            return false;
        }

        $path = trim((string) $user->avatar);

        if (
            $path === ''
            || Str::startsWith($path, ['http://', 'https://'])
        ) {
            return true;
        }

        try {
            $disk = Storage::disk('r2');

            if (!$disk->exists($path)) {
                return true;
            }

            $contents = $disk->get($path);

            if (!is_string($contents)) {
                throw new RuntimeException('Không đọc được ảnh hiện tại.');
            }

            return !hash_equals(
                hash('sha256', $contents),
                $this->avatarChecksum()
            );
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'newAvatar' => 'Không thể kiểm tra ảnh hiện tại. Vui lòng thử lại.',
            ]);
        }
    }

    private function avatarChecksum(): ?string
    {
        if ($this->newAvatar === null) {
            return null;
        }

        try {
            $contents = $this->newAvatar->get();

            if (!is_string($contents)) {
                throw new RuntimeException('Không đọc được ảnh đã chọn.');
            }

            return hash('sha256', $contents);
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'newAvatar' => 'Không đọc được ảnh đã chọn. Vui lòng chọn lại.',
            ]);
        }
    }

    private function deleteManagedAvatar(?string $path): void
    {
        if (
            !$path
            || !preg_match(
                '#\Aavatars/accounts/[1-9][0-9]*/[^/]+\z#D',
                $path
            )
            || str_contains($path, '..')
            || str_contains($path, '\\')
        ) {
            return;
        }

        try {
            // Không xóa ảnh còn được tài khoản tham chiếu.
            if (User::where('avatar', $path)->exists()) {
                return;
            }

            if (!Storage::disk('r2')->delete($path)) {
                throw new RuntimeException('Không dọn được ảnh đại diện.');
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function notify(string $icon, string $title): void
    {
        $this->dispatch(
            'swal',
            icon: $icon,
            title: $title
        );
    }

    public function render()
    {
        // Thống kê toàn bộ tài khoản, độc lập bộ lọc danh sách.
        $groups = User::query()
            ->select('role', 'status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('role', 'status')
            ->toBase()
            ->get();

        $countRoles = fn(array $roles): int =>
            (int) $groups->whereIn('role', $roles)->sum('total');

        $counts = [
            'all' => (int) $groups->sum('total'),

            'internal' => $countRoles([
                'admin',
                'farm_owner',
                'farm_staff',
                'sales',
            ]),

            'customer' => $countRoles(['customer']),
            'farm_staff' => $countRoles(['farm_staff']),
            'sales' => $countRoles(['sales']),

            'blocked' => (int) $groups
                ->where('status', 'blocked')
                ->sum('total'),
        ];

        $users = User::query()
            ->select(
                'id',
                'name',
                'email',
                'phone',
                'avatar',
                'role',
                'status',
                'reward_points',
                'created_at'
            )

            ->search($this->search)
            ->when(
                $this->tab === 'internal',
                fn($query) => $query->whereIn(
                    'role',
                    ['admin', 'farm_owner', 'farm_staff', 'sales']
                )
            )
            ->when(
                $this->tab === 'customer',
                fn($query) => $query->where('role', 'customer')
            )
            ->when(
                $this->tab === 'blocked',
                fn($query) => $query->where('status', 'blocked')
            )
            ->when(
                in_array($this->roleFilter, self::ROLES, true),
                fn($query) => $query->where(
                    'role',
                    $this->roleFilter
                )
            )
            ->when(
                in_array(
                    $this->statusFilter,
                    ['active', 'inactive', 'blocked'],
                    true
                ),
                fn($query) => $query->where(
                    'status',
                    $this->statusFilter
                )
            )
            ->latest('id')
            ->paginate(10);

        return view(
            'livewire.admin.account-manager',
            compact('users', 'counts')
        );
    }
}