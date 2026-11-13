<?php
namespace App\Livewire\FarmStaff;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
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
use Livewire\WithPagination;

use RuntimeException;
use Throwable;

#[Layout('layouts.farm-staff.app')]
#[Title('Quản Lý Sản Phẩm Nông Sản')]
class ProductManager extends Component
{
    use WithFileUploads, WithPagination;

    // Bộ lọc.
    public string $search = '';
    public string $selectedCategory = '';
    public string $selectedStatus = '';

    // Modal.
    public bool $showModal = false;

    #[Locked]
    public ?int $editingId = null;

    // Form sản phẩm.
    public string $name = '';
    public string $slug = '';
    public string $sku = '';
    public string $category_id = '';
    public string $price = '';
    public string $sale_price = '';
    public string $unit = 'kg';
    public string $weight_kg = '1';
    public string $description = '';

    public string $min_growth_days = '';
    public string $status = 'active';

    public array $imageUploads = [];

    /**
     * Danh sách token theo thứ tự hiển thị:
     * old:12 hoặc new:uuid
     */
    #[Locked]
    public array $imageOrder = [];

    /**
     * Ảnh cũ của sản phẩm đang sửa.
     */
    #[Locked]
    public array $existingImages = [];

    /**
     * Ảnh mới đã upload, key là UUID.
     *
     * @var array<string, TemporaryUploadedFile>
     */
    public array $newImages = [];

    public int $imageInputKey = 0;

