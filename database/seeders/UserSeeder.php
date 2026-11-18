<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Tài khoản Quản trị viên gốc để đăng nhập và khởi tạo nhân sự thật
        User::updateOrCreate(['email' => 'admin@datlanhfarm.vn'], [
            'name' => 'Quản Trị Viên',
            'password' => Hash::make('password'),
            'phone' => '0987654321',
            'role' => 'admin',
            'status' => 'active',
            'reward_points' => 0,
            'email_verified_at' => now(),
        ]);
    }
}
