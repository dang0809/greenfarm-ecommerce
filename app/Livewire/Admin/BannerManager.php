<?php

namespace App\Livewire\Admin;

use App\Models\Banner;
use App\Models\FarmImage;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;
use Throwable;

#[Layout('layouts.admin.app')]
#[Title('Nội Dung & Banner | GreenFarm Admin')]
class BannerManager extends Component
{
    use WithFileUploads;

    private const IMAGE_DISK = 'r2';

    public array $banner = [
        'title' => '',
        'description' => '',
        'button_text' => '',
        'link' => '',
        'sort_order' => '1',
        'status' => 'active',
    ];

    public array $page = [
        'title' => '',
        'slug' => '',
        'content' => '',
    ];

    public array $farm = [
        'caption' => '',
        'sort_order' => '1',
    ];

    public $bannerFile = null;
    public $farmFile = null;

    public int $fileInputKey = 0;

    #[Locked]
    public ?string $modal = null;

    #[Locked]
    public ?string $pendingType = null;

    #[Locked]
    public ?string $confirmationHash = null;

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

    public function updated(): void
    {
        // Dữ liệu thay đổi thì xác nhận trước đó không còn hiệu lực.
        $this->cancelConfirmation();
    }

    private function assertR2Configured(): void
    {
        foreach (['key', 'secret', 'bucket', 'endpoint', 'url'] as $field) {
            $value = config('filesystems.disks.r2.' . $field);

            if (!is_string($value) || trim($value) === '') {
                throw new RuntimeException(
                    'Thiếu cấu hình filesystems.disks.r2.' . $field
                );
            }
        }
    }

    private function getImageDisk(): string
    {
        $this->assertR2Configured();

        return self::IMAGE_DISK;
    }

    private function uploadImageToR2(
        string $type,
        ?string &$newPath
    ): string {
        $file = $this->selectedFile($type);

        if ($file === null) {
            throw new RuntimeException('Bạn chưa chọn ảnh.');
        }

        $disk = Storage::disk($this->getImageDisk());

        $folder = match ($type) {
            'banner' => 'content/banners',
            'farm-image' => 'content/farm-images',
            default => throw new RuntimeException(
                'Loại ảnh không hợp lệ.'
            ),
        };

        $filename = Str::uuid() . '.' . $file->extension();

        // Gán trước khi ghi để catch biết object cần dọn nếu upload lỗi.
        $newPath = $folder . '/' . $filename;

        $stream = $file->readStream();

        if (!is_resource($stream)) {
            throw new RuntimeException('Không đọc được ảnh tạm.');
        }

        try {
            $stored = $disk->put($newPath, $stream, [
                'ContentType' => $file->getMimeType(),
            ]);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        // Kiểm tra kết quả ghi và sự tồn tại như mẫu CategoryManager.
        if (!$stored || !$disk->exists($newPath)) {
            throw new RuntimeException(
                'Không tải được ảnh lên R2.'
            );
        }

        return $newPath;
    }

    private function deleteManagedImage(?string $path): void
    {
        // Chỉ dọn object có cấu trúc do chức năng này tạo.
        if (
            !$path
            || preg_match(
                '#\Acontent/(?:banners|farm-images)/[0-9a-f-]{36}\.(?:jpg|jpeg|png|webp)\z#i',
                $path
            ) !== 1
        ) {
            return;
        }

        try {
            // Giữ ảnh nếu database vẫn còn tham chiếu.
            if (
                Banner::where('image', $path)->exists()
                || FarmImage::where('image_url', $path)->exists()
            ) {
                return;
            }

            if (!Storage::disk(self::IMAGE_DISK)->delete($path)) {
                throw new RuntimeException(
                    'Không dọn được ảnh trên R2.'
                );
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function openForm(string $type): void
    {
        abort_unless(
            in_array($type, ['banner', 'page', 'farm-image'], true),
            404
        );

        $this->resetForms();
        $this->modal = $type;
    }

    public function closeModal(): void
    {
        $this->modal = null;
        $this->cancelConfirmation();
    }

    private function resetForms(): void
    {
        $this->reset([
            'banner',
            'page',
            'farm',
            'bannerFile',
            'farmFile',
        ]);

        // Chỉ thay key khi bắt đầu form mới hoặc lưu thành công.
        $this->fileInputKey++;

        $this->resetValidation();
        $this->cancelConfirmation();
    }

    public function cancelConfirmation(): void
    {
        // Không reset dữ liệu, không xóa ảnh tạm, không đóng modal.
        $this->pendingType = null;
        $this->confirmationHash = null;
    }

    private function validatedData(string $type): array
    {
        $imageRules = [
            'required',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:5120',
        ];

        $rules = match ($type) {
            'banner' => [
                'banner.title' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'banner.description' => [
                    'required',
                    'string',
                    'max:1000',
                ],
                'banner.button_text' => [
                    'required',
                    'string',
                    'max:50',
                ],
                'banner.link' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) {
                            $internal = str_starts_with($value, '/')
                            && !str_starts_with($value, '//')
                            && !str_contains($value, '\\')
                            && !preg_match('/\s/u', $value);

                            $external = filter_var($value, FILTER_VALIDATE_URL)
                            && in_array(
                                strtolower(
                                    (string) parse_url($value, PHP_URL_SCHEME)
                                ),
                                ['http', 'https'],
                                true
                            );

                            if (!$internal && !$external) {
                                $fail(
                                'Liên kết phải là /duong-dan hoặc URL http/https hợp lệ.'
                                );
                            }
                        },
                ],
                'banner.sort_order' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:2147483647',
                ],
                'banner.status' => [
                    'required',
                    Rule::in(['active', 'inactive']),
                ],
                'bannerFile' => $imageRules,
            ],

            'page' => [
                'page.title' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'page.slug' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/',
                    Rule::unique('pages', 'slug'),
                ],
                'page.content' => [
                    'required',
                    'string',
                    'max:15000',
                ],
            ],

            'farm-image' => [
                'farm.caption' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'farm.sort_order' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:2147483647',
                ],
                'farmFile' => $imageRules,
            ],
        };

