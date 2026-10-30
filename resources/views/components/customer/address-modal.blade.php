@props([
    'show' => false,
    'addresses' => [],
    'selectedId' => 0,
    'maxDistance' => 25,
])

@if ($show)
    <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity"
         wire:click.self="$set('showAddressModal', false)">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-gray-100 space-y-5 max-h-[90vh] overflow-y-auto">
            
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-900">Chọn Địa Chỉ Nhận Hàng</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Chọn một địa chỉ để giao nông sản hỏa tốc</p>
                </div>
                <button type="button" 
                        wire:click="$set('showAddressModal', false)" 
                        class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Danh sách địa chỉ -->
            <div class="space-y-3">
                @forelse ($addresses as $addr)
                    @php
                        $id = $addr->id ?? ($addr['id'] ?? 0);
                        $name = $addr->recipient_name ?? ($addr['recipient_name'] ?? '');
                        $phone = $addr->recipient_phone ?? ($addr['recipient_phone'] ?? '');
                        $isDef = (bool) ($addr->is_default ?? ($addr['is_default'] ?? false));
                        $dist = $addr->distance ?? ($addr['distance'] ?? null);
                        $exceeded = (bool) ($addr->is_exceeded ?? ($addr['is_exceeded'] ?? false));
                        $full = $addr->full_address ?? ($addr['full_address'] ?? '');
                        $isSelected = ($selectedId == $id);
                    @endphp

                    <div wire:click="selectAddress({{ $id }})" 
                         class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex items-start gap-3 {{ $isSelected ? 'border-brand-500 bg-brand-50/20' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                        <input type="radio" name="modal_address_radio" {{ $isSelected ? 'checked' : '' }} class="mt-1 text-brand-600 focus:ring-brand-500">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-bold text-gray-900">{{ $name }}</span>
                                    <span class="text-xs text-gray-500 font-mono">{{ $phone }}</span>
                                    @if ($isDef)
                                        <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full border border-brand-200">Mặc định</span>
                                    @endif
                                </div>
                                @if(is_null($dist))
                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border-amber-300 px-2 py-0.5 rounded-full border">
                                        Chưa có vị trí
                                    </span>
                                @elseif($exceeded)
                                    <span class="text-[10px] font-bold text-red-700 bg-red-100 border-red-200 px-2 py-0.5 rounded-full border font-mono">
                                        {{ $dist }} km (Vượt bán kính)
                                    </span>
                                @else
                                    <span class="text-[10px] font-mono font-bold text-gray-500 bg-gray-100 border-gray-200 px-2 py-0.5 rounded-full border">
                                        {{ $dist }} km
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">{{ $full }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-500 text-center py-4">Bạn chưa lưu địa chỉ nào trong sổ địa chỉ.</p>
                @endforelse
            </div>

            <!-- Footer Modal -->
            <div class="pt-3 border-t border-gray-100 flex items-center justify-between gap-3">
                <a href="{{ route('customer.profile', ['tab' => 'addresses']) }}" target="_blank" class="text-xs font-semibold text-gray-500 hover:text-brand-600 flex items-center gap-1">
                    <i class="fa-solid fa-pen-to-square"></i> Quản lý trong hồ sơ
                </a>
            </div>
        </div>
    </div>
@endif

