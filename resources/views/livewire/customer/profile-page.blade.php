<div>
    <main class="pt-28 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- CỘT TRÁI (4 CỘT): SIDEBAR MENU -->
            <aside class="lg:col-span-4 space-y-4 lg:sticky lg:top-28">
                
                <!-- Card Thông tin cá nhân ngắn gọn trong Sidebar -->
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <!-- Hiển thị Avatar chuẩn xác -->
                            @if ($newAvatar)
                                <img src="{{ $newAvatar->temporaryUrl() }}" alt="Avatar Preview" class="w-16 h-16 rounded-full object-cover border-2 border-brand-200">
                            @else
                                <img src="{{ $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' .$user->avatar)) : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=200' }}" 
                                    alt="Avatar" class="w-16 h-16 rounded-full object-cover border-2 border-brand-200">
                            @endif

                            <!-- Loading Spinner hiển thị khi đang upload ảnh -->
                            <div wire:loading wire:target="newAvatar" class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-spinner text-white text-xs animate-spin"></i>
                            </div>

                            <!-- Nút chọn ảnh -->
                            <label for="avatar-input" class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-brand-600 text-white flex items-center justify-center text-[10px] cursor-pointer hover:bg-brand-700 shadow-sm" title="Đổi ảnh đại diện">
                                <i class="fa-solid fa-camera"></i>
                            </label>
                            <input type="file" id="avatar-input" wire:model="newAvatar" accept="image/*" class="hidden">
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="text-base font-extrabold text-gray-900 truncate">{{ $user->name }}</h2>
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ $user->email }}</p>
                        </div>
                    </div>
                    @error('newAvatar') 
                        <span class="text-[11px] text-red-500 mt-2 block font-medium">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Menu Sidebar -->
                <nav class="bg-white rounded-3xl border border-gray-100 p-3 shadow-sm space-y-1">
                    <button type="button" wire:click="switchTab('info')" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all text-left cursor-pointer {{ $activeTab === 'info' ? 'text-brand-700 bg-brand-50' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="flex items-center gap-3">
                            <i class="fa-regular fa-user {{ $activeTab === 'info' ? 'text-brand-600' : 'text-gray-400' }} w-4"></i>
                            <span>Thông tin cá nhân</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] {{ $activeTab === 'info' ? 'text-brand-400' : 'text-gray-300' }}"></i>
                    </button>

                    <button type="button" wire:click="switchTab('orders')" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all text-left cursor-pointer {{ $activeTab === 'orders' ? 'text-brand-700 bg-brand-50' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-box-archive {{ $activeTab === 'orders' ? 'text-brand-600' : 'text-gray-400' }} w-4"></i>
                            <span>Đơn hàng của tôi</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] {{ $activeTab === 'orders' ? 'text-brand-400' : 'text-gray-300' }}"></i>
                    </button>

                    <button type="button" wire:click="switchTab('addresses')" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all text-left cursor-pointer {{ $activeTab === 'addresses' ? 'text-brand-700 bg-brand-50' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-location-dot {{ $activeTab === 'addresses' ? 'text-brand-600' : 'text-gray-400' }} w-4"></i>
                            <span>Sổ địa chỉ nhận hàng</span>
                        </span>
                        <span class="text-xs text-gray-400 font-semibold">{{ $addresses->count() }}</span>
                    </button>

                    <button type="button" wire:click="switchTab('points')" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all text-left cursor-pointer {{ $activeTab === 'points' ? 'text-brand-700 bg-brand-50' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-coins {{ $activeTab === 'points' ? 'text-brand-600' : 'text-gray-400' }} w-4"></i>
                            <span>Ví điểm thưởng</span>
                        </span>
                        <span class="text-xs text-brand-600 font-bold">{{ number_format($user->reward_points ?? 0) }}p</span>
                    </button>

                    <button type="button" wire:click="switchTab('password')" 
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs sm:text-sm font-bold transition-all text-left cursor-pointer {{ $activeTab === 'password' ? 'text-brand-700 bg-brand-50' : 'text-gray-700 hover:bg-gray-50' }}">
                        <span class="flex items-center gap-3">
                            <i class="fa-solid fa-lock {{ $activeTab === 'password' ? 'text-brand-600' : 'text-gray-400' }} w-4"></i>
                            <span>Đổi mật khẩu</span>
                        </span>
                        <i class="fa-solid fa-chevron-right text-[10px] {{ $activeTab === 'password' ? 'text-brand-400' : 'text-gray-300' }}"></i>
                    </button>
                </nav>
            </aside>

            <!-- CỘT PHẢI (8 CỘT): NỘI DUNG TỪNG TAB -->
            <div class="lg:col-span-8">
                
                <!-- TAB 1: THÔNG TIN CÁ NHÂN & NGÂN HÀNG HOÀN TIỀN -->
                @if ($activeTab === 'info')
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 shadow-sm space-y-6">
                        <div class="pb-4 border-b border-gray-100">
                            <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">Hồ Sơ Của Tôi</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Cập nhật thông tin cá nhân và tài khoản nhận hoàn tiền</p>
                        </div>

                        <form wire:submit.prevent="updateProfile" class="space-y-6">
                            
                            <!-- 1. THÔNG TIN CÁ NHÂN CƠ BẢN -->
                            <div class="space-y-4">
                                <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-1.5">
                                    <i class="fa-regular fa-id-card text-brand-600"></i>
                                    <span>Thông tin định danh</span>
                                </h3>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Họ và tên</label>
                                        <input type="text" wire:model="name" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                        @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Số điện thoại</label>
                                        <input type="tel" wire:model="phone" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                        @error('phone') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Địa chỉ Email</label>
                                    <input type="email" wire:model="email" class="w-full bg-gray-100 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium text-gray-500 outline-none cursor-not-allowed" readonly title="Email định danh không thể thay đổi">
                                </div>
                            </div>

                            <!-- 2. THÔNG TIN TÀI KHOẢN NGÂN HÀNG HOÀN TIỀN MẶC ĐỊNH -->
                            <div class="pt-5 border-t border-gray-100 space-y-4">
                                <div>
                                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide flex items-center gap-1.5">
                                        <i class="fa-solid fa-building-columns text-brand-600"></i>
                                        <span>Tài khoản ngân hàng nhận lại tiền (Mặc định)</span>
                                    </h3>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Nông Trại Đất Lành dùng tài khoản này để hoàn tiền tự động khi bạn hủy đơn online đã thanh toán.</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <!-- Chọn Ngân hàng -->
                                   <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Ngân hàng / Ví điện tử</label>
                                        <select wire:model="bank_name" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                            <option value="">-- Chọn ngân hàng --</option>
                                            
                                            @forelse ($this->banks as $bank)
                                                <option value="{{ $bank['shortName'] }}">
                                                    {{ $bank['shortName'] }} ({{$bank['code'] }})
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
                                        </select>
                                        @error('bank_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Số tài khoản -->
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1.5">Số tài khoản / SĐT MoMo</label>
                                        <input type="text" wire:model="bank_account_number" placeholder="VD: 0391000123456" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium outline-none focus:bg-white focus:border-brand-500 transition-all">
                                        @error('bank_account_number') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Tên chủ tài khoản -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1.5">Tên chủ tài khoản (Viết hoa không dấu)</label>
                                    <input type="text" wire:model="bank_account_name" placeholder="VD: NGUYEN VAN A" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-medium uppercase outline-none focus:bg-white focus:border-brand-500 transition-all">
                                    @error('bank_account_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Nút Lưu -->
                            <div class="pt-4 border-t border-gray-100 flex justify-end">
                                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold px-6 py-2.5 rounded-xl text-sm shadow-sm transition-all active:scale-95 cursor-pointer flex items-center gap-2">
                                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                                    <span>Lưu thay đổi</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- TAB 2: ĐƠN HÀNG CỦA TÔI -->
                @if ($activeTab === 'orders')
                    <livewire:customer.orders-page />
                @endif

                <!-- TAB 3: SỔ ĐỊA CHỈ NHẬN HÀNG -->
                @if ($activeTab === 'addresses')
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 shadow-sm space-y-6" x-data="vnAddressPicker()">
                        <div class="pb-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">Sổ Địa Chỉ</h2>
                                <p class="text-xs text-gray-400 mt-0.5">Địa chỉ mặc định sẽ tự động chọn khi thanh toán</p>
                            </div>
                            <button type="button" wire:click="openNewAddressForm" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 px-3.5 py-2 rounded-xl transition-all cursor-pointer self-start sm:self-auto">
                                <i class="fa-solid fa-plus text-[11px]"></i>
                                <span>Thêm địa chỉ mới</span>
                            </button>
                        </div>

                        <!-- Form Thêm / Sửa địa chỉ -->
                        @if ($showAddressForm)
                            <form wire:submit.prevent="saveAddress" class="bg-gray-50 p-5 rounded-2xl border border-gray-200 space-y-3">
                                <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide">
                                    {{ $editingAddressId ? 'Chỉnh sửa địa chỉ' : 'Thêm địa chỉ mới' }}
                                </h3>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <input type="text" wire:model="recipient_name" placeholder="Họ và tên người nhận *" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:border-brand-500">
                                        @error('recipient_name') <span class="text-[10px] text-red-500 block mt-0.5">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <input type="tel" wire:model="recipient_phone" placeholder="Số điện thoại người nhận *" class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:border-brand-500">
                                        @error('recipient_phone') <span class="text-[10px] text-red-500 block mt-0.5">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <select id="province-select" 
                                                x-model="selectedProvince" 
                                                x-on:change="onProvinceChange()" 
                                                class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:border-brand-500">
                                            <option value="">-- Chọn Tỉnh / Thành * --</option>
                                            <template x-for="p in provinces" :key="p.code">
                                                <option :value="p.name" :data-id="p.code" x-text="p.name"></option>
                                            </template>
                                        </select>
                                        @error('province') <span class="text-[10px] text-red-500 block mt-0.5">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <select id="district-select" 
                                                x-model="selectedDistrict" 
                                                x-on:change="onDistrictChange()" 
                                                :disabled="!districts.length" 
                                                class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:border-brand-500 disabled:bg-gray-100">
                                            <option value="">-- Chọn Quận / Huyện * --</option>
                                            <template x-for="d in districts" :key="d.code">
                                                <option :value="d.name" :data-id="d.code" x-text="d.name"></option>
                                            </template>
                                        </select>
                                        @error('district') <span class="text-[10px] text-red-500 block mt-0.5">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <select id="ward-select" 
                                                x-model="selectedWard" 
                                                x-on:change="onWardChange()" 
                                                :disabled="!wards.length" 
                                                class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:border-brand-500 disabled:bg-gray-100">
                                            <option value="">-- Chọn Phường / Xã * --</option>
                                            <template x-for="w in wards" :key="w.code">
                                                <option :value="w.name" :data-id="w.code" x-text="w.name"></option>
                                            </template>
                                        </select>
                                        @error('ward') <span class="text-[10px] text-red-500 block mt-0.5">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <!-- Sửa input dùng x-model chuẩn Alpine đồng bộ với geocodeAddress -->
                                    <input type="text" 
                                           x-model="detailAddress" 
                                           x-on:blur="geocodeAddress()" 
                                           placeholder="Địa chỉ chi tiết (Số nhà, tên đường...) *" 
                                           class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-xs outline-none focus:border-brand-500">
                                    @error('address_detail') <span class="text-[10px] text-red-500 block mt-0.5">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex items-center justify-between pt-2">
                                    <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                        <input type="checkbox" wire:model="is_default" class="rounded text-brand-600 focus:ring-brand-500"> Đặt làm địa chỉ mặc định
                                    </label>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="resetAddressForm" class="px-3 py-1.5 text-xs font-semibold text-gray-500 hover:text-gray-700 cursor-pointer">Hủy</button>
                                        <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-brand-600 rounded-xl hover:bg-brand-700 cursor-pointer">
                                            {{ $editingAddressId ? 'Cập nhật' : 'Lưu địa chỉ' }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif

                        <!-- Danh sách địa chỉ đã lưu -->
                        <div class="space-y-4">
                            @forelse ($addresses as $addr)
                                <div class="p-5 rounded-2xl border-2 {{ $addr->is_default ? 'border-brand-500 bg-brand-50/20' : 'border-gray-200 bg-white' }} flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-900">{{ $addr->recipient_name }}</span>
                                            <span class="text-xs text-gray-400 font-mono">{{ $addr->recipient_phone }}</span>
                                            @if ($addr->is_default)
                                                <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full border border-brand-200">Mặc định</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-600 leading-relaxed">
                                            {{ $addr->address_detail }}, {{$addr->ward }}, {{ $addr->district }}, {{$addr->province }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0 self-end sm:self-start">
                                        <button type="button" wire:click="editAddress({{ $addr->id }})" class="text-xs font-bold text-brand-700 hover:underline cursor-pointer">Sửa</button>

                                        @if (!$addr->is_default)
                                            <button type="button" wire:click="setDefaultAddress({{ $addr->id }})" class="text-xs font-semibold text-gray-500 hover:text-brand-600 cursor-pointer">Đặt làm mặc định</button>
                                            <button type="button" wire:click="deleteAddress({{ $addr->id }})" wire:confirm="Xóa địa chỉ này?" class="text-xs font-semibold text-red-500 hover:underline cursor-pointer">Xóa</button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 text-center py-6">Bạn chưa lưu địa chỉ nhận hàng nào.</p>
                            @endforelse
                        </div>
                    </div>
                @endif

                <!-- TAB 4: VÍ ĐIỂM THƯỞNG -->
                @if ($activeTab === 'points')
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 shadow-sm space-y-6">
                        <div class="pb-4 border-b border-gray-100">
                            <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">Ví Điểm Thưởng Đất Lành</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Tích điểm tự động khi mua hàng nông sản</p>
                        </div>

                        <div class="bg-gradient-to-r from-brand-700 to-brand-800 text-white rounded-3xl p-6 sm:p-7 shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-semibold text-brand-200 uppercase tracking-wider block mb-1">Số dư điểm hiện tại</span>
                                <div class="text-3xl sm:text-4xl font-black text-white">
                                    {{ number_format($user->reward_points ?? 0) }} <span class="text-sm font-semibold text-brand-200">điểm</span>
                                </div>
                                <p class="text-xs text-brand-100 mt-2">
                                    Tương đương <b>{{ number_format(($user->reward_points ?? 0) * (int)($loyaltySettings['value_per_point'] ?? 1)) }}đ</b> trừ trực tiếp khi thanh toán đơn hàng.
                                </p>
                            </div>
                            
                            <!-- Quy chế điểm thưởng lấy động từ CSDL -->
                            <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl text-xs space-y-1.5 shrink-0">
                                <span class="font-bold text-white block mb-1">Quy chế điểm thưởng:</span>
                                
                                <p class="text-brand-100 flex items-center gap-1.5">
                                    <span>•</span>
                                    <span>Tích điểm: <b>{{ number_format($loyaltySettings['spending_per_point'] ?? 10000) }}đ</b> chi tiêu = <b>1 điểm</b></span>
                                </p>
                                
                                <p class="text-brand-100 flex items-center gap-1.5">
                                    <span>•</span>
                                    <span>Đổi điểm: <b>1 điểm</b> = <b>{{ number_format($loyaltySettings['value_per_point'] ?? 1) }}đ</b> giảm giá</span>
                                </p>

                                @if(!empty($loyaltySettings['max_point_discount_percent']))
                                    <p class="text-brand-100 flex items-center gap-1.5">
                                        <span>•</span>
                                        <span>Trừ tối đa: <b>{{ $loyaltySettings['max_point_discount_percent'] }}%</b> giá trị đơn</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- TAB 5: ĐỔI MẬT KHẨU -->
                @if ($activeTab === 'password')
                    <div class="bg-white rounded-3xl border border-gray-100 p-6 sm:p-8 shadow-sm space-y-6">
                        <div class="pb-4 border-b border-gray-100">
                            <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">Đổi Mật Khẩu</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Để bảo mật, vui lòng không chia sẻ mật khẩu cho người khác</p>
                        </div>

                        <form wire:submit.prevent="updatePassword" class="space-y-4 max-w-md">
                            <!-- 1. Mật khẩu hiện tại -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Mật khẩu hiện tại</label>
                                <div x-data="{ show: false }" class="relative">
                                    <input :type="show ? 'text' : 'password'" 
                                        wire:model="current_password" 
                                        placeholder="••••••••" 
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-10 py-2.5 text-sm outline-none focus:border-brand-500">
                                    <button type="button" 
                                            @click="show = !show" 
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors p-1">
                                        <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                @error('current_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- 2. Mật khẩu mới -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Mật khẩu mới</label>
                                <div x-data="{ show: false }" class="relative">
                                    <input :type="show ? 'text' : 'password'" 
                                        wire:model="new_password" 
                                        placeholder="Tối thiểu 8 ký tự" 
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-10 py-2.5 text-sm outline-none focus:border-brand-500">
                                    <button type="button" 
                                            @click="show = !show" 
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors p-1">
                                        <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                @error('new_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- 3. Xác nhận mật khẩu mới -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Xác nhận mật khẩu mới</label>
                                <div x-data="{ show: false }" class="relative">
                                    <input :type="show ? 'text' : 'password'" 
                                        wire:model="new_password_confirmation" 
                                        placeholder="Nhập lại mật khẩu mới" 
                                        class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-3.5 pr-10 py-2.5 text-sm outline-none focus:border-brand-500">
                                    <button type="button" 
                                            @click="show = !show" 
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors p-1">
                                        <i class="fa-solid" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                                @error('new_password_confirmation') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold px-6 py-2.5 rounded-xl text-sm shadow-sm transition-all active:scale-95 cursor-pointer">
                                    Cập nhật mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('vnAddressPicker', () => ({
        baseUrl: 'https://provinces.open-api.vn/api',

        provinces: [],
        districts: [],
        wards: [],

        selectedProvince: @entangle('province').live,
        selectedDistrict: @entangle('district').live,
        selectedWard: @entangle('ward').live,
        detailAddress: @entangle('address_detail').live,
        latitude: @entangle('latitude').live,
        longitude: @entangle('longitude').live,

        init() {
            Livewire.on('load-address-dropdowns', async (event) => {
                const info = Array.isArray(event) ? event[0] : event;
                if (!info) return;

                try {
                    if (!this.provinces.length) {
                        await this.loadProvinces();
                    }

                    this.selectedProvince = info.province ?? '';
                    await this.loadDistrictsForSelectedProvince();

                    this.selectedDistrict = info.district ?? '';
                    await this.loadWardsForSelectedDistrict();

                    this.selectedWard = info.ward ?? '';
                    await this.$nextTick();

                    await this.geocodeAddress();
                } catch (error) {
                    console.error('Lỗi nạp địa chỉ:', error);
                }
            });

            this.loadProvinces();
        },

        async loadProvinces() {
            try {
                const response = await fetch(`${this.baseUrl}/p/`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                this.provinces = await response.json();

                if (this.selectedProvince) {
                    await this.syncInitialData();
                }
            } catch (error) {
                console.error('Không thể tải danh sách tỉnh/thành:', error);
            }
        },

        async syncInitialData() {
            const savedDistrict = this.selectedDistrict;
            const savedWard = this.selectedWard;

            await this.loadDistrictsForSelectedProvince();
            this.selectedDistrict = savedDistrict ?? '';

            await this.loadWardsForSelectedDistrict();
            this.selectedWard = savedWard ?? '';
        },

        async loadDistrictsForSelectedProvince() {
            this.districts = [];
            this.wards = [];

            const province = this.provinces.find(item => item.name === this.selectedProvince);
            if (!province) return;

            try {
                const response = await fetch(`${this.baseUrl}/p/${province.code}?depth=2`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const result = await response.json();
                this.districts = result.districts ?? [];
            } catch (error) {
                console.error('Lỗi tải danh sách huyện:', error);
            }
        },

        async loadWardsForSelectedDistrict() {
            this.wards = [];

            const district = this.districts.find(item => item.name === this.selectedDistrict);
            if (!district) return;

            try {
                const response = await fetch(`${this.baseUrl}/d/${district.code}?depth=2`);
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const result = await response.json();
                this.wards = result.wards ?? [];
            } catch (error) {
                console.error('Lỗi tải danh sách xã/phường:', error);
            }
        },

        async onProvinceChange() {
            this.selectedDistrict = '';
            this.selectedWard = '';
            this.districts = [];
            this.wards = [];
            this.clearCoordinates();
            await this.loadDistrictsForSelectedProvince();
        },

        async onDistrictChange() {
            this.selectedWard = '';
            this.wards = [];
            this.clearCoordinates();
            await this.loadWardsForSelectedDistrict();
        },

        async onWardChange() {
            await this.geocodeAddress();
        },

        clearCoordinates() {
            this.latitude = null;
            this.longitude = null;
        },

        cleanPart(str) {
            if (!str) return '';
            return str
                .replace(/^(Tỉnh|Thành phố|TP\.|Huyện|Quận|Thị xã|Xã|Phường|Thị trấn|Thôn|Ấp)\s+/gi, '')
                .trim();
        },

        async geocodeAddress() {
            if (!this.selectedProvince || !this.selectedDistrict || !this.selectedWard) return;

            const cWard = this.cleanPart(this.selectedWard);
            const cDistrict = this.cleanPart(this.selectedDistrict);
            const cProvince = this.cleanPart(this.selectedProvince);
            const detail = (this.detailAddress ?? '').trim();

            const queryFull = [detail, cWard, cDistrict, cProvince, 'Việt Nam'].filter(Boolean).join(', ');
            const queryWard = [cWard, cDistrict, cProvince, 'Việt Nam'].filter(Boolean).join(', ');
            const queryDistrict = [cDistrict, cProvince, 'Việt Nam'].filter(Boolean).join(', ');

            try {
                let data = await this.searchCoordinates(queryFull);

                if (!data.length) {
                    data = await this.searchCoordinates(queryWard);
                }

                if (!data.length) {
                    data = await this.searchCoordinates(queryDistrict);
                }

                if (!data.length) {
                    this.clearCoordinates();
                    return;
                }

                const lat = Number.parseFloat(data[0].lat);
                const lng = Number.parseFloat(data[0].lon);

                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    this.clearCoordinates();
                    return;
                }

                this.latitude = lat;
                this.longitude = lng;

                Livewire.dispatch('address-coordinates-updated', {
                    lat: lat,
                    lng: lng,
                });
            } catch (error) {
                console.error('Lỗi chuyển địa chỉ thành tọa độ:', error);
            }
        },

        async searchCoordinates(address) {
            const url = new URL('https://nominatim.openstreetmap.org/search');
            url.searchParams.set('format', 'jsonv2');
            url.searchParams.set('q', address);
            url.searchParams.set('countrycodes', 'vn');
            url.searchParams.set('limit', '1');

            const response = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) throw new Error(`Nominatim HTTP ${response.status}`);
            const data = await response.json();
            return Array.isArray(data) ? data : [];
        },
    }));
});
</script>