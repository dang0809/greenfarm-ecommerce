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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
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
    public ?int $editingId = null;

    #[Locked]
    public ?string $editingVersion = null;

    #[Locked]
    public ?string $currentImagePath = null;

    #[Locked]
    public ?string $currentImageUrl = null;

    #[Locked]
    public string $originalPageHtml = '';

    #[Locked]
    public string $originalPageText = '';

    #[Locked]
    public array $pendingAction = [];

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

    public function updated(string $property): void
    {
        // Dữ liệu thay đổi thì xác nhận trước đó không còn hiệu lực.
        $this->cancelConfirmation();

        $fields = [
            'banner.title',
            'banner.description',
            'banner.button_text',
            'banner.link',
            'banner.sort_order',
            'banner.status',
            'bannerFile',

            'page.title',
            'page.slug',
            'page.content',

            'farm.caption',
            'farm.sort_order',
            'farmFile',
        ];

        if (in_array($property, $fields, true)) {
            // Chỉ xóa lỗi của trường vừa thay đổi.
            $this->resetValidation($property);
        }
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
        if ($this->pendingAction || $this->confirmationHash !== null) {
            return;
        }
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

    private function modelClass(string $type): string
    {
        return match ($type) {
            'banner' => Banner::class,
            'page' => Page::class,
            'farm-image' => FarmImage::class,
            default => abort(404),
        };
    }

    private function getRecord(
        string $type,
        int $id,
        bool $lock = false
    ): ?Model {
        $class = $this->modelClass($type);
        $query = $class::query();

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->find($id);
    }

    private function recordVersion(Model $record): string
    {
        return hash(
            'sha256',
            json_encode(
                $record->getRawOriginal(),
                JSON_THROW_ON_ERROR
            )
        );
    }

    private function ensureCurrent(?Model $record): void
    {
        if (
            !$record
            || $this->editingVersion !== $this->recordVersion($record)
        ) {
            throw ValidationException::withMessages([
                'save' =>
                    'Bản ghi đã thay đổi hoặc bị xóa. Vui lòng đóng và mở lại form.',
            ]);
        }
    }

    private function normalizeText(string $text): string
    {
        return trim(str_replace(["\r\n", "\r"], "\n", $text));
    }

    private function pageText(string $html): string
    {
        $text = preg_replace(
            '#<br\s*/?>\r?\n?#i',
            "\n",
            $html
        ) ?? $html;

        $text = preg_replace(
            '#</(?:p|div|li|h[1-6])\s*>#i',
            "\n",
            $text
        ) ?? $text;

        return $this->normalizeText(
            html_entity_decode(
                strip_tags($text),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            )
        );
    }

    public function openEdit(string $type, int $id): void
    {
        if ($this->pendingAction || $this->confirmationHash !== null) {
            return;
        }

        $record = $this->getRecord($type, $id);

        if (!$record) {
            $this->notify('info', 'Nội dung không còn tồn tại.');
            return;
        }

        $this->resetForms();

        $this->modal = $type;
        $this->editingId = (int) $record->id;
        $this->editingVersion = $this->recordVersion($record);

        if ($type === 'banner') {
            $this->banner = [
                'title' => (string) $record->title,
                'description' => (string) $record->description,
                'button_text' => (string) $record->button_text,
                'link' => (string) $record->link,
                'sort_order' => (string) $record->sort_order,
                'status' => (string) $record->status,
            ];

            $this->currentImagePath = $record->image;
            $this->currentImageUrl = $record->image_url;
        } elseif ($type === 'page') {
            $this->originalPageHtml = (string) $record->content;
            $this->originalPageText = $this->pageText(
                $this->originalPageHtml
            );

            $this->page = [
                'title' => (string) $record->title,
                'slug' => (string) $record->slug,
                'content' => $this->originalPageText,
            ];
        } else {
            $this->farm = [
                'caption' => (string) $record->caption,
                'sort_order' => (string) $record->sort_order,
            ];

            $this->currentImagePath = $record->image_url;
            $this->currentImageUrl = $record->display_url;
        }
    }

    public function requestToggleStatus(int $id): void
    {
        $this->requestAction('status', 'banner', $id);
    }

    public function requestDelete(string $type, int $id): void
    {
        $this->requestAction('delete', $type, $id);
    }

    private function requestAction(
        string $kind,
        string $type,
        int $id
    ): void {
        if (
            $this->modal !== null
            || $this->pendingAction
            || $this->confirmationHash !== null
        ) {
            return;
        }

        abort_unless(
            $id > 0 && (
                ($kind === 'status' && $type === 'banner')
                || (
                    $kind === 'delete'
                    && in_array($type, ['banner', 'farm-image'], true)
                )
            ),
            422
        );

        $record = $this->getRecord($type, $id);

        if (!$record) {
            $this->notify('info', 'Nội dung không còn tồn tại.');
            return;
        }

        if (
            $kind === 'status'
            && !in_array($record->status, ['active', 'inactive'], true)
        ) {
            $this->notify('error', 'Trạng thái banner không hợp lệ.');
            return;
        }

        $to = $kind === 'status'
            ? ($record->status === 'active' ? 'inactive' : 'active')
            : null;

        $this->pendingAction = [
            'kind' => $kind,
            'type' => $type,
            'id' => $id,
            'version' => $this->recordVersion($record),
            'to' => $to,
        ];

        $this->dispatch(
            'confirm-content-action',
            kind: $kind,
            name: (string) (
                $type === 'banner'
                ? $record->title
                : $record->caption
            ),
            enabling: $to === 'active'
        )->self();
    }

    public function cancelAction(): void
    {
        $this->pendingAction = [];
    }

    public function saveAction(): void
    {
        $pending = $this->pendingAction;
        $this->pendingAction = [];

        if (!$pending) {
            return;
        }

        try {
            $result = DB::transaction(function () use ($pending) {
                $record = $this->getRecord(
                    $pending['type'],
                    $pending['id'],
                    true
                );

                if (!$record) {
                    return [
                        'saved' => false,
                        'status' => null,
                        'path' => null,
                    ];
                }

                if (
                    $this->recordVersion($record)
                    !== $pending['version']
                ) {
                    return [
                        'saved' => false,
                        'status' => $pending['type'] === 'banner'
                            ? $record->status
                            : null,
                        'path' => null,
                    ];
                }

                if ($pending['kind'] === 'status') {
                    $record->status = $pending['to'];

                    if (!$record->saveOrFail()) {
                        throw new RuntimeException(
                            'Không lưu được trạng thái.'
                        );
                    }

                    return [
                        'saved' => true,
                        'status' => $record->status,
                        'path' => null,
                    ];
                }

                $column = $pending['type'] === 'banner'
                    ? 'image'
                    : 'image_url';

                $path = $record->getRawOriginal($column);

                if (!$record->delete()) {
                    throw new RuntimeException(
                        'Không xóa được bản ghi.'
                    );
                }

                return [
                    'saved' => true,
                    'status' => null,
                    'path' => $path,
                ];
            });
        } catch (Throwable $exception) {
            report($exception);

            $this->notify(
                'error',
                'Thao tác chưa hoàn tất. Vui lòng thử lại.'
            );

            return;
        }

        // Đồng bộ theo trạng thái thật, kể cả khi phát hiện dữ liệu đã đổi.
        if (
            $pending['type'] === 'banner'
            && $result['status'] !== null
        ) {
            $this->dispatch(
                'banner-status-updated',
                id: (int) $pending['id'],
                status: $result['status']
            );
        }

        if (!$result['saved']) {
            $this->notify(
                'info',
                'Bản ghi đã thay đổi hoặc bị xóa. Vui lòng thao tác lại.'
            );

            return;
        }

        if ($pending['kind'] === 'delete') {
            $this->deleteManagedImage($result['path']);
        }

        $this->notify(
            'success',
            $pending['kind'] === 'delete'
            ? 'Đã xóa nội dung.'
            : (
                $pending['to'] === 'active'
                ? 'Đã bật hiển thị banner.'
                : 'Đã tạm ẩn banner.'
            )
        );
    }

    private function notify(string $icon, string $title): void
    {
        $this->dispatch(
            'swal',
            icon: $icon,
            title: $title
        );
    }

    private function resetForms(): void
    {
        $this->reset([
            'banner',
            'page',
            'farm',
            'bannerFile',
            'farmFile',
            'editingId',
            'editingVersion',
            'currentImagePath',
            'currentImageUrl',
            'originalPageHtml',
            'originalPageText',
            'pendingAction',
        ]);

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

    private function imagePreviewUrl(): ?string
    {
        $file = $this->selectedFile($this->modal ?? '');

        if ($file === null) {
            return $this->currentImageUrl;
        }

        try {
            $validator = Validator::make(
                ['file' => $file],
                [
                    'file' => [
                        'image',
                        'mimes:jpg,jpeg,png,webp',
                        'max:5120',
                    ],
                ]
            );

            return $validator->passes()
                ? $file->temporaryUrl()
                : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function validatedData(string $type): array
    {
        $imageRules = [
            $this->editingId === null || !$this->currentImagePath
            ? 'required'
            : 'nullable',

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
                    Rule::unique('pages', 'slug')->ignore($this->editingId),
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
            $text = $this->normalizeText($payload['content']);

            $payload['content'] = $this->editingId !== null
                && $text === $this->normalizeText($this->originalPageText)
                ? $this->originalPageHtml
                : nl2br(e($text));

            if (strlen($payload['content']) > 65535) {
                throw ValidationException::withMessages([
                    'page.content' => 'Nội dung quá dài sau khi định dạng.',
                ]);
            }
        }

        return $payload;
    }

    private function imageHasChanges(
        string $type,
        Model $record
    ): bool {
        $file = $this->selectedFile($type);

        if ($file === null) {
            return false;
        }

        $column = $type === 'banner' ? 'image' : 'image_url';

        $path = trim(
            (string) $record->getRawOriginal($column)
        );

        // URL ngoài: chọn file mới được hiểu là thay nguồn ảnh.
        if ($path === '' || preg_match('#\Ahttps?://#i', $path)) {
            return true;
        }

        try {
            $diskName = Str::startsWith($path, [
                'content/banners/',
                'content/farm-images/',
            ]) ? self::IMAGE_DISK : 'public';

            $disk = Storage::disk($diskName);

            if (!$disk->exists($path)) {
                return true;
            }

            $old = $disk->get($path);
            $new = $file->get();

            if (!is_string($old) || !is_string($new)) {
                throw new RuntimeException(
                    'Không đọc được ảnh để so sánh.'
                );
            }

            return !hash_equals(
                hash('sha256', $old),
                hash('sha256', $new)
            );
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                $type === 'banner' ? 'bannerFile' : 'farmFile'
                => 'Không thể so sánh ảnh hiện tại. Vui lòng thử lại.',
            ]);
        }
    }

    private function hasChanges(
        string $type,
        Model $record,
        array $data
    ): bool {
        $copy = clone $record;
        $copy->fill($data);

        return $copy->isDirty(array_keys($data))
            || (
                $type !== 'page'
                && $this->imageHasChanges($type, $record)
            );
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
                [
                    $type,
                    $this->editingId,
                    $this->editingVersion,
                    $data,
                    $checksum,
                ],
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

        // Validate trước khi mở SweetAlert xác nhận.
        $data = $this->validatedData($type);

        if ($this->editingId !== null) {
            $record = $this->getRecord(
                $type,
                $this->editingId
            );

            $this->ensureCurrent($record);

            if (!$this->hasChanges($type, $record, $data)) {
                $this->notify(
                    'info',
                    'Không có sự thay đổi nào.'
                );

                return;
            }
        }

        $this->confirmationHash = $this->signature($type, $data);
        $this->pendingType = $type;

        $this->dispatch(
            'confirm-content-save',
            type: $type,
            editing: $this->editingId !== null
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
                'Dữ liệu đã đổi. Vui lòng nhấn Lưu và xác nhận lại.'
            );

            return;
        }

        $this->cancelConfirmation();

        $editing = $this->editingId !== null;
        $newPath = null;
        $oldPath = null;
        $savedRecord = null;

        try {
            $changed = DB::transaction(function () use ($type, $data, $editing, &$newPath, &$oldPath, &$savedRecord) {
                if ($editing) {
                    $record = $this->getRecord(
                        $type,
                        $this->editingId,
                        true
                    );

                    $this->ensureCurrent($record);

                    if (!$this->hasChanges($type, $record, $data)) {
                        return false;
                    }
                } else {
                    $class = $this->modelClass($type);
                    $record = new $class();
                }

                $replaceImage = $type !== 'page'
                    && (
                        !$editing
                        || $this->imageHasChanges($type, $record)
                    );

                if ($replaceImage) {
                    $column = $type === 'banner'
                        ? 'image'
                        : 'image_url';

                    $oldPath = $editing
                        ? $record->getRawOriginal($column)
                        : null;

                    $data[$column] = $this->uploadImageToR2(
                        $type,
                        $newPath
                    );
                }

                $record->fill($data);

                if (!$record->saveOrFail()) {
                    throw new RuntimeException(
                        'Không lưu được bản ghi.'
                    );
                }

                $savedRecord = $record;

                return true;
            });
        } catch (ValidationException $exception) {
            $this->deleteManagedImage($newPath);

            throw $exception;
        } catch (UniqueConstraintViolationException $exception) {
            $this->deleteManagedImage($newPath);

            report($exception);

            $this->addError(
                $type === 'page' ? 'page.slug' : 'save',
                'Dữ liệu vừa bị trùng. Vui lòng kiểm tra lại.'
            );

            return;
        } catch (Throwable $exception) {
            $this->deleteManagedImage($newPath);

            report($exception);

            $this->addError(
                'save',
                'Không thể lưu nội dung. Vui lòng thử lại.'
            );

            return;
        }

        if (!$changed) {
            $this->notify('info', 'Không có sự thay đổi nào.');
            return;
        }

        // Database đã commit mới dọn ảnh cũ.
        $this->deleteManagedImage($oldPath);

        // Đồng bộ toggle cả khi trạng thái được sửa từ form.
        if ($type === 'banner') {
            $this->dispatch(
                'banner-status-updated',
                id: (int) $savedRecord->id,
                status: $savedRecord->status
            );
        }

        $this->modal = null;
        $this->resetForms();

        $this->notify(
            'success',
            $editing
            ? 'Cập nhật nội dung thành công.'
            : 'Thêm nội dung thành công.'
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
            'imagePreviewUrl' => $this->imagePreviewUrl(),
        ]);
    }
}