@props(['show' => false, 'maxRefund' => null])

@if ($show)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4">
        <div class="bg-white rounded-3xl p-6 max-w-lg w-full space-y-4 shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Khiếu Nại Hoàn Tiền Nông Sản (24H)</h3>
                <button type="button" wire:click="resetComplaintModal" class="text-gray-400 hover:text-gray-600 transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <p class="text-xs text-amber-800 bg-amber-50 p-3 rounded-xl border border-amber-200 leading-relaxed">
                <i class="fa-solid fa-shield-halved text-amber-600 mr-1"></i>
                Chính sách cam kết tươi giòn: Nông trại hỗ trợ hoàn tiền trực tiếp vào tài khoản ngân hàng nếu rau củ bị dập nát, héo úa hoặc hư hại do vận chuyển.
            </p>

            <form wire:submit.prevent="submitComplaint" class="space-y-3.5 text-xs">
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Mô tả chi tiết tình trạng hư hỏng *</label>
                    <textarea wire:model="complaint_reason" rows="3" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500 focus:border-brand-500" placeholder="VD: Cải bó xôi bị dập nát nhiều lá, cà chua bị vỡ hỏng do vận chuyển..."></textarea>
                    @error('complaint_reason') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="font-bold text-gray-700 block mb-1">Số tiền đề xuất hoàn (VNĐ) *</label>
                    <input type="number" wire:model="complaint_refund_amount" class="w-full rounded-xl border-gray-200 text-xs font-mono font-bold focus:ring-brand-500 focus:border-brand-500" placeholder="VD: 50000">
                    @if ($maxRefund)
                        <span class="text-[10px] text-gray-400 mt-0.5 block">Tối đa: {{ number_format($maxRefund) }}đ (Tổng giá trị đơn hàng)</span>
                    @endif
                    @error('complaint_refund_amount') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-1">
                        <label class="font-bold text-gray-700 block mb-1">Ngân hàng *</label>
                        <select wire:model="bank_name" class="w-full rounded-xl border-gray-200 text-xs focus:ring-brand-500 focus:border-brand-500">
                            <option value="">-- Chọn --</option>
                            <option value="MBBank">MB Bank</option>
                            <option value="Vietcombank">Vietcombank</option>
                            <option value="Techcombank">Techcombank</option>
                            <option value="ACB">ACB</option>
                            <option value="VPBank">VPBank</option>
                            <option value="VietinBank">VietinBank</option>
                            <option value="BIDV">BIDV</option>
                            <option value="MoMo">Ví MoMo</option>
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

                <div>
                    <label class="font-bold text-gray-700 block mb-1">Hình ảnh bằng chứng nông sản hư hại</label>
                    <input type="file" wire:model="complaint_images" multiple accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
                    @error('complaint_images.*') <span class="text-red-500 text-[11px] block mt-0.5">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" wire:click="resetComplaintModal" class="px-4 py-2 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 transition cursor-pointer">
                        Hủy bỏ
                    </button>
                    <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 rounded-xl text-xs font-bold bg-amber-600 text-white hover:bg-amber-700 shadow-sm flex items-center gap-1.5 transition cursor-pointer">
                        <i class="fa-solid fa-spinner fa-spin text-xs" wire:loading></i>
                        <span wire:loading.remove><i class="fa-solid fa-paper-plane mr-1"></i>Gửi khiếu nại</span>
                        <span wire:loading>Đang gửi...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

