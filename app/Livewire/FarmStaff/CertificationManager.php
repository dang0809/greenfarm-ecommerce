<?php

namespace App\Livewire\FarmStaff;

use App\Models\Certification;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;
use Throwable;

#[Layout('layouts.farm-staff.app')]
#[Title('Hồ Sơ Kiểm Định & Chứng Nhận | Nông Trại Đất Lành')]
class CertificationManager extends Component
{
    use WithFileUploads;

    public string $search = '';
    public string $statusFilter = 'all';
    public bool $showForm = false;

    #[Locked]
    public ?int $editingId = null;

    #[Locked]
    public ?int $scanId = null;

    #[Locked]
    public ?string $currentFileUrl = null;

    #[Locked]
    public bool $currentIsPdf = false;

    public string $name = '';
    public string $code = '';
    public string $status = 'active';
    public string $issuedDate = '';
    public string $expiryDate = '';
    public string $description = '';

    public $newFile = null;
    public int $fileInputKey = 0;

    public function boot(): void
    {
        // Kiểm tra lại quyền ở mọi request Livewire.
        $id = Auth::guard('web')->id();
        $user = $id ? User::find($id) : null;

        abort_unless(
            $user
                && $user->role === 'farm_staff'
                && $user->status === 'active',
            403
        );
    }

    public function openCreate(): void
    {
        $this->closeScan();
        $this->resetForm();

        $this->issuedDate = today()->toDateString();
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $cert = Certification::findOrFail($id);

        $this->closeScan();
        $this->resetForm();

        $this->editingId = $cert->id;
        $this->name = $cert->name;
        $this->code = $cert->code;

        $this->status = $this->effectiveStatus($cert);

        $this->issuedDate = $cert->issued_date?->toDateString() ?? '';
        $this->expiryDate = $cert->expiry_date?->toDateString() ?? '';
        $this->description = (string) ($cert->description ?? '');

        $this->currentFileUrl = $this->scanUrl($cert->image);
        $this->currentIsPdf = $this->isPdf($cert->image);

        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function openScan(int $id): void
    {
        Certification::findOrFail($id);

        $this->closeForm();
        $this->scanId = $id;
    }

    public function closeScan(): void
    {
        $this->scanId = null;
    }

    private function fileRules(): array
    {
        return [
            'bail',
            $this->currentFileUrl === null ? 'required' : 'nullable',
            'file',
            'mimes:jpg,jpeg,png,webp,pdf',
            'max:5120',
        ];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('certifications', 'code')
                    ->ignore($this->editingId),
            ],

            'status' => [
                'required',
                Rule::in(['active', 'expired']),
            ],

            'issuedDate' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],

