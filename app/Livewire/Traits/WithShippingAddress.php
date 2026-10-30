<?php

namespace App\Livewire\Traits;

use App\Models\Address;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait WithShippingAddress
{
    /**
     * Thay đổi địa chỉ nhận hàng từ Modal popup
     */
    public function selectAddress(int $addressId): void
    {
        if (isset($this->isLocked) && $this->isLocked) {
            return;
        }

        $this->selectedAddressId = $addressId;
        $this->showAddressModal = false;
        $this->dispatch('show-toast', type: 'success', message: 'Đã cập nhật địa chỉ giao hàng.');
    }

    /**
     * Livewire Computed Property: $this->selectedAddress
     * Tự động lấy địa chỉ đang chọn (theo ID, hoặc mặc định, hoặc địa chỉ đầu tiên)
     */
    public function getSelectedAddressProperty(): ?Address
    {
        return Address::where('user_id', Auth::id())
            ->where('id', $this->selectedAddressId)
            ->first()
            ?? Address::where('user_id', Auth::id())->where('is_default', true)->first()
            ?? Address::where('user_id', Auth::id())->first();
    }

    /**
     * Lấy tọa độ nông trại từ bảng system_settings (Cache 24h)
     */
    protected function getFarmCoordinates(): array
    {
        return Cache::remember('system_farm_coordinates', 86400, function () {
            $settings = SystemSetting::whereIn('key', [
                'farm_latitude', 'farm_longitude'
            ])->pluck('value', 'key')->toArray();

            return [
                'lat' => (float) ($settings['farm_latitude'] ?? 10.7769),
                'lng' => (float) ($settings['farm_longitude'] ?? 106.7009),
            ];
        });
    }

    /**
     * Lấy bán kính giao hàng tối đa từ bảng system_settings (Cache 24h)
     */
    protected function getMaxShippingDistance(): float
    {
        return Cache::remember('system_max_shipping_distance', 86400, function () {
            $val = SystemSetting::where('key', 'max_shipping_distance_km')->value('value');
            return (float) ($val ?? 25.0);
        });
    }

    /**
     * Tính khoảng cách đường bộ bằng OSRM API (Cache 24h theo cặp tọa độ)
     */
    protected function getShippingDistance(?float $destLat, ?float $destLng): ?float
    {
        if (!$destLat || !$destLng) {
            return null;
        }

        $cacheKey = 'osrm_dist_' . md5("{$destLat}_{$destLng}");
        return Cache::remember($cacheKey, 86400, function () use ($destLat, $destLng) {
            $farm = $this->getFarmCoordinates();
            $farmLat = $farm['lat'];
            $farmLng = $farm['lng'];

            try {
                $coordinates = "{$farmLng},{$farmLat};{$destLng},{$destLat}";
                $url = "https://router.project-osrm.org/route/v1/driving/{$coordinates}?overview=false";

                $response = Http::timeout(2.5)->get($url);
                if ($response->successful() && isset($response->json()['routes'][0]['distance'])) {
                    return (float) round($response->json()['routes'][0]['distance'] / 1000, 1);
                }
            } catch (\Exception $e) {
                Log::warning('Lỗi kết nối OSRM API: ' . $e->getMessage());
            }
            return null;
        });
    }

    /**
     * Lấy danh sách địa chỉ của khách hàng, tính toán khoảng cách và trạng thái bán kính phục vụ
     */
    protected function resolveShippingAddresses(?int $userId = null): array
    {
        $userId = $userId ?? Auth::id();
        $maxDistance = $this->getMaxShippingDistance();

        $addresses = Address::where('user_id', $userId)->get()->each(function ($addr) use ($maxDistance) {
            $addr->distance    = $this->getShippingDistance($addr->latitude ?? null, $addr->longitude ?? null);
            $addr->is_unknown  = is_null($addr->distance);
            $addr->is_exceeded = !is_null($addr->distance) && ($addr->distance > $maxDistance);
        });

        $address = $addresses->firstWhere('id', $this->selectedAddressId)
            ?? $addresses->firstWhere('is_default', true)
            ?? $addresses->first();

        $distance = $address ? $address->distance : null;
        $isDistanceUnknown = $address && is_null($distance);
        $isDistanceExceeded = !is_null($distance) && ($distance > $maxDistance);

        return [
            'addresses'          => $addresses,
            'address'            => $address,
            'distance'           => $distance,
            'maxDistance'        => $maxDistance,
            'isDistanceUnknown'  => $isDistanceUnknown,
            'isDistanceExceeded' => $isDistanceExceeded,
        ];
    }

    /**
     * Kiểm tra tính hợp lệ của địa chỉ nhận hàng trước khi thanh toán / lưu hợp đồng
     */
    protected function validateShippingAddress(?Address $address, ?float $distance, float $maxDistance): bool
    {
        if (!$address) {
            $this->dispatch('show-toast', type: 'error', message: 'Vui lòng chọn địa chỉ giao hàng!');
            return false;
        }

        if (is_null($distance)) {
            $this->dispatch('show-toast', type: 'error', message: 'Địa chỉ nhận hàng chưa xác định được vị trí, vui lòng cập nhật lại địa chỉ!');
            return false;
        }

        if ($distance > $maxDistance) {
            $this->dispatch('show-toast', type: 'error', message: "Địa chỉ nhận hàng vượt quá bán kính phục vụ tối đa ({$maxDistance} km)!");
            return false;
        }

        return true;
    }
}
