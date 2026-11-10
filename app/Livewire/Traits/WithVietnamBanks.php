<?php

namespace App\Livewire\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait WithVietnamBanks
{
    /**
     * Lấy danh sách ngân hàng Việt Nam từ API VietQR (chỉ cache 24h khi dữ liệu hợp lệ).
     */
    public function banks(): array
    {
        $cached = Cache::get('vietnam_banks_list');
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        try {
            $response = Http::timeout(3)->get('https://api.vietqr.io/v2/banks');
            if ($response->successful()) {
                $data = $response->json('data', []);
                if (is_array($data) && !empty($data)) {
                    Cache::put('vietnam_banks_list', $data, 86400);

                    return $data;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Không thể lấy danh sách ngân hàng VietQR: ' . $e->getMessage());
        }

        return [];
    }
}

