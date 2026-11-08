<?php

namespace App\Livewire\FarmStaff;

use App\Models\Category;
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
#[Title('Quản Lý Danh Mục Nông Sản | Nông Trại Đất Lành')]
class CategoryManager extends Component
{
    use WithFileUploads, WithPagination;

    // Tìm kiếm.
    public string $search = '';

    // Trạng thái modal.
    public bool $showModal = false;

    #[Locked]
    public ?int $editingId = null;

    // Dữ liệu form.
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    // Chỉ dùng trong form; khi lưu sẽ chuyển sang is_active.
    public string $status = 'active';

    public $newImage = null;

    #[Locked]
    public ?string $currentImageUrl = null;

    // Đổi key để reset ô chọn file khi mở/đóng form.
    public int $imageInputKey = 0;

    public function boot(): void
    {
        // boot() chạy ở lần tải đầu và các request Livewire tiếp theo.
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
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    private function categoryImageUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : Storage::disk('public')->url(ltrim($path, '/'));
    }

    public function createCategory(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editCategory(int $id): void
    {
        $category = Category::findOrFail($id);

        $this->resetForm();

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = (string) ($category->description ?? '');
        $this->status = $category->is_active
            ? 'active'
            : 'hidden';

        $this->currentImageUrl = $this->categoryImageUrl($category->image);
        $this->showModal = true;
    }


    private function validatedData(): array
    {
        $this->resetValidation('form');

        $this->name = trim($this->name);
        $this->slug = trim($this->slug);
        $this->description = trim($this->description);

        if ($this->slug === '') {
            $this->slug = Str::slug($this->name);
        }

        $validated = $this->validate();

        return [
            'name' => $validated['name'],
            'slug' => $validated['slug'],

            'description' => $validated['description'] === ''
                ? null
                : $validated['description'],

            'is_active' => $validated['status'] === 'active',
        ];
    }

    public function requestSaveCategory(): void
    {
        // Chỉ kiểm tra, chưa ghi dữ liệu hoặc lưu ảnh.
        $this->validatedData();

        $this->dispatch(
            'confirm-category-save',
            editing: $this->editingId !== null
        )->self();
    }

    public function requestDeleteCategory(int $id): void
    {
        $category = Category::find($id);

        if (! $category) {
            $this->resetPage();
            $this->notify('error', 'Danh mục không còn tồn tại.');

            return;
        }

        // Chưa xóa ở bước này.
        $this->dispatch(
            'confirm-category-delete',
            id: $category->id,
            name: $category->name
        )->self();
    }
    
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function updatedName(): void
    {
        // Tự động cập nhật slug theo tên ở form thêm và sửa.
        $this->slug = Str::slug(trim($this->name));

        // Xóa lỗi của slug trước đó khi tên đã thay đổi.
        $this->resetValidation('slug');
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

                Rule::unique('categories', 'slug')
                    ->ignore($this->editingId),
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
            'description' => [
            'nullable',
            'string',
            'max:10000',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.max' => 'Tên danh mục tối đa 255 ký tự.',

            'slug.required' => 'Vui lòng nhập slug hợp lệ.',
            'slug.max' => 'Slug tối đa 255 ký tự.',
            'slug.regex' => 'Slug chỉ gồm chữ thường a-z, số và dấu gạch nối.',
            'slug.unique' => 'Slug đã tồn tại. Vui lòng chọn slug khác.',

            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',

            'newImage.image' => 'Tệp được chọn không phải ảnh hợp lệ.',
            'newImage.mimes' => 'Chỉ chấp nhận ảnh JPG, JPEG, PNG hoặc WEBP.',
            'newImage.max' => 'Ảnh không được vượt quá 2 MB.',
            'description.string' => 'Mô tả phải là văn bản.',
            'description.max' => 'Mô tả tối đa 10.000 ký tự.',
        ];
    }

    public function updatedNewImage(): void
    {
        try {
            $this->validateOnly('newImage');
        } catch (ValidationException $exception) {
            // Không để Blade tạo temporaryUrl() cho file không hợp lệ.
            $this->reset('newImage');
            $this->imageInputKey++;

            throw $exception;
        }
    }

    public function saveCategory(): void
    {
        $data = $this->validatedData();

        $wasEditing = $this->editingId !== null;

        $newPath = null;
        $oldPath = null;

        try {
            DB::transaction(function () use (
                $data,
                &$newPath,
                &$oldPath
            ) {
                $category = $this->editingId === null
                    ? new Category()
                    : Category::query()
                        ->lockForUpdate()
                        ->findOrFail($this->editingId);

                $category->fill($data);

                if ($this->newImage !== null) {
                    $path = $this->newImage->store(
                        'categories',
                        'public'
                    );

                    if (! is_string($path) || $path === '') {
                        throw new RuntimeException(
                            'Không lưu được ảnh danh mục.'
                        );
                    }

                    $newPath = $path;
                    $oldPath = $category->image;

                    $category->image = $newPath;
                }

                if (! $category->save()) {
                    throw new RuntimeException(
                        'Không lưu được danh mục.'
                    );
                }

                // Khi danh mục ẩn thì sản phẩm cũng ẩn theo
                if (! $category->is_active) {
                    $category->products()
                        ->where('status', '<>', 'hidden')
                        ->update([
                            'status' => 'hidden',
                        ]);
                }
            });
        } catch (UniqueConstraintViolationException $exception) {
            $this->deleteManagedImage($newPath);

            $this->addError(
                'slug',
                'Slug vừa được sử dụng. Vui lòng chọn slug khác.'
            );

            return;
        } catch (Throwable $exception) {
            $this->deleteManagedImage($newPath);

            report($exception);

            $this->addError(
                'form',
                'Không thể lưu danh mục. Vui lòng thử lại.'
            );

            return;
        }

        // DB đã commit, lúc này mới dọn ảnh bị thay thế.
        $this->deleteManagedImage($oldPath);

        $this->closeModal();
        $this->resetPage();

        $this->notify(
            'success',
            $wasEditing
                ? 'Đã cập nhật danh mục!'
                : 'Đã thêm danh mục!'
        );
    }

    public function deleteCategory(int $id): void
    {
        $oldPath = null;

        try {
            $deleted = DB::transaction(function () use (
                $id,
                &$oldPath
            ) {
                $category = Category::query()
                    ->lockForUpdate()
                    ->findOrFail($id);

                // Kiểm tra mọi sản phẩm, kể cả sản phẩm đang ẩn.
                if ($category->products()->exists()) {
                    return false;
                }

                $oldPath = $category->image;

                if (! $category->delete()) {
                    throw new RuntimeException(
                        'Không xóa được danh mục.'
                    );
                }

                return true;
            });
        } catch (Throwable $exception) {
            // FK restrictOnDelete vẫn bảo vệ dữ liệu ở mức CSDL.
            report($exception);

            $this->notify(
                'error',
                'Không thể xóa danh mục. Vui lòng tải lại và kiểm tra sản phẩm liên quan.'
            );

            return;
        }

        if (! $deleted) {
            $this->notify(
                'error',
                'Không thể xóa danh mục đang chứa sản phẩm, kể cả sản phẩm đang ẩn.'
            );

            return;
        }

        $this->deleteManagedImage($oldPath);

        if ($this->editingId === $id) {
            $this->closeModal();
        }

        // Tránh ở lại trang rỗng sau khi xóa bản ghi cuối.
        $this->resetPage();

        $this->notify('success', 'Đã xóa danh mục!');
    }

    private function deleteManagedImage(?string $path): void
    {
        // Chỉ dọn ảnh thuộc định dạng đường dẫn do chức năng này tạo.
        // URL ngoài và đường dẫn thư mục khác sẽ được bỏ qua.
        if (
            ! $path
            || ! preg_match(
                '#\Acategories/[A-Za-z0-9_-]+\.(?:jpg|jpeg|png|webp)\z#i',
                $path
            )
        ) {
            return;
        }

        try {
            // Không xóa nếu vẫn còn danh mục tham chiếu ảnh.
            if (Category::where('image', $path)->exists()) {
                return;
            }

            if (! Storage::disk('public')->delete($path)) {
                throw new RuntimeException(
                    'Không dọn được ảnh danh mục.'
                );
            }
        } catch (Throwable $exception) {
            // Lỗi dọn file không làm đảo ngược thao tác DB đã thành công.
            report($exception);
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'name',
            'slug',
            'description',
            'status',
            'newImage',
            'currentImageUrl',
        ]);

        $this->imageInputKey++;
        $this->resetValidation();
    }

    private function notify(string $icon, string $title): void
    {
        // Layout Farm Staff đã có listener cho sự kiện này.
        $this->dispatch(
            'swal',
            icon: $icon,
            title: $title
        );
    }

    public function render()
    {
        $search = trim($this->search);

        $categories = Category::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where(
                        'name',
                        'like',
                        '%'.$search.'%'
                    )->orWhere(
                        'slug',
                        'like',
                        '%'.$search.'%'
                    );
                });
            })
            ->orderByDesc('id')
            ->paginate(10);

        // Chuẩn bị URL riêng, giữ nguyên đối tượng phân trang.
        $categoryImageUrls = $categories
            ->getCollection()
            ->mapWithKeys(fn (Category $category) => [
                $category->id => $this->categoryImageUrl($category->image),
            ])
            ->all();

        return view(
            'livewire.farm-staff.category-manager',
            compact('categories', 'categoryImageUrls')
        );
    }
}