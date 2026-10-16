<?php
namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin hệ thống
        User::updateOrCreate(
            ['email' => 'admin@datlanhfarm.vn'],
            [
                'name' => 'Quản Trị Viên',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN ?? 'admin',
                'phone' => '0987654321',
            ]
        );

        // 2. Nhân viên Nông Trại (Farm Staff)
        User::updateOrCreate(
            ['email' => 'farm@datlanhfarm.vn'],
            [
                'name' => 'Nguyễn Văn Vườn',
                'password' => Hash::make('password'),
                'role' => UserRole::FARM_STAFF ?? 'farm_staff',
                'phone' => '0912345678',
            ]
        );

        // 3. Nhân viên Bán hàng & CSKH (Sales)
        User::updateOrCreate(
            ['email' => 'sales@datlanhfarm.vn'],
            [
                'name' => 'Trần Thị Kho',
                'password' => Hash::make('password'),
                'role' => UserRole::SALES ?? 'sales',
                'phone' => '0934567890',
            ]
        );

        // 4. Khách hàng mẫu (Khách hàng Lê Thanh Trúc trên giao diện)
        User::updateOrCreate(
            ['email' => 'thanhtruc@example.com'],
            [
                'name' => 'Lê Thanh Trúc',
                'password' => Hash::make('password'),
                'role' => UserRole::CUSTOMER ?? 'customer',
                'phone' => '0909123456',
                'reward_points' => 120,
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150',
            ]
        );
    }
}