        // Tránh chạy các rule phía sau khi trường đã sai kiểu dữ liệu.
        foreach ($rules as &$fieldRules) {
            array_unshift($fieldRules, 'bail');
        }
        unset($fieldRules);

        $validated = $this->validate($rules, [
            'required' => 'Vui lòng nhập hoặc chọn :attribute.',
            'string' => ':attribute phải là chuỗi văn bản.',
            'max' => ':attribute vượt quá giới hạn :max.',
            'integer' => ':attribute phải là số nguyên.',
            'min' => ':attribute phải từ :min trở lên.',
            'in' => ':attribute không hợp lệ.',
            'image' => ':attribute phải là hình ảnh hợp lệ.',
            'mimes' => ':attribute chỉ nhận JPG, JPEG, PNG hoặc WebP.',

            'page.slug.regex' =>
                'Slug chỉ gồm chữ thường không dấu, số và dấu gạch ngang.',
            'page.slug.unique' =>
                'Đường dẫn này đã được sử dụng.',

            'bannerFile.max' =>
                'Ảnh banner không được vượt quá 5 MB.',
            'farmFile.max' =>
                'Ảnh nông trại không được vượt quá 5 MB.',
        ], [
            'banner.title' => 'tiêu đề banner',
            'banner.description' => 'mô tả banner',
            'banner.button_text' => 'chữ trên nút bấm',
            'banner.link' => 'đường dẫn liên kết',
            'banner.sort_order' => 'thứ tự banner',
            'banner.status' => 'trạng thái banner',
            'bannerFile' => 'ảnh banner',

            'page.title' => 'tiêu đề trang',
            'page.slug' => 'đường dẫn trang',
            'page.content' => 'nội dung trang',

            'farm.caption' => 'chú thích ảnh',
            'farm.sort_order' => 'thứ tự ảnh',
            'farmFile' => 'ảnh nông trại',
        ]);

        $prefix = $type === 'farm-image' ? 'farm' : $type;

        // Chỉ lấy các cột được phép lưu.
        $keys = match ($type) {
            'banner' => [
                'title',
                'description',
                'button_text',
                'link',
                'sort_order',
                'status',
            ],
            'page' => ['title', 'slug', 'content'],
            'farm-image' => ['caption', 'sort_order'],
        };

        $payload = [];

        foreach ($keys as $key) {
            $value = $validated[$prefix][$key];

            $payload[$key] = is_string($value)
                ? trim($value)
                : $value;
        }

        if (isset($payload['sort_order'])) {
            $payload['sort_order'] = (int) $payload['sort_order'];
        }

        if ($type === 'page') {
            /*
             * Textarea hiện nhận văn bản thuần.
             * AboutPage đang xuất nội dung bằng {!! !!},
             * vì vậy escape trước khi lưu và giữ xuống dòng.
             */
            $payload['content'] = nl2br(e($payload['content']));

            // Bảo đảm phù hợp giới hạn cột TEXT hiện tại trên MySQL.
            if (strlen($payload['content']) > 65535) {
                throw ValidationException::withMessages([
                    'page.content' =>
                        'Nội dung quá dài sau khi định dạng. Vui lòng rút ngắn.',
                ]);
            }
        }

