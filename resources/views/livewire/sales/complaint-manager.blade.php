<div class="flex-1 flex flex-col min-w-0 min-h-0 overflow-hidden">
    <!-- ================= 1. HEADER CHÍNH ================= -->
    <header class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" onclick="toggleSidebar()" class="lg:hidden p-2 rounded-xl text-gray-600 hover:bg-brand-50 hover:text-brand-700 transition shrink-0" aria-label="Mở menu">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            <div class="min-w-0">
                <h1 class="text-base sm:text-2xl font-extrabold text-gray-900 tracking-tight truncate leading-tight">
                    Xử Lý Khiếu Nại & Bồi Thường
                </h1>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <div class="inline-flex items-center gap-1.5 sm:gap-2 bg-brand-50 border border-brand-200 text-brand-800 px-2.5 sm:px-4 py-1.5 sm:py-2 rounded-2xl text-[11px] sm:text-xs font-bold font-mono">
                <i class="fa-regular fa-calendar-check text-brand-700"></i>
                <span class="hidden sm:inline">Hôm nay:</span>
                <span>{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
    </header>

    <!-- ================= 2. NỘI DUNG CUỘN CHÍNH ================= -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12 h-[calc(100dvh-5rem)]">

        <!-- ================= THẺ TỔNG QUAN: TỔNG TIỀN ĐÃ HOÀN TRẢ ================= -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-emerald-100 shadow-soft flex items-center justify-between">
            <div class="flex items-center gap-3.5 sm:gap-4">
                <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-emerald-700 block uppercase tracking-wider">Tổng Tiền Đã Hoàn Trả Khách Hàng</span>
                    <div class="flex items-baseline gap-2 mt-0.5 flex-wrap">
                        <span class="text-xl sm:text-2xl font-black text-emerald-800 tracking-tight font-mono">{{ number_format($kpiRefundAmount, 0, ',', '.') }}đ</span>
                        <span class="text-xs text-gray-500 font-medium font-sans">({{ $kpiRefundCount }} đơn đã hoàn tiền thành công)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= 3. BẢNG DANH SÁCH KHIẾU NẠI CHÍNH ================= -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-gray-100 shadow-soft overflow-hidden space-y-4">
            
            <!-- Hàng Tab Lọc Theo Đúng 3 Trạng Thái Chuẩn CSDL (pending, resolved, rejected) -->
            <div class="flex overflow-x-auto border-b border-gray-100 px-4 sm:px-6 pt-3 gap-6 text-sm no-scrollbar bg-gray-50/40">
                <button type="button" wire:click="setStatusFilter('all')" class="pb-3 {{ $statusFilter === 'all' ? 'tab-btn-active font-extrabold text-brand-700' : 'tab-btn-inactive font-medium text-gray-500' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Tất cả</span>
                    <span class="text-xs font-bold bg-slate-200 text-slate-700 px-2 py-0.5 rounded-full">{{ $statusCounts['all'] }}</span>
                </button>
                <button type="button" wire:click="setStatusFilter('pending')" class="pb-3 {{ $statusFilter === 'pending' ? 'tab-btn-active font-extrabold text-rose-700' : 'tab-btn-inactive font-medium text-gray-500' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Chờ xử lý</span>
                    <span class="text-xs font-bold bg-rose-100 text-rose-800 px-2 py-0.5 rounded-full">{{ $statusCounts['pending'] }}</span>
                </button>
                <button type="button" wire:click="setStatusFilter('resolved')" class="pb-3 {{ $statusFilter === 'resolved' ? 'tab-btn-active font-extrabold text-emerald-700' : 'tab-btn-inactive font-medium text-gray-500' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Đã duyệt hoàn tiền</span>
                    <span class="text-xs font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">{{ $statusCounts['resolved'] }}</span>
                </button>
                <button type="button" wire:click="setStatusFilter('rejected')" class="pb-3 {{ $statusFilter === 'rejected' ? 'tab-btn-active font-extrabold text-gray-700' : 'tab-btn-inactive font-medium text-gray-500' }} transition whitespace-nowrap flex items-center gap-2 cursor-pointer">
                    <span>Đã từ chối</span>
                    <span class="text-xs font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $statusCounts['rejected'] }}</span>
                </button>
            </div>

            <!-- Thanh Tìm Kiếm (Chỉ tìm theo Mã Đơn Hàng, Bỏ nút reset) -->
            <div class="p-3.5 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                <div class="relative w-full sm:w-96">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs sm:text-sm"></i>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Tìm theo mã đơn hàng (#1794...)" 
                           class="w-full pl-10 pr-9 py-2 sm:py-2.5 border border-gray-200 rounded-xl sm:rounded-2xl text-xs sm:text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 bg-[#FAFAF8] font-medium transition-all">
                    @if(!empty($search))
                        <button type="button" wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-0.5 cursor-pointer" title="Xóa tìm kiếm">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Bảng Dữ Liệu Tinh Gọn (5 cột chuẩn, không tràn ngang) -->
            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[10px] sm:text-xs tracking-wider border-b border-gray-100">
                            <th class="py-3.5 px-4 sm:px-6 whitespace-nowrap">Đơn Hàng & Khách Hàng</th>
                            <th class="py-3.5 px-4">Lý Do & Bằng Chứng</th>
                            <th class="py-3.5 px-4 whitespace-nowrap">Số Tiền & Ngân Hàng</th>
                            <th class="py-3.5 px-4 text-center whitespace-nowrap">Trạng Thái</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @forelse($complaints as $c)
                            @php
                                $order = $c->order;
                                $refundVal = $order ? (float) $order->total : (float) ($c->refund_amount ?? 0);
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition">
                                <!-- 1. Đơn Hàng & Khách Hàng -->
                                <td class="py-3.5 px-4 sm:px-6 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" 
                                                wire:click="openComplaint({{ $c->id }})" 
                                                class="font-mono font-extrabold text-brand-800 hover:text-brand-950 hover:underline text-xs sm:text-sm cursor-pointer">
                                            #{{ $order?->order_code ?? '---' }}
                                        </button>
                                    </div>
                                    <p class="font-bold text-gray-900 text-xs mt-0.5">{{ $c->user?->name ?? $order?->recipient_name ?? 'Khách hàng' }}</p>
                                    <div class="flex items-center gap-1.5 text-[11px] text-gray-400 font-mono mt-0.5">
                                        <span>{{ $order?->recipient_phone ?? $c->user?->phone ?? '---' }}</span>
                                        <span>•</span>
                                        <span>{{ $c->created_at->format('H:i d/m') }}</span>
                                    </div>
                                </td>

                                <!-- 2. Lý Do Khiếu Nại & Ảnh Bằng Chứng -->
                                <td class="py-3.5 px-4 min-w-[220px] max-w-md">
                                    <div class="flex items-start gap-2.5">
                                        @if($c->images->isNotEmpty())
                                            <div class="flex items-center gap-1 shrink-0 mt-0.5">
                                                @foreach($c->images->take(2) as $img)
                                                    @php
                                                        $imgSrc = $img->url;
                                                    @endphp
                                                    <div class="w-9 h-9 rounded-lg overflow-hidden border border-gray-200 shrink-0 shadow-2xs bg-gray-50" title="Xem chi tiết đơn để phóng to ảnh">
                                                        <img src="{{ $imgSrc }}" class="w-full h-full object-cover">
                                                    </div>
                                                @endforeach
                                                @if($c->images->count() > 2)
                                                    <span class="text-[10px] font-bold text-gray-500 font-mono bg-gray-100 px-1.5 py-1 rounded" title="Tổng cộng {{ $c->images->count() }} ảnh">
                                                        +{{ $c->images->count() - 2 }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-semibold text-gray-800 line-clamp-2 leading-relaxed" title="{{ $c->reason }}">
                                                {{ $c->reason }}
                                            </p>
                                            @if($order && $order->items->isNotEmpty())
                                                <p class="text-[11px] text-gray-500 truncate mt-0.5">
                                                    <span class="text-gray-400">Đơn có:</span> {{ $order->items->pluck('product_name')->take(2)->implode(', ') }}@if($order->items->count() > 2)...@endif
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- 3. Số Tiền & Tài Khoản Ngân Hàng -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-mono font-black text-sm text-emerald-800">
                                        {{ number_format($refundVal, 0, ',', '.') }}đ
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="font-mono text-xs font-semibold text-gray-700">{{ $c->bank_account_number }}</span>
                                        <button type="button" 
                                                x-data 
                                                @click="navigator.clipboard.writeText('{{ $c->bank_account_number }}'); $dispatch('show-toast', {type: 'success', message: 'Đã sao chép STK: {{ $c->bank_account_number }}'})" 
                                                class="text-gray-400 hover:text-emerald-700 transition cursor-pointer p-0.5" 
                                                title="Sao chép STK">
                                            <i class="fa-regular fa-copy text-[11px]"></i>
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-gray-400 truncate max-w-[180px]">{{ $c->bank_name }} • {{ $c->bank_account_name }}</p>
                                </td>

                                <!-- 4. Trạng Thái -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if($c->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            Chờ xử lý
                                        </span>
                                    @elseif($c->status === 'resolved')
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                                            <i class="fa-solid fa-circle-check text-[10px]"></i>
                                            Đã hoàn tiền
                                        </span>
                                    @elseif($c->status === 'rejected')
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-gray-700 bg-gray-100 border border-gray-200 px-2.5 py-1 rounded-full">
                                            <i class="fa-solid fa-ban text-[10px]"></i>
                                            Đã từ chối
                                        </span>
                                    @endif
                                </td>

                                <!-- 5. Thao Tác -->
                                <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap">
                                    <button type="button" 
                                            wire:click="openComplaint({{ $c->id }})" 
                                            class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl border transition shadow-2xs cursor-pointer {{ $c->status === 'pending' ? 'bg-brand-600 hover:bg-brand-700 text-white border-transparent' : 'bg-white hover:bg-gray-50 text-gray-700 border-gray-200' }}">
                                        <i class="fa-solid {{ $c->status === 'pending' ? 'fa-scale-balanced' : 'fa-eye' }} text-[11px]"></i>
                                        <span>{{ $c->status === 'pending' ? 'Xử Lý' : 'Chi Tiết' }}</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="w-14 h-14 rounded-full bg-gray-50 text-gray-300 flex items-center justify-center mx-auto mb-2 text-2xl border border-gray-100">
                                        <i class="fa-solid fa-shield-heart"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-700">Không tìm thấy yêu cầu khiếu nại nào phù hợp</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Tất cả phản ánh đã được tiếp nhận và xử lý hoàn tất.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân trang Livewire -->
            <div class="p-4 border-t border-gray-100">
                {{ $complaints->links() }}
            </div>
        </div>

    </div>

    <!-- ================= 4. MODAL THẨM ĐỊNH & PHÊ DUYỆT HOÀN TIỀN ================= -->
    @if($showModal && $selectedComplaint)
        @php
            $mOrder = $selectedComplaint->order;
            $mRefundVal = $mOrder ? (float) $mOrder->total : (float) ($selectedComplaint->refund_amount ?? 0);
            $isPending = ($selectedComplaint->status === 'pending');
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-3 sm:p-4">
            <div class="bg-white rounded-2xl sm:rounded-3xl w-full max-w-4xl shadow-2xl p-5 sm:p-8 max-h-[92vh] overflow-y-auto custom-scrollbar border border-gray-100 space-y-5 sm:space-y-6">
                
                <!-- Header Modal -->
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 sm:pb-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap">
                            <h3 class="text-base sm:text-xl font-extrabold text-gray-900 font-mono">Đơn Hàng #{{ $mOrder?->order_code ?? '---' }}</h3>
                            @if($selectedComplaint->status === 'pending')
                                <span class="text-[10px] sm:text-xs font-bold px-2.5 sm:px-3 py-0.5 rounded-full border bg-rose-50 text-rose-700 border-rose-200">Chờ xử lý</span>
                            @elseif($selectedComplaint->status === 'resolved')
                                <span class="text-[10px] sm:text-xs font-bold px-2.5 sm:px-3 py-0.5 rounded-full border bg-emerald-50 text-emerald-700 border-emerald-200">Đã duyệt hoàn tiền</span>
                            @elseif($selectedComplaint->status === 'rejected')
                                <span class="text-[10px] sm:text-xs font-bold px-2.5 sm:px-3 py-0.5 rounded-full border bg-gray-100 text-gray-700 border-gray-200">Đã từ chối</span>
                            @endif
                        </div>
                        <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5 font-mono">Gửi yêu cầu lúc: <span>{{ $selectedComplaint->created_at->format('H:i • d/m/Y') }}</span></p>
                    </div>
                    <button type="button" wire:click="closeModal" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center transition shrink-0 cursor-pointer">
                        <i class="fa-solid fa-xmark text-base sm:text-lg"></i>
                    </button>
                </div>

                <!-- THÔNG TIN ĐƠN GỐC, KHÁCH HÀNG & TÀI KHOẢN NGÂN HÀNG -->
                <div class="bg-[#FAFAF8] rounded-xl sm:rounded-2xl border border-gray-200/80 p-4 sm:p-5 space-y-3.5 text-xs">
                    <h4 class="font-extrabold text-gray-900 uppercase tracking-wider text-[11px] flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-brand-700"></i> Đơn Hàng Gốc & Tài Khoản Nhận Tiền Hoàn
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-1">
                        <div>
                            <span class="text-gray-400 block mb-0.5">Khách hàng phản ánh:</span>
                            <p class="font-bold text-gray-900 text-sm">{{ $selectedComplaint->user?->name ?? $mOrder?->recipient_name ?? '---' }}</p>
                            <p class="text-gray-500 font-mono text-[11px] mt-0.5 flex items-center gap-1">
                                <i class="fa-solid fa-phone text-gray-400 text-[10px]"></i>
                                <span>{{ $mOrder?->recipient_phone ?? $selectedComplaint->user?->phone ?? '---' }}</span>
                            </p>
                            <p class="text-gray-500 text-[11px] mt-0.5 leading-relaxed break-words">
                                {{ $mOrder?->recipient_address ?? '---' }}
                            </p>
                        </div>

                        <div>
                            <span class="text-gray-400 block mb-0.5">Chi tiết đơn hàng gốc:</span>
                            <p class="font-bold text-brand-800 font-mono text-sm">#{{ $mOrder?->order_code ?? '---' }}</p>
                            <p class="text-gray-600 text-[11px]">Tổng thanh toán: <b class="font-mono text-gray-900">{{ number_format($mRefundVal, 0, ',', '.') }}đ</b></p>
                            @php
                                $deliveredTime = $mOrder?->statusHistories?->firstWhere('new_status', 'delivered')?->created_at ?? $mOrder?->updated_at;
                            @endphp
                            <p class="text-gray-600 text-[11px]">Giao lúc: <b class="font-mono text-gray-900">{{ $deliveredTime ? $deliveredTime->format('H:i d/m/Y') : '---' }}</b></p>
                        </div>

                        <div>
                            <span class="text-gray-400 block mb-0.5">Nông sản xuất kho trong đơn:</span>
                            @if($mOrder && $mOrder->items->isNotEmpty())
                                <div class="space-y-1 max-h-24 overflow-y-auto custom-scrollbar pr-1">
                                    @foreach($mOrder->items as $it)
                                        <div class="text-[11px] text-gray-700 flex items-center justify-between">
                                            <span class="font-semibold truncate">{{ $it->product_name }}</span>
                                            <span class="text-gray-400 font-mono shrink-0 ml-1">x{{ $it->quantity }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">Không có dữ liệu</span>
                            @endif
                        </div>
                    </div>

                    <!-- Thẻ thông tin tài khoản ngân hàng của khách -->
                    <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200 text-emerald-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                                <i class="fa-solid fa-building-columns"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Tài khoản nhận tiền hoàn trả</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <b class="font-extrabold text-sm text-gray-900 font-mono">{{ $selectedComplaint->bank_account_number }}</b>
                                    <button type="button" 
                                            x-data 
                                            @click="navigator.clipboard.writeText('{{ $selectedComplaint->bank_account_number }}'); $dispatch('show-toast', {type: 'success', message: 'Đã sao chép STK: {{ $selectedComplaint->bank_account_number }}'})" 
                                            class="text-emerald-700 hover:text-emerald-900 text-xs font-bold underline cursor-pointer" 
                                            title="Sao chép số tài khoản">
                                        <i class="fa-regular fa-copy"></i> Copy
                                    </button>
                                </div>
                                <p class="text-[11px] text-gray-600">Ngân hàng: <b class="text-gray-900">{{ $selectedComplaint->bank_name }}</b> • Chủ TK: <b class="text-gray-900 uppercase">{{ $selectedComplaint->bank_account_name }}</b></p>
                            </div>
                        </div>
                        <div class="sm:text-right">
                            <span class="text-[10px] text-gray-500 block">Số tiền hoàn lại cho khách:</span>
                            <b class="text-base sm:text-lg font-black text-rose-700 font-mono">{{ number_format($mRefundVal, 0, ',', '.') }}đ</b>
                        </div>
                    </div>

                    <!-- Lời nhắn của khách -->
                    <div class="bg-rose-50/70 p-3.5 rounded-xl border border-rose-200/80 text-rose-950 text-xs flex items-start gap-2.5">
                        <i class="fa-solid fa-comment-dots text-rose-600 mt-0.5 shrink-0 text-sm"></i>
                        <div>
                            <span class="font-bold block text-rose-900 mb-0.5">Nội dung phản ánh của khách hàng:</span>
                            <p class="italic leading-relaxed text-rose-900">"{{ $selectedComplaint->reason }}"</p>
                        </div>
                    </div>
                </div>

                <!-- THƯ VIỆN HÌNH ẢNH BẰNG CHỨNG (EVIDENCE GALLERY TỪ COMPLAINT_IMAGES) -->
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <h4 class="font-extrabold text-gray-900 text-xs uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-solid fa-camera text-brand-700"></i> Hình Ảnh Bằng Chứng Khách Tải Lên ({{ $selectedComplaint->images->count() }} ảnh)
                        </h4>
                        <span class="text-[11px] text-gray-400 italic">Nhấp vào ảnh để phóng to kiểm tra vết dập</span>
                    </div>

                    @if($selectedComplaint->images->isNotEmpty())
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($selectedComplaint->images as $img)
                                @php
                                    $iUrl = $img->url;
                                @endphp
                                <button type="button" wire:click="openZoom('{{ $iUrl }}')" class="relative group rounded-xl overflow-hidden border border-gray-200 aspect-square bg-gray-50 cursor-pointer hover:border-brand-500 transition shadow-2xs">
                                    <img src="{{ $iUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1">
                                        <i class="fa-solid fa-magnifying-glass-plus"></i> Xem ảnh
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 bg-gray-50 rounded-xl border border-dashed border-gray-200 text-center text-gray-400 text-xs">
                            Khách hàng không đính kèm hình ảnh bằng chứng
                        </div>
                    @endif
                </div>

                <!-- FORM PHÊ DUYỆT (CHỈ HIỆN KHI ĐANG PENDING) -->
                @if($isPending)
                    <div class="bg-[#FAFAF8] rounded-xl sm:rounded-2xl border border-gray-200/80 p-4 sm:p-5 space-y-4 text-xs">
                        <h4 class="font-extrabold text-gray-900 uppercase tracking-wider text-[11px] flex items-center gap-2">
                            <i class="fa-solid fa-scale-balanced text-brand-700"></i> Phê Duyệt Phương Án Xử Lý (Nhân Viên Sales)
                        </h4>

                        <!-- 2 Lựa Chọn Giải Pháp: Hoàn Tiền Hoặc Từ Chối -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <!-- Lựa chọn 1: Duyệt Hoàn Tiền -->
                            <label class="relative flex flex-col p-4 rounded-xl sm:rounded-2xl border-2 transition shadow-2xs cursor-pointer {{ $resolution_type === 'refund' ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="resolution_type" value="refund" class="absolute top-3.5 right-3.5 text-brand-600 focus:ring-brand-500 cursor-pointer">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm shadow-xs"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                                    <div>
                                        <b class="text-sm font-extrabold text-emerald-950 block">1. Duyệt Hoàn Tiền</b>
                                        <span class="text-[10px] font-semibold text-emerald-800">Chuyển trả tiền lại cho khách</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-500 leading-relaxed mt-1">Chuyển khoản trực tiếp vào số tài khoản ngân hàng khách đã cung cấp ở trên.</p>
                            </label>

                            <!-- Lựa chọn 2: Từ Chối Khiếu Nại -->
                            <label class="relative flex flex-col p-4 rounded-xl sm:rounded-2xl border-2 transition shadow-2xs cursor-pointer {{ $resolution_type === 'reject' ? 'border-rose-500 bg-rose-50/50' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="resolution_type" value="reject" class="absolute top-3.5 right-3.5 text-rose-600 focus:ring-rose-500 cursor-pointer">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center text-sm shadow-xs"><i class="fa-solid fa-ban"></i></span>
                                    <div>
                                        <b class="text-sm font-extrabold text-gray-900 block">2. Từ Chối Khiếu Nại</b>
                                        <span class="text-[10px] font-semibold text-rose-700">Không chấp thuận bồi thường</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-gray-500 leading-relaxed mt-1">Sản phẩm quá thời hạn 24H hoặc ảnh bằng chứng không thể hiện rõ vết dập nát.</p>
                            </label>
                        </div>

                        <!-- Chi tiết Khi chọn Hoàn Tiền (Số tiền cố định = $order->total, không cho sửa tay) -->
                        @if($resolution_type === 'refund')
                            <div class="p-4 rounded-xl bg-white border border-emerald-200 space-y-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Số tiền duyệt hoàn trả (VNĐ):</label>
                                    <div class="flex items-center gap-3">
                                        <input type="text" 
                                               value="{{ number_format($mRefundVal, 0, ',', '.') }}đ" 
                                               readonly 
                                               class="w-full sm:w-72 bg-emerald-50/70 border border-emerald-300 rounded-xl px-3.5 py-2.5 text-base font-mono font-black text-emerald-800 cursor-not-allowed">
                                        <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                            Hoàn 100% đơn hàng
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mt-1">
                                        * Số tiền hoàn được cố định chính xác theo tổng giá trị khách đã thanh toán cho đơn hàng này (bao gồm cả tiền hàng và phí vận chuyển).
                                    </p>
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Ghi chú lưu vết xử lý nội bộ của Sales (`resolution_note`):</label>
                                    <input type="text" 
                                           wire:model="resolution_note" 
                                           placeholder="VD: Đã đối soát ảnh dập nát, hoàn tiền chuyển khoản Vietcombank thành công..." 
                                           class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-3.5 py-2 text-xs outline-none focus:border-brand-500">
                                </div>
                            </div>
                        @else
                            <!-- Chi tiết Khi chọn Từ Chối -->
                            <div class="p-4 rounded-xl bg-white border border-rose-200 space-y-2.5">
                                <label class="block font-bold text-rose-900 mb-1">Lý do từ chối gửi thông báo cho khách <span class="text-red-500">*</span></label>
                                
                                <select wire:model.live="reject_reason" class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl px-3 py-2 text-xs font-semibold outline-none cursor-pointer">
                                    <option value="">-- Chọn lý do mẫu hoặc tự nhập bên dưới --</option>
                                    <option value="Sản phẩm đã nhận quá 24h theo chính sách bồi hoàn nông sản tươi sống của nông trại.">Sản phẩm đã nhận quá 24h theo chính sách bồi hoàn</option>
                                    <option value="Hư hỏng do bảo quản ở nhiệt độ thường quá lâu, không giữ mát tủ lạnh theo hướng dẫn.">Hư hỏng do bảo quản không đúng cách</option>
                                    <option value="Hình ảnh gửi không thể hiện rõ vết dập nát hoặc thiếu tem nhãn lô nông sản GreenFarm.">Hình ảnh không thể hiện rõ vết dập hoặc thiếu tem nhãn</option>
                                </select>

                                <textarea wire:model="reject_reason" rows="2" placeholder="Nội dung gửi thông báo từ chối đến tài khoản khách hàng..." class="w-full bg-[#FAFAF8] border border-gray-200 rounded-xl p-3 text-xs outline-none focus:border-rose-500 resize-none"></textarea>
                            </div>
                        @endif

                    </div>

                    <!-- Nút Hành Động -->
                    <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                        <button type="button" wire:click="closeModal" class="px-5 py-2.5 rounded-xl sm:rounded-2xl border border-gray-200 text-gray-600 font-bold text-xs hover:bg-gray-50 transition cursor-pointer">
                            Đóng
                        </button>
                        <button type="button" 
                                wire:click="confirmResolution" 
                                wire:loading.attr="disabled"
                                class="bg-brand-700 hover:bg-brand-800 text-white font-bold text-xs sm:text-sm px-6 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl shadow-md transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                            <i class="fa-solid fa-circle-check" wire:loading.remove></i>
                            <i class="fa-solid fa-spinner fa-spin" wire:loading></i>
                            <span>Xác Nhận Xử Lý Khiếu Nại</span>
                        </button>
                    </div>

                @else
                    <!-- KHI ĐÃ ĐƯỢC XỬ LÝ (RESOLVED / REJECTED) -->
                    <div class="p-4 rounded-xl sm:rounded-2xl {{ $selectedComplaint->status === 'resolved' ? 'bg-emerald-50/70 border border-emerald-200 text-emerald-950' : 'bg-gray-50 border border-gray-200 text-gray-800' }} text-xs space-y-1.5 font-mono">
                        <div class="flex items-center gap-2 font-bold text-sm">
                            @if($selectedComplaint->status === 'resolved')
                                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                                <span>ĐÃ DUYỆT BỒI HOÀN: {{ number_format($selectedComplaint->refund_amount ?? $mRefundVal, 0, ',', '.') }}đ</span>
                            @else
                                <i class="fa-solid fa-ban text-rose-600"></i>
                                <span>ĐÃ TỪ CHỐI BỒI HOÀN</span>
                            @endif
                        </div>
                        <p class="text-gray-600">
                            Nhân viên xử lý: <b>{{ $selectedComplaint->resolver?->name ?? 'Nhân viên Nông trại' }}</b>
                            • Lúc: <b>{{ $selectedComplaint->updated_at->format('H:i • d/m/Y') }}</b>
                        </p>
                        @if(!empty($selectedComplaint->resolution_note))
                            <p class="text-gray-700 font-sans italic pt-1 border-t border-gray-200/50">
                                Ghi chú phản hồi: "{{ $selectedComplaint->resolution_note }}"
                            </p>
                        @endif
                    </div>

                    <div class="pt-2 border-t border-gray-100 flex justify-end">
                        <button type="button" wire:click="closeModal" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold text-xs hover:bg-gray-50 cursor-pointer">
                            Đóng
                        </button>
                    </div>
                @endif

            </div>
        </div>
    @endif

    <!-- ================= 5. MODAL PHÓNG TO HÌNH ẢNH BẰNG CHỨNG ================= -->
    @if($showZoomModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-xs p-4" wire:click="closeZoom">
            <div class="relative max-w-2xl w-full bg-white rounded-2xl sm:rounded-3xl p-3 sm:p-4 shadow-2xl overflow-hidden" onclick="event.stopPropagation()">
                <div class="flex justify-between items-center pb-2 mb-2 border-b border-gray-100">
                    <span class="text-xs font-bold text-gray-700">Ảnh Bằng Chứng Nông Sản Khách Tải Lên</span>
                    <button type="button" wire:click="closeZoom" class="w-7 h-7 rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <img src="{{ $zoomImageUrl }}" class="w-full max-h-[75vh] object-contain rounded-xl">
            </div>
        </div>
    @endif
</div>
