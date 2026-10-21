<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ban quản trị & Nhân viên
        User::updateOrCreate(['email' => 'admin@datlanhfarm.vn'], [
            'name' => 'Quản Trị Viên Master', 'password' => Hash::make('password'),
            'phone' => '0987654321', 'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150',
            'role' => 'admin', 'status' => 'active', 'reward_points' => 0, 'email_verified_at' => now(),
        ]);

        User::updateOrCreate(['email' => 'farm@datlanhfarm.vn'], [
            'name' => 'Nguyễn Văn Vườn', 'password' => Hash::make('password'),
            'phone' => '0912345678', 'avatar' => 'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=150',
            'role' => 'farm_staff', 'status' => 'active', 'reward_points' => 0, 'email_verified_at' => now(),
        ]);

        User::updateOrCreate(['email' => 'sales@datlanhfarm.vn'], [
            'name' => 'Trần Thị Kho', 'password' => Hash::make('password'),
            'phone' => '0934567890', 'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150',
            'role' => 'sales', 'status' => 'active', 'reward_points' => 0, 'email_verified_at' => now(),
        ]);

        // Danh sách Khách hàng VIP & Mua lẻ (Đã bổ sung STK Ngân hàng mặc định)
        $customers = [
            [
                'email' => 'thanhtruc@example.com', 'name' => 'Lê Thanh Trúc', 'phone' => '0909123456', 'points' => 240, 
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150',
                'bank_name' => 'MBBank', 'bank_number' => '0909123456', 'bank_acc_name' => 'LE THANH TRUC'
            ],
            [
                'email' => 'minhhoang@example.com', 'name' => 'Phạm Minh Hoàng', 'phone' => '0918223344', 'points' => 150, 
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150',
                'bank_name' => 'Vietcombank', 'bank_number' => '012345678999', 'bank_acc_name' => 'PHAM MINH HOANG'
            ],
            [
                'email' => 'haimy@example.com', 'name' => 'Nguyễn Hải Mỹ', 'phone' => '0988776655', 'points' => 80, 
                'avatar' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=150',
                'bank_name' => 'Techcombank', 'bank_number' => '19034567890011', 'bank_acc_name' => 'NGUYEN HAI MY'
            ],
            [
                'email' => 'quocbao@example.com', 'name' => 'Đỗ Quốc Bảo', 'phone' => '0933445566', 'points' => 0, 
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150',
                'bank_name' => 'MBBank', 'bank_number' => '0933445566', 'bank_acc_name' => 'DO QUOC BAO'
            ],
        ];

        foreach ($customers as $c) {
            User::updateOrCreate(['email' => $c['email']], [
                'name'                        => $c['name'], 
                'password'                    => Hash::make('password'),
                'phone'                       => $c['phone'], 
                'avatar'                      => $c['avatar'],
                'role'                        => 'customer', 
                'status'                      => 'active',
                'reward_points'               => $c['points'], 
                'default_bank_name'           => $c['bank_name'],
                'default_bank_account_number' => $c['bank_number'],
                'default_bank_account_name'   => $c['bank_acc_name'],
                'email_verified_at'           => now(),
            ]);

            DB::table('password_reset_tokens')->updateOrInsert(['email' => $c['email']], [
                'token' => Str::random(60), 'created_at' => now(),
            ]);
        }
    }
}