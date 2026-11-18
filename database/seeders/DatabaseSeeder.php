<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,          // 1. Tài khoản Admin gốc
            SystemContentSeeder::class, // 2. Cấu hình hệ thống & 2 trang tĩnh mặc định
        ]);
    }
}
