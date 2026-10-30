@props([
    'address' => null,
    'distance' => null,
    'maxDistance' => 25,
    'isExceeded' => false,
    'isUnknown' => false,
])

@php
    $recipientName = $address->recipient_name ?? (is_array($address) ? ($address['recipient_name'] ?? '') : '');
    $recipientPhone = $address->recipient_phone ?? (is_array($address) ? ($address['recipient_phone'] ?? '') : '');
    $isDefault = (bool) ($address->is_default ?? (is_array($address) ? ($address['is_default'] ?? false) : false));
    $fullAddress = $address->full_address ?? (is_array($address) ? ($address['full_address'] ?? '') : '');
    $hasAddress = !empty($recipientName) || !empty($fullAddress);
    $isUnknown = $isUnknown || ($hasAddress && is_null($distance));
@endphp

<div class="bg-white p-6 sm:p-7 rounded-3xl border border-gray-100 shadow-sm space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-gray-100 flex-wrap gap-2">
        <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
            <i class="fa-solid fa-location-dot text-brand-600"></i> Địa Chỉ Nhận Hàng
        </h2>
        <div class="flex items-center gap-2">
            @if ($hasAddress)
                @if ($isUnknown)
                    <span class="text-[11px] font-bold text-amber-700 bg-amber-50 border-amber-300 px-2.5 py-1 rounded-full border inline-flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation text-amber-600"></i> Chưa xác định vị trí
                    </span>
                @elseif (!is_null($distance))
                    <span class="text-[11px] font-bold {{ $isExceeded ? 'text-red-700 bg-red-50 border-red-200' : 'text-brand-700 bg-brand-50 border-brand-200' }} px-2.5 py-1 rounded-full border">
                        {{ $isExceeded ? 'Vượt bán kính (' . $distance . ' km)' : 'Trong bán kính (' . $distance . ' km)' }}
                    </span>
                @endif
            @endif
            <button type="button" 
                    wire:click="$set('showAddressModal', true)"
                    class="text-xs font-bold text-brand-700 hover:text-brand-800 bg-brand-50 hover:bg-brand-100 border border-brand-200 px-3.5 py-1.5 rounded-xl transition-all flex items-center gap-1.5 shadow-2xs cursor-pointer">
                <i class="fa-solid fa-repeat text-[10px]"></i>
                <span>{{ $hasAddress ? 'Thay đổi địa chỉ' : 'Chọn địa chỉ' }}</span>
            </button>
        </div>
    </div>

    @if ($hasAddress)
        <div class="p-4 sm:p-5 rounded-2xl border {{ $isUnknown ? 'border-amber-300 bg-amber-50/40' : ($isExceeded ? 'border-red-300 bg-red-50/50' : 'border-gray-200 bg-gray-50/60') }} space-y-2">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="text-sm sm:text-base font-bold text-gray-900">{{ $recipientName }}</span>
                <span class="text-xs sm:text-sm text-gray-600 font-mono">{{ $recipientPhone }}</span>
                @if ($isDefault)
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2.5 py-0.5 rounded-full border border-brand-200">Mặc định</span>
                @endif
            </div>
            <p class="text-xs sm:text-sm text-gray-600 mt-2 leading-relaxed">{{ $fullAddress }}</p>
            @if(!is_null($distance))
                <div class="mt-3 flex items-center gap-2 text-xs {{ $isExceeded ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                    <i class="fa-solid fa-route {{ $isExceeded ? 'text-red-600' : 'text-brand-600' }}"></i>
                    <span>Khoảng cách đến nông trại: <b class="font-mono text-gray-700">{{ $distance }} km</b> / tối đa {{ $maxDistance }} km</span>
                </div>
            @endif
        </div>

        @if ($isUnknown)
            <!-- CẢNH BÁO CHƯA XÁC ĐỊNH ĐƯỢC VỊ TRÍ -->
            <div class="p-4 rounded-2xl border border-amber-300 bg-amber-50 text-amber-900 text-xs space-y-1.5">
                <p class="font-bold flex items-center gap-1.5 text-sm text-amber-800">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                    <span>Không tìm thấy vị trí của địa chỉ này trên bản đồ!</span>
                </p>
                <p class="text-amber-800 leading-relaxed">
                    Hệ thống không thể xác định tọa độ để tính khoảng cách và phí giao hàng. Vui lòng bấm <b>"Thay đổi địa chỉ"</b> để chọn địa chỉ khác hoặc cập nhật lại địa chỉ chi tiết hơn trong hồ sơ.
                </p>
                <div class="pt-1">
                    <a href="{{ route('customer.profile', ['tab' => 'addresses']) }}" target="_blank" class="text-xs font-bold text-brand-700 hover:underline inline-flex items-center gap-1">
                        <i class="fa-solid fa-pen-to-square"></i> Cập nhật địa chỉ trong hồ sơ &rarr;
                    </a>
                </div>
            </div>
        @elseif ($isExceeded)
            <!-- CẢNH BÁO VƯỢT BÁN KÍNH -->
            <div class="p-4 rounded-2xl border border-red-200 bg-red-50 text-red-700 text-xs space-y-1.5">
                <p class="font-bold flex items-center gap-1.5 text-sm">
                    <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
                    <span>Rất tiếc, địa chỉ này vượt quá bán kính giao hàng của nông trại!</span>
                </p>
                <p class="text-red-600 leading-relaxed">
                    Nông trại chỉ nhận giao hàng tươi trong ngày trong phạm vi <b>{{ $maxDistance }} km</b> (khoảng cách hiện tại: <b>{{ $distance }} km</b>). Vui lòng chọn địa chỉ khác gần hơn để tiếp tục đặt hàng.
                </p>
            </div>
        @endif
    @else
        <div class="p-4 rounded-2xl border border-dashed border-amber-300 bg-amber-50 text-amber-800 text-xs flex items-center justify-between">
            <span class="font-medium flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-sm"></i>
                <span>Bạn chưa chọn địa chỉ nhận hàng. Vui lòng chọn địa chỉ để giao hàng hỏa tốc!</span>
            </span>
        </div>
    @endif

    {{ $slot ?? '' }}
</div>
