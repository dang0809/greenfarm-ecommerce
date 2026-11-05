<?php

namespace App\Livewire\FarmStaff;

use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.farm-staff.app')]
#[Title('Quản Lý Danh Mục Nông Sản | Nông Trại Đất Lành')]
class CategoryManager extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteCategory($id)
    {
        $category = Category::withCount('products')->find($id);
        
        if ($category && $category->products_count > 0) {
            session()->flash('error', 'Không thể xóa danh mục đang chứa sản phẩm trực thuộc!');
            return;
        }

        if ($category) {
            $category->delete();
            session()->flash('success', 'Đã xóa danh mục thành công!');
        }
    }

    public function render()
    {
        $query = Category::with('parent')->withCount('products');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        }

        $categories = $query->latest()->paginate(10);
        $parentCategories = Category::whereNull('parent_id')->get();

        return view('livewire.farm-staff.category-manager', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ]);
    }
}