<?php
namespace App\Livewire\FarmStaff;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
#[Title('Quản Lý Sản Phẩm Nông Sản | Nông Trại Đất Lành')]
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

    // Ảnh chính.
    public $newImage = null;
    public ?string $currentImageUrl = null;
    public int $imageInputKey = 0;

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
        'newImage' => 'ảnh chính',
        'min_growth_days' => 'số ngày thu hoạch',
    ];

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

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'selectedCategory',
            'selectedStatus',
        ]);

        $this->resetPage();
    }

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

        $product = Product::with('coverImage')->findOrFail($id);

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
        $this->currentImageUrl = $product->cover_image_url;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function updatedName(): void
    {
        $this->staff();
        $this->syncSlugWithName();
    }

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

    private function productImageRules(bool $required): array
    {
        return [
            'bail',
            $required ? 'required' : 'nullable',
            'file',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
        ];
    }

    private function validateProductImage(?Product $product): void
    {
        // Kiểm tra ảnh đang lưu trong database.
        $hasImage = $product !== null
            && $product->exists
            && $product->productImages()
                ->whereRaw("TRIM(image) <> ''")
                ->exists();

        $this->validateOnly('newImage', [
            'newImage' => $this->productImageRules(!$hasImage),
        ]);
    }

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
                'nullable',
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

            'newImage' => $this->productImageRules(false),
        ];
    }

    protected function messages(): array
    {
        return [
            '*.required' => 'Vui lòng nhập :attribute.',
            '*.string' => ':attribute phải là văn bản.',
            '*.numeric' => ':attribute phải là số.',
            '*.decimal' => ':attribute chỉ được có tối đa 2 chữ số thập phân.',
            '*.min' => ':attribute phải từ :min trở lên.',
            '*.max' => ':attribute vượt quá giới hạn :max.',

            'slug.regex' => 'Slug chỉ gồm chữ thường a-z, số và dấu gạch nối.',
            'slug.unique' => 'Slug đã tồn tại. Vui lòng thêm hậu tố khác.',
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

            'newImage.image' => 'Tệp được chọn không phải ảnh hợp lệ.',
            'newImage.mimes' => 'Chỉ chấp nhận JPG, JPEG, PNG hoặc WEBP.',
            'newImage.max' => 'Ảnh chính không được vượt quá 2 MB.',
            'newImage.required' => 'Vui lòng tải ảnh sản phẩm lên trước khi lưu.',
            'newImage.file' => 'Tệp tải lên không hợp lệ.',
        ];
    }

    public function updatedNewImage(): void
    {
        $this->staff();

        try {
            $this->validateOnly('newImage');
        } catch (ValidationException $exception) {
            $this->reset('newImage');
            $this->imageInputKey++;

            throw $exception;
        }
    }

    public function cancelNewImage(): void
    {
        $this->staff();

        $this->reset('newImage');
        $this->imageInputKey++;
        $this->resetValidation('newImage');
    }

    // Các trang khách hàng còn gọi hai API static này.
// Logic tính tồn kho được thực hiện bởi Product model.
    public static function withTotalStock(Builder $query): Builder
    {
        return $query->withTotalStock();
    }

    private static function getImageDisk(): string
    {
        return config('filesystems.disks.r2.key') ? 'r2' : 'public';
    }

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

    public function requestSaveProduct(): void
    {
        $this->staff();

        if (!$this->showModal) {
            return;
        }

        $this->resetValidation('form');
        $data = $this->validatedData();

        $product = $this->editingId !== null
            ? Product::findOrFail($this->editingId)
            : null;

        // Kiểm tra ảnh trước khi kiểm tra dữ liệu "không thay đổi".
        $this->validateProductImage($product);

        if ($product !== null) {
            $product->fill($data);

            if (!$product->isDirty() && $this->newImage === null) {
                $this->notify('info', 'Không có thay đổi nào.');

                return;
            }
        }

        $this->dispatch('confirm-product-save');
    }

    public function saveProduct(): void
    {
        $this->staff();

        // Kiểm tra lại khi người dùng nhấn Xác nhận.
        $data = $this->validatedData();

        $newPath = null;
        $oldPath = null;
        $wasEditing = $this->editingId !== null;

        try {
            $saved = DB::transaction(function () use ($data, &$newPath, &$oldPath) {
                $product = $this->editingId === null
                    ? new Product()
                    : Product::lockForUpdate()
                        ->findOrFail($this->editingId);

                $this->validateProductImage($product->exists ? $product : null);

                $product->fill($data);

                if (
                    $product->exists
                    && !$product->isDirty()
                    && $this->newImage === null
                ) {
                    return false;
                }

                if (!$product->save()) {
                    throw new RuntimeException(
                        'Không lưu được sản phẩm.'
                    );
                }

                if ($this->newImage !== null) {
                    $diskName = self::getImageDisk();
                    $newPath = $this->newImage->store(
                        "products/{$product->id}",
                        $diskName
                    );

                    if (
                        !is_string($newPath)
                        || $newPath === ''
                        || !Storage::disk($diskName)->exists($newPath)
                    ) {
                        throw new RuntimeException(
                            'Không lưu được ảnh sản phẩm.'
                        );
                    }

                    $cover = $product->coverImage()
                        ->lockForUpdate()
                        ->first();

                    $oldPath = $cover?->image;

                    $cover ??= $product->productImages()->make();

                    $cover->fill([
                        'image' => $newPath,
                        'is_primary' => true,
                        'sort_order' => 0,
                    ]);

                    if (!$cover->save()) {
                        throw new RuntimeException(
                            'Không lưu được dữ liệu ảnh.'
                        );
                    }

                    // Đảm bảo các ảnh khác không còn là ảnh chính.
                    $product->productImages()
                        ->where('id', '<>', $cover->id)
                        ->where('is_primary', true)
                        ->update(['is_primary' => false]);
                }

                return true;
            });
        } catch (ValidationException $exception) {
            $this->deleteManagedImage($newPath);
            throw $exception;
        } catch (UniqueConstraintViolationException $exception) {
            $this->deleteManagedImage($newPath);

            $this->addError(
                'form',
                'SKU hoặc slug vừa được sử dụng. Vui lòng kiểm tra lại.'
            );

            return;
        } catch (Throwable $exception) {
            $this->deleteManagedImage($newPath);

            report($exception);

            $this->addError(
                'form',
                'Không thể lưu sản phẩm. Vui lòng thử lại.'
            );

            return;
        }

        if (!$saved) {
            $this->notify('info', 'Không có thay đổi nào.');

            return;
        }

        // DB đã commit; chỉ dọn ảnh không còn được tham chiếu.
        $this->deleteManagedImage($oldPath);

        $this->closeModal();
        $this->resetPage();

        $this->notify(
            'success',
            $wasEditing
            ? 'Đã cập nhật sản phẩm!'
            : 'Đã thêm sản phẩm!'
        );
    }

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

    public function toggleStatus(
        int $id,
        string $expectedStatus,
        string $targetStatus
    ): void {
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
            'newImage',
            'currentImageUrl',
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