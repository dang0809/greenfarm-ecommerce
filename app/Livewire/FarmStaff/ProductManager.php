<?php
namespace App\Livewire\FarmStaff;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
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

    public const UNIT_OPTIONS = [
        'kg' => 'Kg',
        'bó 500g' => 'Bó 500g',
        'hộp 500g' => 'Hộp 500g',
    ];

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
    // Giá trị thực tế dùng để lưu vào products.unit.
    public string $unit = 'kg';

    // Giá trị lựa chọn trên select.
    public string $unitOption = 'kg';

    // Nội dung nhập khi chọn Khác.
    public string $customUnit = '';
    public string $weight_kg = '1';
    public string $description = '';

    public string $min_growth_days = '';
    public string $status = 'active';

    public bool $hasExistingBatchesOrOrders = false;

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

    public function updated(string $property): void
    {
        if (!$this->showModal) {
            return;
        }

        // Mỗi trường chỉ xóa lỗi của chính nó và các trường phụ thuộc.
        $errorFields = [
            'name' => ['name', 'slug'],
            'slug' => ['slug'],
            'sku' => ['sku'],
            'category_id' => ['category_id'],

            'price' => ['price', 'sale_price'],
            'sale_price' => ['sale_price'],

            'unitOption' => ['unitOption', 'customUnit'],
            'customUnit' => ['customUnit'],

            'weight_kg' => ['weight_kg'],
            'description' => ['description'],
            'min_growth_days' => ['min_growth_days'],
            'status' => ['status'],
        ];

        if (!array_key_exists($property, $errorFields)) {
            return;
        }

        $this->resetValidation($errorFields[$property]);
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

            $uploads = array_values($this->imageUploads);

            if (count($this->imageOrder) + count($uploads) > 10) {
                $this->addError(
                    'imageUploads',
                    'Mỗi sản phẩm chỉ được có tối đa 10 ảnh.'
                );

                return;
            }

            foreach ($uploads as $file) {
                $key = 'new:' . (string) Str::uuid();

                $this->newImages[$key] = $file;
                $this->imageOrder[] = $key;
            }

            $this->resetValidation('imageUploads');
        } finally {
            $this->reset('imageUploads');
            $this->imageInputKey++;
            $this->dispatch('product-images-ready');
        }
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

            'unitOption' => [
                'required',
                Rule::in([
                    ...array_keys(self::UNIT_OPTIONS),
                    'other',
                ]),
            ],

            'customUnit' => [
                'exclude_unless:unitOption,other',
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
            'category_id.required' => 'Vui lòng chọn danh mục sản phẩm.',

            'unitOption.required' => 'Vui lòng chọn đơn vị tính.',
            'unitOption.in' => 'Đơn vị tính được chọn không hợp lệ.',

            'customUnit.required' => 'Vui lòng nhập đơn vị tính khác.',
            'customUnit.string' => 'Đơn vị tính phải là văn bản.',
            'customUnit.max' => 'Đơn vị tính tối đa 50 ký tự.',

            'weight_kg.required' => 'Vui lòng nhập khối lượng tịnh (kg).',
            'weight_kg.numeric' => 'Khối lượng tịnh phải là số.',
            'weight_kg.min' => 'Khối lượng tịnh tối thiểu là 0.01 kg.',
            'weight_kg.max' => 'Khối lượng tịnh tối đa là 999.99 kg.',

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
        'unitOption' => 'đơn vị tính',
        'customUnit' => 'đơn vị tính khác',
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
        $this->customUnit = trim($this->customUnit);
        $this->description = trim($this->description);
        $this->sale_price = trim($this->sale_price);
        $this->min_growth_days = trim($this->min_growth_days);

        $this->syncSlugWithName();

        $v = $this->validate();

        // Chọn Khác: lấy nội dung nhập.
        // Chọn đơn vị có sẵn: lấy giá trị của option.
        $this->unit = $v['unitOption'] === 'other'
            ? $v['customUnit']
            : $v['unitOption'];

        // Nếu sản phẩm đã phát sinh lô hàng hoặc đơn hàng thì giữ nguyên đơn vị tính cũ
        if ($this->hasExistingBatchesOrOrders && $this->editingId !== null) {
            $existing = Product::findOrFail($this->editingId);
            $this->unit = (string) $existing->unit;
        }

        // Chỉ trả về các trường thực tế được lưu vào sản phẩm.
        return [
            'name' => $v['name'],
            'slug' => $v['slug'],
            'sku' => $v['sku'] === '' ? null : $v['sku'],

            'category_id' => (int) $v['category_id'],

            'price' => $v['price'],

            'unit' => $this->unit,

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

        if (!Category::query()->exists()) {
            $this->notify(
                'error',
                'Chưa có danh mục. Vui lòng tạo danh mục trước.'
            );

            return;
        }

        // Để trống cho người dùng tự chọn.
        $this->category_id = '';

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

        // $this->unit đã được nạp từ sản phẩm trong foreach phía trên.
        $this->unitOption = array_key_exists($this->unit, self::UNIT_OPTIONS)
            ? $this->unit
            : 'other';

        $this->customUnit = $this->unitOption === 'other'
            ? $this->unit
            : '';

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

        $this->hasExistingBatchesOrOrders = $product->batches()->exists()
            || $product->orderItems()->exists();

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

                // Tự động tính toán và đồng bộ lại giá khuyến mãi theo lô Flash Sale hiện tại (FEFO)
                ProductBatch::syncProductSalePrice($product->id);

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
            'unitOption',
            'customUnit',
            'weight_kg',
            'description',
            'min_growth_days',
            'status',
            'imageUploads',
            'imageOrder',
            'existingImages',
            'newImages',
            'hasExistingBatchesOrOrders',
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