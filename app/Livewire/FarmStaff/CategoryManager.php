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

        $this->status = $category->is_active
            ? 'active'
            : 'hidden';

        $this->currentImageUrl = $category->image_url;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function updatedName(): void
    {
        // Thêm mới: tự sinh slug khi nhập tên.
        // Chỉnh sửa: giữ slug hiện tại để tránh tự đổi URL cũ.
        if ($this->editingId === null) {
            $this->slug = Str::slug(trim($this->name));
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
        $this->resetValidation('form');

        $this->name = trim($this->name);
        $this->slug = trim($this->slug);

        // Cho phép để trống slug để hệ thống sinh từ tên.
        if ($this->slug === '') {
            $this->slug = Str::slug($this->name);
        }

        $validated = $this->validate();

        // Chỉ nhận những trường form thực sự được phép cập nhật.
        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'is_active' => $validated['status'] === 'active',
        ];

        $wasEditing = $this->editingId !== null;

        $newPath = null;
        $oldPath = null;

        try {
            $saved = DB::transaction(function () use (
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

                if (
                    $category->exists
                    && ! $category->isDirty()
                    && $this->newImage === null
                ) {
                    return false;
                }

                if ($this->newImage !== null) {
                    $path = $this->newImage->store(
                        'categories',
                        'public'
                    );

                    // Public disk hiện cấu hình throw => false.
                    if (! is_string($path) || $path === '') {
                        throw new RuntimeException(
                            'Không lưu được ảnh danh mục.'
                        );
                    }

                    $newPath = $path;

                    // Lấy đường dẫn ảnh cũ từ DB.
                    $oldPath = $category->image;
                    $category->image = $newPath;
                }

                // Không chọn ảnh mới thì giữ nguyên ảnh hiện tại.
                if (! $category->save()) {
                    throw new RuntimeException(
                        'Không lưu được danh mục.'
                    );
                }

                return true;
            });
        } catch (UniqueConstraintViolationException $exception) {
            // Có thể xảy ra nếu hai người cùng lưu một slug.
            $this->deleteManagedImage($newPath);

            $this->addError(
                'slug',
                'Slug vừa được sử dụng. Vui lòng chọn slug khác.'
            );

            return;
        } catch (Throwable $exception) {
            // DB rollback nhưng file upload cần được dọn riêng.
            $this->deleteManagedImage($newPath);

            report($exception);

            $this->addError(
                'form',
                'Không thể lưu danh mục. Vui lòng thử lại.'
            );

            return;
        }

        if (! $saved) {
            $this->notify('info', 'Không có thay đổi nào.');

            return;
        }

        // Chỉ dọn ảnh cũ sau khi DB đã commit.
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

        return view(
            'livewire.farm-staff.category-manager',
            compact('categories')
        );
    }
}