    public function mount(): void
    {
        $this->staff();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedName(): void
    {
        $this->staff();
        $this->syncSlugWithName();
    }

    public function updatedImageUploads(): void
    {
        $this->staff();

        try {
            $this->validate([
                'imageUploads' => ['array', 'max:10'],
                'imageUploads.*' => $this->imageRules(),
            ], [
                'imageUploads.max' => 'Mỗi lần chỉ được chọn tối đa 10 ảnh.',
                'imageUploads.*.image' => 'Tệp được chọn không phải ảnh hợp lệ.',
                'imageUploads.*.mimes' => 'Chỉ chấp nhận JPG, JPEG, PNG hoặc WEBP.',
                'imageUploads.*.max' => 'Mỗi ảnh không được vượt quá 2 MB.',
            ]);
        } catch (ValidationException $exception) {
            $this->reset('imageUploads');
            $this->imageInputKey++;

            throw $exception;
        }

        $uploads = array_values($this->imageUploads);

        if (count($this->imageOrder) + count($uploads) > 10) {
            $this->addError(
                'imageUploads',
                'Mỗi sản phẩm chỉ được có tối đa 10 ảnh.'
            );

            $this->reset('imageUploads');
            $this->imageInputKey++;

            return;
        }

        foreach ($uploads as $file) {
            $key = 'new:' . (string) Str::uuid();

            $this->newImages[$key] = $file;
            $this->imageOrder[] = $key;
        }

        $this->reset('imageUploads');
        $this->imageInputKey++;
        $this->resetValidation('imageUploads');

        $this->dispatch('product-images-ready');
    }

    // Validate dữ liệu
    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')
                    ->ignore($this->editingId),
            ],

            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')
                    ->ignore($this->editingId),
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'price' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:9999999999.99',
            ],

            'sale_price' => [
                'nullable',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:9999999999.99',
                'lt:price',
            ],

            'unit' => [
                'required',
                'string',
                'max:50',
            ],

            'weight_kg' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:999999.99',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'min_growth_days' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:10000',
            ],

            'status' => [
                'required',
                Rule::in(['active', 'hidden']),
            ],

            'imageOrder' => [
                'required',
                'array',
                'min:1',
                'max:10',
            ],

            'imageUploads' => [
                'array',
                'max:10',
            ],

            'imageUploads.*' => $this->imageRules(),
        ];
    }

    protected function messages(): array
    {
        return [
            'imageOrder.required' => 'Vui lòng chọn ít nhất một ảnh sản phẩm.',
            'imageOrder.min' => 'Vui lòng chọn ít nhất một ảnh sản phẩm.',
            'imageOrder.max' => 'Mỗi sản phẩm chỉ được có tối đa 10 ảnh.',

            '*.required' => 'Vui lòng nhập :attribute.',
            '*.string' => ':attribute phải là văn bản.',
            '*.numeric' => ':attribute phải là số.',
            '*.decimal' => ':attribute chỉ được có tối đa 2 chữ số thập phân.',
            '*.min' => ':attribute phải từ :min trở lên.',
            '*.max' => ':attribute vượt quá giới hạn :max.',

            'slug.regex' => 'Slug chỉ gồm chữ thường a-z, số và dấu gạch nối.',
            'slug.unique' => 'Slug đã tồn tại. Vui lòng thêm hậu tố khác.',
            'sku.required' => 'Vui lòng nhập mã SKU.',
            'sku.unique' => 'SKU đã tồn tại.',

            'category_id.integer' => 'Danh mục không hợp lệ.',
            'category_id.exists' => 'Danh mục không còn tồn tại.',

            'sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            'status.in' => 'Trạng thái không hợp lệ.',

            'min_growth_days.required' =>
                'Vui lòng nhập số ngày thu hoạch tối thiểu.',

            'min_growth_days.integer' =>
                'Số ngày thu hoạch phải là số nguyên.',

            'min_growth_days.min' =>
                'Số ngày thu hoạch tối thiểu là 1 ngày.',

            'min_growth_days.max' =>
                'Số ngày thu hoạch tối đa là 10.000 ngày.',

            'imageOrder.array' =>
                'Danh sách thứ tự ảnh không hợp lệ.',

            'imageUploads.array' =>
                'Danh sách ảnh không hợp lệ.',

            'imageUploads.max' =>
                'Mỗi sản phẩm chỉ được có tối đa 10 ảnh.',

            'imageUploads.*.image' =>
                'Tệp được chọn không phải ảnh hợp lệ.',

            'imageUploads.*.mimes' =>
                'Chỉ chấp nhận JPG, JPEG, PNG hoặc WEBP.',

            'imageUploads.*.max' =>
                'Mỗi ảnh không được vượt quá 2 MB.',

            'imageUploads.*.file' =>
                'Tệp tải lên không hợp lệ.',
        ];
    }

    private function imageRules(): array
    {
        return [
            'bail',
            'file',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
        ];
    }

    protected $validationAttributes = [
        'name' => 'tên sản phẩm',
        'slug' => 'slug',
        'sku' => 'SKU',
        'category_id' => 'danh mục',
        'price' => 'giá gốc',
        'sale_price' => 'giá khuyến mãi',
        'unit' => 'đơn vị',
        'weight_kg' => 'khối lượng',
        'description' => 'mô tả',
        'status' => 'trạng thái',
        'min_growth_days' => 'số ngày thu hoạch',
        'imageUploads' => 'ảnh sản phẩm',
        'imageOrder' => 'thứ tự ảnh sản phẩm',
    ];

    private function validatedData(): array
    {
        $this->name = trim($this->name);
        $this->sku = Str::upper(trim($this->sku));
        $this->slug = trim($this->slug);
        $this->unit = trim($this->unit);
        $this->description = trim($this->description);
        $this->sale_price = trim($this->sale_price);
        $this->min_growth_days = trim($this->min_growth_days);
        $this->syncSlugWithName();

        $v = $this->validate();

        // Whitelist: không nhận tồn kho, sold_count hoặc avg_rating từ form.
        return [
            'name' => $v['name'],
            'slug' => $v['slug'],
            'sku' => $v['sku'] === '' ? null : $v['sku'],
            'category_id' => (int) $v['category_id'],
            'price' => $v['price'],
            'sale_price' => $v['sale_price'] === ''
                ? null
                : $v['sale_price'],
            'unit' => $v['unit'],
            'weight_kg' => $v['weight_kg'],
            'description' => $v['description'] === ''
                ? null
                : $v['description'],
            'min_growth_days' => (int) $v['min_growth_days'],
            'status' => $v['status'],
        ];
    }

    private function validateImageOrder(?Product $product = null, ?array $currentImageIds = null): void
    {
        $count = count($this->imageOrder);

        if ($count < 1) {
            throw ValidationException::withMessages([
                'imageOrder' => 'Vui lòng chọn ít nhất một ảnh sản phẩm.',
            ]);
        }

        if ($count > 10) {
            throw ValidationException::withMessages([
                'imageOrder' => 'Mỗi sản phẩm chỉ được có tối đa 10 ảnh.',
            ]);
        }

        $currentImageIds ??= $product
            ? $product->productImages()->pluck('id')->map(fn($id) => (int) $id)->all()
            : [];

        $seenOldIds = [];
        $seenNewKeys = [];

        foreach ($this->imageOrder as $token) {
            if (str_starts_with($token, 'old:')) {
                $id = (int) substr($token, 4);

                if (
                    $id <= 0
                    || !in_array($id, $currentImageIds, true)
                    || in_array($id, $seenOldIds, true)
                ) {
                    throw ValidationException::withMessages([
                        'imageOrder' => 'Thứ tự ảnh cũ không hợp lệ. Vui lòng tải lại sản phẩm.',
                    ]);
                }

                $seenOldIds[] = $id;

                continue;
            }

            if (str_starts_with($token, 'new:')) {
                if (
                    !isset($this->newImages[$token])
                    || !($this->newImages[$token] instanceof TemporaryUploadedFile)
                    || in_array($token, $seenNewKeys, true)
                ) {
                    throw ValidationException::withMessages([
                        'imageOrder' => 'Có ảnh mới không hợp lệ trong danh sách.',
                    ]);
                }

                Validator::make(
                    ['image' => $this->newImages[$token]],
                    ['image' => $this->imageRules()]
                )->validate();

                $seenNewKeys[] = $token;

                continue;
            }

            throw ValidationException::withMessages([
                'imageOrder' => 'Danh sách thứ tự ảnh không hợp lệ.',
            ]);
        }

        $newKeysInOrder = collect($this->imageOrder)
            ->filter(fn(string $token) => str_starts_with($token, 'new:'))
            ->values()
            ->all();

        if (array_diff(array_keys($this->newImages), $newKeysInOrder)) {
            throw ValidationException::withMessages([
                'imageOrder' => 'Danh sách ảnh mới chưa được sắp xếp hợp lệ.',
            ]);
        }
    }

    // Bộ lọc danh mục
    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'selectedCategory',
            'selectedStatus',
        ]);

        $this->resetPage();
    }

    // Modal form
    public function createProduct(): void
    {
        $this->staff();
        $this->resetForm();

        // Cùng thứ tự với danh sách danh mục trong render().
        $categoryId = Category::orderBy('name')->value('id');

        if ($categoryId === null) {
            $this->notify(
                'error',
                'Chưa có danh mục. Vui lòng tạo danh mục trước.'
            );

            return;
        }

        $this->category_id = (string) $categoryId;
        $this->sku = 'PROD-' . Str::upper(Str::random(8));
        $this->showModal = true;
    }

    public function editProduct(int $id): void
    {
        $this->staff();

        $product = Product::with([
            'productImages' => fn($query) => $query
                ->orderBy('sort_order')
                ->orderBy('id'),
        ])->findOrFail($id);

        $this->resetForm();

        $this->editingId = $product->id;

        foreach ([
            'name',
            'slug',
            'sku',
            'price',
            'sale_price',
            'unit',
            'weight_kg',
            'description',
            'min_growth_days',
            'status',
        ] as $field) {
            $this->{$field} = (string) ($product->{$field} ?? '');
        }

        $this->category_id = (string) $product->category_id;

        // Chuẩn bị URL xem trước cho các ảnh đã lưu.
        $this->existingImages = $product->productImages
            ->mapWithKeys(fn(ProductImage $image) => [
                (string) $image->id => [
                    'url' => $image->url,
                ],
            ])
            ->all();

        $this->imageOrder = $product->productImages
            ->map(fn(ProductImage $image) => 'old:' . $image->id)
            ->values()
            ->all();

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    // Quản lý ảnh
    public function moveImage(int $index, int $direction): void
    {
        $this->staff();

        if (!in_array($direction, [-1, 1], true)) {
            return;
        }

        $targetIndex = $index + $direction;

        if (
            !isset($this->imageOrder[$index])
            || !isset($this->imageOrder[$targetIndex])
        ) {
            return;
        }

        [$this->imageOrder[$index], $this->imageOrder[$targetIndex]] = [
            $this->imageOrder[$targetIndex],
            $this->imageOrder[$index],
        ];
    }

    public function removeImage(int $index): void
    {
        $this->staff();

        $token = $this->imageOrder[$index] ?? null;

        if ($token === null) {
            return;
        }

        if (str_starts_with($token, 'new:')) {
            unset($this->newImages[$token]);
        }

        unset($this->imageOrder[$index]);
        $this->imageOrder = array_values($this->imageOrder);
    }

    // Lưu sản phẩm
    public function requestSaveProduct(): void
    {
        $this->staff();

        if (!$this->showModal) {
            return;
        }

        $this->resetValidation('form');

        $data = $this->validatedData();

        $product = $this->editingId !== null
            ? Product::with('productImages')->findOrFail($this->editingId)
            : null;

        $this->validateImageOrder($product);

        if ($product !== null) {
            // Bắt buộc fill dữ liệu form trước khi kiểm tra isDirty().
            $product->fill($data);

            if (
                !$product->isDirty()
                && $this->galleryHasNoChanges($product)
            ) {
                $this->notify('info', 'Không có thay đổi nào.');

                return;
            }
        }

        $this->dispatch('confirm-product-save')->self();
    }

    public function saveProduct(): void
    {
        $this->staff();

        // Kiểm tra lại khi người dùng nhấn Xác nhận.
        $data = $this->validatedData();

        $newPaths = [];
        $removedPaths = [];
        $wasEditing = $this->editingId !== null;

        try {
            DB::transaction(function () use (&$newPaths, &$removedPaths, $data) {
                $product = $this->editingId === null
                    ? new Product()
                    : Product::query()
                        ->lockForUpdate()
                        ->findOrFail($this->editingId);

                $currentImages = $product->productImages()
                    ->lockForUpdate()
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();

                $this->validateImageOrder(
                    $product->exists ? $product : null,
                    $currentImages->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->all()
                );

                $product->fill($data);

                if (!$product->save()) {
                    throw new RuntimeException('Không lưu được sản phẩm.');
                }

                $oldIdsInOrder = collect($this->imageOrder)
                    ->filter(fn(string $token) => str_starts_with($token, 'old:'))
                    ->map(fn(string $token) => (int) substr($token, 4))
                    ->values()
                    ->all();

                $currentById = $currentImages->keyBy('id');

                foreach ($currentImages as $currentImage) {
                    if (!in_array($currentImage->id, $oldIdsInOrder, true)) {
                        $removedPaths[] = $currentImage->image;
                        $currentImage->delete();
                    }
                }

                foreach ($this->imageOrder as $index => $token) {
                    $sortOrder = $index + 1;
                    $isPrimary = $index === 0;

                    if (str_starts_with($token, 'old:')) {
                        $imageId = (int) substr($token, 4);
                        $image = $currentById->get($imageId);

                        if (!$image) {
                            throw new RuntimeException(
                                'Không tìm thấy ảnh cũ của sản phẩm.'
                            );
                        }

                        $image->update([
                            'sort_order' => $sortOrder,
                            'is_primary' => $isPrimary,
                        ]);

                        continue;
                    }

                    $file = $this->newImages[$token] ?? null;

                    if (!$file instanceof TemporaryUploadedFile) {
                        throw new RuntimeException('Ảnh mới không hợp lệ.');
                    }

                    $path = $file->store(
                        "products/{$product->id}",
                        self::getImageDisk()
                    );

                    if (
                        !is_string($path)
                        || $path === ''
                        || !Storage::disk(self::getImageDisk())->exists($path)
                    ) {
                        throw new RuntimeException('Không lưu được ảnh sản phẩm.');
                    }

                    $newPaths[] = $path;

                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,
                        'sort_order' => $sortOrder,
                        'is_primary' => $isPrimary,
                    ]);
                }

                return true;
            });
        } catch (ValidationException $exception) {
            foreach ($newPaths as $path) {
                $this->deleteManagedImage($path);
            }

            throw $exception;
        } catch (Throwable $exception) {
            foreach ($newPaths as $path) {
                $this->deleteManagedImage($path);
            }

            report($exception);

            $this->addError(
                'form',
                'Không thể lưu sản phẩm và ảnh. Vui lòng thử lại.'
            );

            return;
        }

        foreach ($removedPaths as $path) {
            $this->deleteManagedImage($path);
        }

        $this->closeModal();
        $this->resetPage();

        $this->notify(
            'success',
            $wasEditing
            ? 'Đã cập nhật sản phẩm và thứ tự ảnh!'
            : 'Đã thêm sản phẩm cùng gallery ảnh!'
        );
    }

    // Nút trạng thái sản phẩm
    public function requestToggleStatus(int $id): void
    {
        $this->staff();
        $this->resetValidation();

        $product = Product::with('category')->findOrFail($id);

        $from = (string) $product->status;
        $to = $from === 'active' ? 'hidden' : 'active';

        if (
            $to === 'active'
            && !$product->category?->is_active
        ) {
            $this->notify(
                'info',
                'Hãy hiển thị danh mục trước khi bật sản phẩm.'
            );

            return;
        }

        // Chưa cập nhật database.
        $this->dispatch(
            'confirm-product-status',
            id: $product->id,
            name: $product->name,
            expectedStatus: $from,
            targetStatus: $to
        )->self();
    }

    public function toggleStatus(int $id, string $expectedStatus, string $targetStatus): void
    {
        $this->staff();

        abort_unless(
            in_array($expectedStatus, ['active', 'hidden'], true)
            && in_array($targetStatus, ['active', 'hidden'], true)
            && $expectedStatus !== $targetStatus,
            422
        );

        try {
            $result = DB::transaction(function () use ($id, $expectedStatus, $targetStatus) {
                $snapshot = Product::findOrFail($id);

                // Khóa danh mục trước sản phẩm.
                $category = Category::query()
                    ->lockForUpdate()
                    ->findOrFail($snapshot->category_id);

                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($id);

                if (
                    (int) $product->category_id
                    !== (int) $category->id
                ) {
                    return 'stale';
                }

                if (
                    $targetStatus === 'active'
                    && !$category->is_active
                ) {
                    return 'category_hidden';
                }

                // Request lặp không đảo trạng thái lần nữa.
                if ($product->status === $targetStatus) {
                    return 'unchanged';
                }

                if ($product->status !== $expectedStatus) {
                    return 'stale';
                }

                $product->status = $targetStatus;

                if (!$product->save()) {
                    throw new RuntimeException(
                        'Không lưu được trạng thái.'
                    );
                }

                return 'saved';
            });
        } catch (Throwable $exception) {
            report($exception);

            $this->notify(
                'error',
                'Không thể cập nhật trạng thái. Vui lòng tải lại trang.'
            );

            return;
        }

        if ($result === 'category_hidden') {
            $this->notify(
                'info',
                'Hãy hiển thị danh mục trước khi bật sản phẩm.'
            );

            return;
        }

        if ($result === 'stale') {
            $this->notify(
                'info',
                'Sản phẩm đã thay đổi. Vui lòng tải lại rồi thử lại.'
            );

            return;
        }

        // cập nhật hiệu ứng toggle.
        $this->dispatch(
            'product-status-updated',
            id: $id,
            status: $targetStatus
        );

        $this->notify(
            $result === 'saved' ? 'success' : 'info',
            $result === 'saved'
            ? (
                $targetStatus === 'active'
                ? 'Đã bật hiển thị sản phẩm!'
                : 'Đã tắt hiển thị sản phẩm!'
            )
            : 'Sản phẩm đã ở trạng thái này.'
        );
    }

    // Tự sinh slug khi đang nhập
    private function syncSlugWithName(): void
    {
        $name = trim($this->name);

        // Thêm mới: sinh slug từ tên đang nhập.
        if ($this->editingId === null) {
            $this->slug = Str::slug($name);

            return;
        }

        // Chỉnh sửa: đối chiếu với tên và slug đang lưu trong DB.
        $product = Product::findOrFail($this->editingId);

        $this->slug = $name === trim((string) $product->name)
            ? (string) $product->slug
            : Str::slug($name);
    }

    private function galleryHasNoChanges(Product $product): bool
    {
        $currentOrder = $product->productImages
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->map(fn(ProductImage $image) => 'old:' . $image->id)
            ->values()
            ->all();

        return $currentOrder === $this->imageOrder
            && $this->newImages === [];
    }

    // Lưu trữ ảnh
    private static function getImageDisk(): string
    {
        return config('filesystems.disks.r2.key') ? 'r2' : 'public';
    }

    private function deleteManagedImage(?string $path): void
    {
        // Chỉ dọn file thuộc thư mục do chức năng này tạo.
        if (
            !$path
            || !preg_match('#^products/[0-9]+/[^/\\\\]+$#', $path)
            || str_contains($path, '..')
        ) {
            return;
        }

        try {
            if (ProductImage::where('image', $path)->exists()) {
                return;
            }

            $diskName = self::getImageDisk();
            if (!Storage::disk($diskName)->delete($path)) {
                throw new RuntimeException(
                    'Không dọn được ảnh.'
                );
            }

            if ($diskName !== 'public' && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'slug',
            'sku',
            'category_id',
            'price',
            'sale_price',
            'unit',
            'weight_kg',
            'description',
            'min_growth_days',
            'status',
            'imageUploads',
            'imageOrder',
            'existingImages',
            'newImages',
        ]);

        $this->imageInputKey++;
        $this->resetValidation();
    }

    private function staff(): User
    {
        $user = Auth::id()
            ? User::find(Auth::id())
            : null;

        abort_unless(
            $user
            && $user->role === 'farm_staff'
            && $user->status === 'active',
            403,
            'Bạn không có quyền truy cập chức năng này.'
        );

        return $user;
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
        $this->staff();

        $products = Product::with(['category', 'coverImage'])
            ->withTotalStock()
            ->search($this->search)

            ->when(
                $this->selectedCategory !== '',
                fn($query) => $query->where(
                    'category_id',
                    $this->selectedCategory
                )
            )
            ->when(
                in_array(
                    $this->selectedStatus,
                    ['active', 'hidden'],
                    true
                ),
                fn($query) => $query->where(
                    'status',
                    $this->selectedStatus
                )
            )
            ->latest('id')
            ->paginate(10);

        $productImageUrls = $products
            ->getCollection()
            ->mapWithKeys(fn(Product $product) => [
                $product->id => $product->cover_image_url,
            ])
            ->all();

        $productStocks = $products
            ->getCollection()
            ->mapWithKeys(fn(Product $product) => [
                $product->id => (int) ($product->total_stock ?? 0),
            ])
            ->all();

        return view('livewire.farm-staff.product-manager', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'productImageUrls' => $productImageUrls,
            'productStocks' => $productStocks,
        ]);
    }
}