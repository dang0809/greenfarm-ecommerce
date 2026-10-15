<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Rau Lá Xanh',
                'slug' => 'rau-la-xanh',
                'image' => 'https://images.unsplash.com/photo-1622383563227-04401ab4e5ea?auto=format&fit=crop&q=80&w=300',
            ],
            [
                'name' => 'Củ Quả Tươi',
                'slug' => 'cu-qua-tuoi',
                'image' => 'https://images.unsplash.com/photo-1592282139728-28040d96a922?auto=format&fit=crop&q=80&w=300',
            ],
            [
                'name' => 'Trái Cây',
                'slug' => 'trai-cay-theo-mua',
                'image' => 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?auto=format&fit=crop&q=80&w=300',
            ],
            [
                'name' => 'Nấm Tươi',
                'slug' => 'nam-organic',
                'image' => 'https://images.unsplash.com/photo-1509358271058-acd22cc93898?auto=format&fit=crop&q=80&w=300',
            ],
            [
                'name' => 'Rau Gia Vị',
                'slug' => 'rau-gia-vi',
                'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&q=80&w=300',
            ],
            [
                'name' => 'Hạt Dinh Dưỡng',
                'slug' => 'hat-dinh-duong',
                'image' => 'https://images.unsplash.com/photo-1536922246289-88c42f957773?auto=format&fit=crop&q=80&w=300',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}