            'expiryDate' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:issuedDate',
            ],

            'description' => [
                'required',
                'string',
                'max:10000',
            ],

            'newFile' => $this->fileRules(),
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên giấy kiểm định.',
            'name.max' => 'Tên giấy kiểm định tối đa 255 ký tự.',

            'code.required' => 'Vui lòng nhập mã quản lý.',
            'code.max' => 'Mã quản lý tối đa 50 ký tự.',
            'code.unique' => 'Mã quản lý đã tồn tại.',

            'status.in' => 'Trạng thái không hợp lệ.',

            'issuedDate.required' => 'Vui lòng chọn ngày cấp.',
            'issuedDate.date_format' => 'Ngày cấp không hợp lệ.',
            'issuedDate.before_or_equal' => 'Ngày cấp không được sau hôm nay.',

            'expiryDate.date_format' => 'Ngày hết hạn không hợp lệ.',
            'expiryDate.after_or_equal' => 'Ngày hết hạn không được trước ngày cấp.',

            'description.required' => 'Vui lòng nhập tóm tắt kết quả.',
            'description.max' => 'Tóm tắt kết quả tối đa 10.000 ký tự.',

            'newFile.required' => 'Vui lòng chọn bản scan.',
            'newFile.file' => 'Tệp tải lên không hợp lệ.',
            'newFile.mimes' => 'Chỉ nhận JPG, JPEG, PNG, WEBP hoặc PDF.',
            'newFile.max' => 'Bản scan không được vượt quá 5 MB.',
        ];
    }

    public function updatedNewFile(): void
    {
        try {
            $this->validateOnly('newFile');
        } catch (ValidationException $exception) {
            // Không tạo preview cho file không hợp lệ.
            $this->reset('newFile');
            $this->fileInputKey++;

            throw $exception;
        }
    }

    public function save(): void
    {
        $this->resetValidation('form');

        $this->name = trim($this->name);
        $this->code = trim($this->code);
        $this->description = trim($this->description);

        $data = $this->validate();

        $newPath = null;
        $oldPath = null;

        try {
            DB::transaction(function () use (
                $data,
                &$newPath,
                &$oldPath
            ) {
                $cert = $this->editingId === null
                    ? new Certification()
                    : Certification::query()
                        ->lockForUpdate()
                        ->findOrFail($this->editingId);

                $cert->fill([
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'description' => $data['description'],
                    'issued_date' => $data['issuedDate'],
                    'expiry_date' => $data['expiryDate'] ?: null,

                    // Khi lưu, giấy đã quá ngày hết hạn luôn là expired.
                    'status' => $data['expiryDate'] !== ''
                        && $data['expiryDate'] < today()->toDateString()
                            ? 'expired'
                            : $data['status'],
                ]);

                if ($this->newFile !== null) {
                    $newPath = $this->newFile->store(
                        'certifications',
                        'public'
                    );

                    if (
                        ! is_string($newPath)
                        || $newPath === ''
                        || ! Storage::disk('public')->exists($newPath)
                    ) {
                        throw new RuntimeException(
                            'Không lưu được bản scan.'
                        );
                    }

                    // Đường dẫn cũ lấy từ bản ghi đã khóa.
                    $oldPath = $cert->image;
                    $cert->image = $newPath;
                }

                // Không chọn file mới: giữ nguyên image hiện có.
                if (! $cert->save()) {
                    throw new RuntimeException(
                        'Không lưu được giấy kiểm định.'
                    );
                }
            });
        } catch (UniqueConstraintViolationException $exception) {
            $this->deleteManagedFile($newPath);

            $this->addError(
                'code',
                'Mã vừa được sử dụng. Vui lòng chọn mã khác.'
            );

            return;
        } catch (Throwable $exception) {
            $this->deleteManagedFile($newPath);
            report($exception);

            $this->addError(
                'form',
                'Không thể lưu giấy kiểm định. Vui lòng thử lại.'
            );

            return;
        }

        // Chỉ dọn file cũ sau khi database đã commit.
        $this->deleteManagedFile($oldPath);

        $this->closeForm();
        $this->notify('success', 'Đã lưu giấy kiểm định!');
    }

    public function requestDelete(int $id): void
    {
        $cert = Certification::findOrFail($id);

        // Bước này chưa xóa dữ liệu.
        $this->dispatch(
            'confirm-certification-delete',
            id: $cert->id,
            name: $cert->code.' - '.$cert->name
        )->self();
    }

    public function deleteCertification(int $id): void
    {
        $oldPath = null;

        try {
            DB::transaction(function () use ($id, &$oldPath) {
                $cert = Certification::query()
                    ->lockForUpdate()
                    ->find($id);

                if (! $cert) {
                    return;
                }

                $oldPath = $cert->image;

                if (! $cert->delete()) {
                    throw new RuntimeException(
                        'Không xóa được giấy kiểm định.'
                    );
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            $this->notify(
                'error',
                'Không thể xóa giấy kiểm định.'
            );

            return;
        }

        $this->deleteManagedFile($oldPath);

        if ($this->editingId === $id) {
            $this->closeForm();
        }

        if ($this->scanId === $id) {
            $this->closeScan();
        }

        $this->notify(
            'success',
            'Giấy kiểm định đã được gỡ khỏi danh sách.'
        );
    }

    private function deleteManagedFile(?string $path): void
    {
        // Chỉ xóa file trong thư mục do chức năng này quản lý.
        // Bỏ qua URL ngoài và đường dẫn thư mục khác.
        if (
            ! $path
            || ! preg_match(
                '#\Acertifications/[A-Za-z0-9_-]+\.(?:jpg|jpeg|png|webp|pdf)\z#i',
                $path
            )
        ) {
            return;
        }

        try {
            if (
                ! Certification::where('image', $path)->exists()
                && ! Storage::disk('public')->delete($path)
            ) {
                throw new RuntimeException(
                    'Không dọn được bản scan cũ.'
                );
            }
        } catch (Throwable $exception) {
            // Dọn file thất bại không đảo ngược DB đã commit.
            report($exception);
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'code',
            'status',
            'issuedDate',
            'expiryDate',
            'description',
            'newFile',
            'currentFileUrl',
            'currentIsPdf',
        ]);

        $this->fileInputKey++;
        $this->resetValidation();
    }

    private function notify(string $icon, string $title): void
    {
        // Layout Farm Staff đã có listener swal.
        $this->dispatch(
            'swal',
            icon: $icon,
            title: $title
        );
    }

    private function filterByEffectiveStatus(
        Builder $query,
        string $status
    ): Builder {
        if ($status === 'active') {
            return $query
                ->where('status', 'active')
                ->where(function (Builder $query) {
                    $query->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', today());
                });
        }

        if ($status === 'expired') {
            return $query->where(function (Builder $query) {
                $query->where('status', 'expired')
                    ->orWhereDate('expiry_date', '<', today());
            });
        }

        return $query;
    }

    private function effectiveStatus(Certification $cert): string
    {
        if (
            $cert->status === 'expired'
            || (
                $cert->expiry_date !== null
                && $cert->expiry_date->lt(today())
            )
        ) {
            return 'expired';
        }

        return 'active';
    }

    private function scanUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        // Hỗ trợ URL ảnh hiện có trong database.
        if (preg_match('#\Ahttps?://#i', $path)) {
            return $path;
        }

        return Storage::disk('public')->url(ltrim($path, '/'));
    }

    private function isPdf(?string $path): bool
    {
        $filePath = parse_url(
            trim((string) $path),
            PHP_URL_PATH
        );

        return is_string($filePath)
            && strtolower(
                pathinfo($filePath, PATHINFO_EXTENSION)
            ) === 'pdf';
    }

    private function prepareForView(Certification $cert): object
    {
        return (object) [
            'id' => $cert->id,
            'name' => $cert->name,
            'code' => $cert->code,
            'image' => $cert->image,
            'description' => $cert->description,
            'issued_date' => $cert->issued_date,
            'expiry_date' => $cert->expiry_date,
            'status' => $cert->status,

            // Các giá trị được component tính để hiển thị.
            'effective_status' => $this->effectiveStatus($cert),
            'image_url' => $this->scanUrl($cert->image),
            'is_pdf' => $this->isPdf($cert->image),
        ];
    }

    public function render()
    {
        $search = trim($this->search);

        $query = Certification::query()
            ->when(
                $search !== '',
                function (Builder $query) use ($search) {
                    $query->where(
                        function (Builder $query) use ($search) {
                            $query->where(
                                'name',
                                'like',
                                '%'.$search.'%'
                            )->orWhere(
                                'code',
                                'like',
                                '%'.$search.'%'
                            );
                        }
                    );
                }
            );

        // Lọc bằng hàm của component.
        $certifications = $this->filterByEffectiveStatus(
            $query,
            $this->statusFilter
        )
            ->orderByDesc('id')
            ->get()
            ->map(
                fn (Certification $cert) => $this->prepareForView($cert)
            )
            ->values();

        // Chuẩn bị dữ liệu cho modal xem bản scan.
        $scanModel = $this->scanId
            ? Certification::find($this->scanId)
            : null;

        $scanCertification = $scanModel
            ? $this->prepareForView($scanModel)
            : null;

        $uploadPreviewUrl = null;
        $uploadFileName = null;

        if (
            $this->newFile instanceof TemporaryUploadedFile
            && ! $this->getErrorBag()->has('newFile')
        ) {
            $uploadFileName = $this->newFile->getClientOriginalName();

            // Chỉ tạo temporary preview cho ảnh.
            if (
                in_array(
                    $this->newFile->getMimeType(),
                    [
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ],
                    true
                )
                && $this->newFile->isPreviewable()
            ) {
                $uploadPreviewUrl = $this->newFile->temporaryUrl();
            }
        }

        return view(
            'livewire.farm-staff.certification-manager',
            compact(
                'certifications',
                'scanCertification',
                'uploadPreviewUrl',
                'uploadFileName'
            )
        );
    }
}