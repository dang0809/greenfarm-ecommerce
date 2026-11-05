<?php

namespace App\Livewire\FarmStaff;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
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
    $this->sku = 'PROD-'.Str::upper(Str::random(8));
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
        // Khi sửa tên, không tự thay slug đang được sử dụng.
        if ($this->editingId === null) {
            $this->slug = Str::slug($this->name);
        }
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

            'status' => [
                'required',
                Rule::in(['active', 'hidden']),
            ],

            'newImage' => [
                'bail',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
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

            'newImage.image' => 'Tệp được chọn không phải ảnh hợp lệ.',
            'newImage.mimes' => 'Chỉ chấp nhận JPG, JPEG, PNG hoặc WEBP.',
            'newImage.max' => 'Ảnh chính không được vượt quá 2 MB.',
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

    private function validatedData(): array
    {
        $this->name = trim($this->name);
        $this->sku = Str::upper(trim($this->sku));
        $this->slug = trim($this->slug);
        $this->unit = trim($this->unit);
        $this->description = trim($this->description);
        $this->sale_price = trim($this->sale_price);

        if ($this->editingId === null && $this->slug === '') {
            $this->slug = Str::slug($this->name);
        }

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
            'status' => $v['status'],
        ];
    }

    public function requestSaveProduct(): void
    {
        $this->staff();

        $data = $this->validatedData();

        if ($this->editingId !== null) {
            $product = Product::findOrFail($this->editingId);
            $product->fill($data);

            if (! $product->isDirty() && $this->newImage === null) {
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
            $saved = DB::transaction(function () use (
                $data,
                &$newPath,
                &$oldPath
            ) {
                $product = $this->editingId === null
                    ? new Product()
                    : Product::lockForUpdate()
                        ->findOrFail($this->editingId);

                $product->fill($data);

                if (
                    $product->exists
                    && ! $product->isDirty()
                    && $this->newImage === null
                ) {
                    return false;
                }

                if (! $product->save()) {
                    throw new RuntimeException(
                        'Không lưu được sản phẩm.'
                    );
                }

                if ($this->newImage !== null) {
                    $newPath = $this->newImage->store(
                        "products/{$product->id}",
                        'public'
                    );

                    if (! is_string($newPath) || $newPath === '') {
                        throw new RuntimeException(
                            'Không lưu được ảnh.'
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

                    if (! $cover->save()) {
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

        if (! $saved) {
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

    public function toggleStatus(int $id): void
    {
        $this->staff();

        try {
            DB::transaction(function () use ($id) {
                $product = Product::lockForUpdate()
                    ->findOrFail($id);

                $product->status = $product->status === 'active'
                    ? 'hidden'
                    : 'active';

                if (! $product->save()) {
                    throw new RuntimeException(
                        'Không lưu được trạng thái.'
                    );
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            $this->notify(
                'error',
                'Không thể cập nhật trạng thái.'
            );

            return;
        }

        $this->resetPage();

        $this->notify(
            'success',
            'Đã cập nhật trạng thái sản phẩm!'
        );
    }

    private function deleteManagedImage(?string $path): void
    {
        // Chỉ dọn file thuộc thư mục do chức năng này tạo.
        if (
            ! $path
            || ! preg_match('#^products/[0-9]+/[^/\\\\]+$#', $path)
            || str_contains($path, '..')
        ) {
            return;
        }

        try {
            if (
                ! ProductImage::where('image', $path)->exists()
                && ! Storage::disk('public')->delete($path)
            ) {
                throw new RuntimeException(
                    'Không dọn được ảnh.'
                );
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

        $products = Product::with([
                'category',
                'coverImage',
            ])
            ->withTotalStock()
            ->when(
                trim($this->search) !== '',
                function ($q) {
                    $term = '%'.trim($this->search).'%';

                    $q->where(fn ($q) =>
                        $q->where('name', 'like', $term)
                            ->orWhere('sku', 'like', $term)
                    );
                }
            )
            ->when(
                $this->selectedCategory !== '',
                fn ($q) => $q->where(
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
                fn ($q) => $q->where(
                    'status',
                    $this->selectedStatus
                )
            )
            ->latest('id')
            ->paginate(10);

        return view('livewire.farm-staff.product-manager', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}