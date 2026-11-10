@props(['show' => false, 'refundAmount' => 0, 'images' => [], 'banks' => []])

@if ($show)
    <div x-data="{ previewImg: null }" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-xl max-h-[90vh] flex flex-col overflow-hidden">
            <div class="flex justify-between items-center px-6 pt-5 pb-3 border-b border-gray-100 shrink-0">
                <h3 class="text-base font-bold text-gray-900">Khiếu Nại Hoàn Tiền Nông Sản (24H)</h3>
                <button type="button" wire:click="resetComplaintModal" class="text-gray-400 hover:text-gray-600 transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form wire:submit.prevent="submitComplaint" class="flex-1 min-h-0 flex flex-col text-xs">
                <div class="flex-1 min-h-0 overflow-y-auto custom-scrollbar overscroll-contain px-6 py-4 space-y-3.5">
                    <p class="text-xs text-amber-800 bg-amber-50 p-3 rounded-xl border border-amber-200 leading-relaxed">
                        <i class="fa-solid fa-shield-halved text-amber-600 mr-1"></i>
                        Chính sách cam kết tươi giòn: Nông trại hỗ trợ hoàn tiền trực tiếp vào tài khoản ngân hàng nếu rau củ bị dập nát, héo úa hoặc hư hại do vận chuyển.
                    </p>

                    <div>
                    <label class="font-bold text-gray-700 block mb-1">Mô tả chi tiết tình trạng hư hỏng *</label>
                    <textarea wire:model="complaint_reason" rows="3" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500 focus:border-brand-500" placeholder="VD: Cải bó xôi bị dập nát nhiều lá, cà chua bị vỡ hỏng do vận chuyển..."></textarea>
                    @error('complaint_reason') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                </div>

                <!-- Số tiền bồi hoàn cố định theo giá trị đơn hàng (100%) -->
                <div class="bg-emerald-50/80 border border-emerald-200 rounded-2xl p-3.5 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-900 block flex items-center gap-1.5">
                            <i class="fa-solid fa-hand-holding-dollar text-emerald-600"></i> Số tiền bồi hoàn:
                        </span>
                        <span class="text-[10px] text-emerald-700 font-medium">Chính sách bảo hành hoàn trả 100% giá trị đơn hàng</span>
                    </div>
                    <span class="text-base sm:text-lg font-black text-emerald-800 font-mono">
                        {{ number_format($refundAmount ?? 0, 0, ',', '.') }}đ
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-1">
                        <label class="font-bold text-gray-700 block mb-1">Ngân hàng *</label>
                        <select wire:model="bank_name" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500 focus:border-brand-500">
                            <option value="">-- Chọn ngân hàng --</option>
                            @forelse ($banks as $bank)
                                <option value="{{ $bank['shortName'] }}">
                                    {{ $bank['shortName'] }} ({{ $bank['code'] }})
                                </option>
                            @empty
                                <option value="MBBank">MBBank (MB)</option>
                                <option value="Vietcombank">Vietcombank (VCB)</option>
                                <option value="Techcombank">Techcombank (TCB)</option>
                                <option value="ACB">ACB</option>
                                <option value="VPBank">VPBank (VPB)</option>
                                <option value="VietinBank">VietinBank (CTG)</option>
                                <option value="BIDV">BIDV</option>
                                <option value="MoMo">Ví MoMo</option>
                            @endforelse
                            @if (!empty($banks) && !collect($banks)->contains('shortName', 'MoMo'))
                                <option value="MoMo">Ví MoMo</option>
                            @endif
                        </select>
                        @error('bank_name') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="font-bold text-gray-700 block mb-1">Số tài khoản / SĐT MoMo *</label>
                        <input type="text" wire:model="bank_account_number" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500 focus:border-brand-500" placeholder="VD: 0391000123456">
                        @error('bank_account_number') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="font-bold text-gray-700 block mb-1">Tên chủ tài khoản (Viết hoa không dấu) *</label>
                    <input type="text" wire:model="bank_account_name" class="w-full rounded-xl border-gray-200 text-xs uppercase focus:ring-brand-500 focus:border-brand-500" placeholder="VD: NGUYEN VAN A">
                    @error('bank_account_name') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-gray-700 block">Hình ảnh bằng chứng nông sản hư hại *</label>
                        <span class="text-[10px] font-medium {{ count($images) >= 4 ? 'text-amber-700 font-bold' : 'text-gray-400' }}">
                            (Đã chọn {{ count($images) }}/4 ảnh)
                        </span>
                    </div>

                    @if (count($images) < 4)
                        <label class="inline-flex items-center justify-center gap-2 w-full cursor-pointer text-xs font-semibold text-gray-600 hover:text-brand-600 bg-gray-50 hover:bg-brand-50/70 border border-dashed border-gray-300 hover:border-brand-400 rounded-xl px-3.5 py-2.5 transition-colors">
                            <i class="fa-solid fa-camera text-brand-600 text-sm"></i>
                            <span>{{ empty($images) ? 'Chọn ảnh bằng chứng (Tối đa 4 ảnh)' : 'Chọn thêm ảnh (' . count($images) . '/4 ảnh)' }}</span>
                            <input type="file" wire:model="new_complaint_images" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">
                        </label>
                    @endif

                    <div wire:loading wire:target="new_complaint_images" class="text-[11px] font-semibold text-brand-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <span>Đang tải ảnh lên...</span>
                    </div>

                    <!-- Khung Preview ảnh đã chọn trước khi gửi -->
                    @if (!empty($images))
                        <div class="flex flex-wrap gap-2.5 pt-1">
                            @foreach ($images as $index => $cImg)
                                @if (is_object($cImg) && method_exists($cImg, 'temporaryUrl') && (!method_exists($cImg, 'isPreviewable') || $cImg->isPreviewable()))
                                    @php
                                        $tempUrl = $cImg->temporaryUrl();
                                        $origName = method_exists($cImg, 'getClientOriginalName') ? $cImg->getClientOriginalName() : ('Ảnh ' . ($index + 1));
                                    @endphp
                                    <div class="relative group" wire:key="complaint-preview-{{ $index }}-{{ md5($cImg->getFilename()) }}">
                                        <img src="{{ $tempUrl }}"
                                            @click="previewImg = '{{ $tempUrl }}'"
                                            alt="{{ $origName }}"
                                            title="{{ $origName }} (Nhấn để phóng to)"
                                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl object-cover border border-brand-200 shadow-2xs cursor-pointer hover:opacity-90 transition">
                                        <button type="button"
                                            wire:click="removeComplaintImage({{ $index }})"
                                            wire:loading.attr="disabled"
                                            title="Xóa ảnh này"
                                            class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-rose-500 hover:bg-rose-600 text-white rounded-full flex items-center justify-center text-[10px] shadow-sm transition cursor-pointer disabled:opacity-50">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @error('complaint_images') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                    @error('complaint_images.*') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                    @error('new_complaint_images.*') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                </div>
                </div>

                <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50/40 shrink-0">
                    <button type="button" wire:click="resetComplaintModal" class="px-4 py-2 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 transition cursor-pointer">
                        Hủy bỏ
                    </button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="submitComplaint,new_complaint_images" class="px-5 py-2 rounded-xl text-xs font-bold bg-amber-600 text-white hover:bg-amber-700 shadow-sm flex items-center gap-1.5 transition cursor-pointer disabled:opacity-50">
                        <i class="fa-solid fa-spinner fa-spin text-xs" wire:loading wire:target="submitComplaint"></i>
                        <span wire:loading.remove wire:target="submitComplaint"><i class="fa-solid fa-paper-plane mr-1"></i>Gửi khiếu nại</span>
                        <span wire:loading wire:target="submitComplaint">Đang gửi...</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Lightbox xem ảnh phóng to khi click vào thumbnail -->
        <div x-show="previewImg" x-cloak @click="previewImg = null" @keydown.escape.window="previewImg = null"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 backdrop-blur-xs p-4">
            <div class="relative max-w-2xl w-full flex items-center justify-center" @click.stop>
                <img :src="previewImg" class="max-h-[85vh] max-w-full rounded-2xl shadow-2xl object-contain bg-white">
                <button type="button" @click="previewImg = null"
                    class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-white text-gray-700 hover:text-rose-600 shadow-lg flex items-center justify-center cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    </div>
@endif