        return $payload;
    }

    private function selectedFile(string $type)
    {
        return match ($type) {
            'banner' => $this->bannerFile,
            'farm-image' => $this->farmFile,
            default => null,
        };
    }

    private function signature(string $type, array $data): string
    {
        $checksum = null;
        $file = $this->selectedFile($type);

        if ($file !== null) {
            try {
                $contents = $file->get();

                if (!is_string($contents)) {
                    throw new RuntimeException('Không đọc được file tạm.');
                }

                $checksum = hash('sha256', $contents);
            } catch (Throwable $exception) {
                report($exception);

                throw ValidationException::withMessages([
                    $type === 'banner' ? 'bannerFile' : 'farmFile'
                    => 'Không đọc được ảnh. Vui lòng chọn lại.',
                ]);
            }
        }

        return hash_hmac(
            'sha256',
            json_encode(
                [$type, $data, $checksum],
                JSON_THROW_ON_ERROR
            ),
            (string) config('app.key')
        );
    }

    public function requestSave(string $type): void
    {
        if (
            !in_array($type, ['banner', 'page', 'farm-image'], true)
            || $this->modal !== $type
        ) {
            return;
        }

        $this->cancelConfirmation();
        $this->resetValidation();

        // Validate trước. Nếu sai, Livewire hiển thị lỗi và dừng.
        $data = $this->validatedData($type);

        $this->confirmationHash = $this->signature($type, $data);
        $this->pendingType = $type;

        // Bước này chưa ghi database hoặc lưu ảnh chính thức.
        $this->dispatch(
            'confirm-content-save',
            type: $type
        )->self();
    }

    public function saveConfirmed(): void
    {
        $type = $this->pendingType;

        if (
            !in_array($type, ['banner', 'page', 'farm-image'], true)
            || $this->modal !== $type
            || $this->confirmationHash === null
        ) {
            return;
        }

        // Kiểm tra lại dữ liệu trước khi ghi.
        $data = $this->validatedData($type);

        if (
            !hash_equals(
                $this->confirmationHash,
                $this->signature($type, $data)
            )
        ) {
            $this->cancelConfirmation();

            $this->addError(
                'save',
                'Dữ liệu đã thay đổi. Vui lòng nhấn Lưu và xác nhận lại.'
            );

            return;
        }

        $this->cancelConfirmation();

        $newPath = null;

        try {
            if ($type !== 'page') {
                $column = $type === 'banner'
                    ? 'image'
                    : 'image_url';

                $data[$column] = $this->uploadImageToR2(
                    $type,
                    $newPath
                );
            }

            DB::transaction(function () use ($type, $data) {
                $record = match ($type) {
                    'banner' => new Banner(),
                    'page' => new Page(),
                    'farm-image' => new FarmImage(),
                };

                $record->fill($data);

                if (!$record->saveOrFail()) {
                    throw new RuntimeException(
                        'Không lưu được bản ghi.'
                    );
                }
            });
        } catch (UniqueConstraintViolationException $exception) {
            $this->deleteManagedImage($newPath);

            report($exception);

            $this->addError(
                $type === 'page' ? 'page.slug' : 'save',
                $type === 'page'
                ? 'Đường dẫn vừa được sử dụng. Hãy chọn đường dẫn khác.'
                : 'Dữ liệu bị trùng. Vui lòng kiểm tra lại.'
            );

            return;
        } catch (Throwable $exception) {
            $this->deleteManagedImage($newPath);

            report($exception);

            $this->addError(
                'save',
                'Không thể lưu dữ liệu hoặc tải ảnh lên R2. Vui lòng thử lại.'
            );

            return;
        }

        $message = match ($type) {
            'banner' => 'Thêm banner thành công.',
            'page' => 'Thêm trang nội dung thành công.',
            'farm-image' => 'Tải ảnh nông trại thành công.',
        };

        $this->modal = null;
        $this->resetForms();

        // Database đã lưu thành công mới thông báo.
        $this->dispatch(
            'swal',
            icon: 'success',
            title: $message
        );
    }

    public function render()
    {
        return view('livewire.admin.banner-manager', [
            'banners' => Banner::orderBy('sort_order')
                ->orderBy('id')
                ->get(),

            'pages' => Page::latest('updated_at')
                ->orderByDesc('id')
                ->get(),

            'farmImages' => FarmImage::orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }
}