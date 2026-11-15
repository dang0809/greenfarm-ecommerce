<div x-data="{
    confirmingSettings: false,
    switchingTab: null,

    async changeSettingsTab(tab) {
        if (
            this.confirmingSettings ||
            this.switchingTab !== null ||
            tab === this.$wire.activeTab
        ) {
            return;
        }

        this.switchingTab = tab;

        try {
            await this.$wire.switchTab(tab);
        } catch (error) {
            this.$dispatch('swal', {
                icon: 'error',
                title: 'Không thể chuyển tab',
                text: 'Vui lòng kiểm tra kết nối rồi thử lại.',
            });
        } finally {
            this.switchingTab = null;
        }
    },
}" x-on:setting-confirm-state="confirmingSettings = $event.detail.busy"
    class="flex flex-1 min-h-0 flex-col overflow-hidden">

    @php
        $fields = match ($activeTab) {
            'contact' => [
                'farm_name' => [
                    'label' => 'Tên thương hiệu nông trại',
                    'type' => 'text',
                    'maxlength' => 255,
                ],
                'hotline' => [
                    'label' => 'Hotline CSKH / Đặt hàng',
                    'type' => 'text',
                    'maxlength' => 50,
                ],
                'email' => [
                    'label' => 'Email liên hệ chính thức',
                    'type' => 'email',
                    'maxlength' => 255,
                ],
                'farm_latitude' => [
                    'label' => 'Vĩ độ nông trại (Latitude)',
                    'type' => 'number',
                    'min' => -90,
                    'max' => 90,
                    'step' => 'any',
                ],
                'farm_longitude' => [
                    'label' => 'Kinh độ nông trại (Longitude)',
                    'type' => 'number',
                    'min' => -180,
                    'max' => 180,
                    'step' => 'any',
                ],
                'farm_address' => [
                    'label' => 'Địa chỉ nông trại thực tế',
                    'type' => 'text',
                    'maxlength' => 500,
                    'wide' => true,
                ],
            ],

            'shipping' => [
                'max_shipping_distance_km' => [
                    'label' => 'Bán kính giao tối đa từ nông trại (km)',
                    'type' => 'number',
                    'min' => 1,
                    'max' => 500,
                    'step' => 'any',
                    'hint' => 'Đơn vượt quá bán kính này sẽ bị từ chối giao hỏa tốc.',
                ],
            ],

            'loyalty' => [
                'spending_per_point' => [
                    'label' => 'Mức chi tiêu tích 1 điểm (VNĐ)',
                    'type' => 'number',
                    'min' => 1000,
                    'max' => 100000000,
                    'step' => 1,
                    'hint' => 'Ví dụ: chi tiêu 10.000đ tích được 1 điểm.',
                ],
                'value_per_point' => [
                    'label' => 'Giá trị quy đổi 1 điểm (VNĐ)',
                    'type' => 'number',
                    'min' => 1,
                    'max' => 10000000,
                    'step' => 1,
                    'hint' => 'Ví dụ: 1 điểm được giảm 100đ.',
                ],
                'max_point_discount_percent' => [
                    'label' => 'Giới hạn giảm bằng điểm mỗi đơn (%)',
                    'type' => 'number',
                    'min' => 1,
                    'max' => 100,
                    'step' => 'any',
                    'hint' => 'Tỷ lệ giảm tối đa trên tiền hàng.',
                ],
            ],

            'subscription' => [
                'vip_subscription_enabled' => [
                    'label' => 'Mở bán Gói Rau VIP trên website',
                    'type' => 'checkbox',
                    'wide' => true,
                    'hint' => 'Ẩn hoặc hiện mục đăng ký gói VIP đối với khách hàng.',
                ],
                'vip_package_name' => [
                    'label' => 'Tên gói VIP',
                    'type' => 'text',
                    'maxlength' => 255,
                ],
                'vip_package_price' => [
                    'label' => 'Giá gói VIP (VNĐ)',
                    'type' => 'number',
                    'min' => 1000,
                    'max' => 100000000,
                    'step' => 1,
                ],
                'vip_delivery_max_weight' => [
                    'label' => 'Định lượng tối đa mỗi đợt giao (kg)',
                    'type' => 'number',
                    'min' => 0.5,
                    'max' => 50,
                    'step' => 'any',
                    'hint' => 'Tổng cân nặng tối đa khách được chọn cho mỗi đợt giao.',
                ],
            ],
        };
    @endphp

    <header
        class="min-h-[4.5rem] sm:h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex items-center px-4 sm:px-8 shrink-0 z-20 gap-3">

        <button type="button" onclick="toggleSidebar()" aria-label="Mở menu"
            class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>

        <h1 class="text-base sm:text-2xl font-black text-slate-900 truncate">
            Cài Đặt Hệ Thống
        </h1>
    </header>

    <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-5 custom-scrollbar">

        {{-- Thanh tab --}}
        <nav aria-label="Nhóm cấu hình"
            class="flex gap-5 overflow-x-auto bg-white rounded-2xl border border-slate-200 px-5 pt-4 no-scrollbar">

            @foreach ($tabs as $group => $tab)
                <button type="button" wire:key="settings-tab-{{ $group }}"
                    x-on:click="changeSettingsTab('{{ $group }}')"
                    x-bind:disabled="confirmingSettings || switchingTab === '{{ $group }}'"
                    x-bind:aria-busy="(switchingTab === '{{ $group }}').toString()"
                    x-bind:class="switchingTab === '{{ $group }}'
                        ?
                        'opacity-50' :
                        'opacity-100'"
                    wire:loading.attr="disabled" wire:target="requestSave,saveConfirmed,cancelSave"
                    aria-current="{{ $activeTab === $group ? 'page' : 'false' }}"
                    class="pb-3 inline-flex items-center gap-2 whitespace-nowrap text-sm transition cursor-pointer {{ $activeTab === $group ? 'tab-btn-active' : 'tab-btn-inactive' }}">

                    <i x-show="switchingTab !== '{{ $group }}'" class="fa-solid {{ $tab['icon'] }}">
                    </i>

                    <i x-cloak x-show="switchingTab === '{{ $group }}'" class="fa-solid fa-spinner fa-spin">
                    </i>

                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>

        {{-- Form của tab đang chọn --}}
        <form wire:key="settings-form-{{ $activeTab }}" wire:submit="requestSave('{{ $activeTab }}')" novalidate
            x-bind:inert="switchingTab !== null"
            x-on:submit.capture="
        if (confirmingSettings || switchingTab !== null) {
            $event.preventDefault();
            $event.stopImmediatePropagation();
        }
    "
            class="bg-white p-5 sm:p-8 rounded-2xl sm:rounded-3xl border border-slate-200 shadow-soft">

            <fieldset x-bind:disabled="confirmingSettings" wire:loading.attr="disabled"
                wire:target="requestSave,saveConfirmed,cancelSave" class="space-y-6">

                <div class="border-b border-slate-100 pb-3.5">
                    <h2 class="font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid {{ $tabs[$activeTab]['icon'] }} text-brand-700"></i>
                        {{ $tabs[$activeTab]['label'] }}
                    </h2>

                    <p class="text-xs text-slate-500 mt-1">
                        {{ $tabs[$activeTab]['description'] }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs sm:text-sm">
                    @foreach ($fields as $key => $field)
                        <div wire:key="setting-field-{{ $key }}"
                            class="{{ $field['wide'] ?? false ? 'md:col-span-2' : '' }}">

                            @if ($field['type'] === 'checkbox')
                                <label for="setting-{{ $key }}"
                                    class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-brand-50 border border-brand-200 cursor-pointer">

                                    <span class="font-bold text-slate-700">
                                        {{ $field['label'] }}
                                    </span>

                                    <span class="inline-flex items-center gap-2">
                                        <input id="setting-{{ $key }}" type="checkbox"
                                            wire:model.live="{{ $key }}" class="w-5 h-5 accent-brand-700">

                                        <span>
                                            {{ $vip_subscription_enabled ? 'Đang bật' : 'Đang tắt' }}
                                        </span>
                                    </span>
                                </label>
                            @else
                                <label for="setting-{{ $key }}" class="block font-bold text-slate-700 mb-1.5">
                                    {{ $field['label'] }}
                                    <span class="text-red-500">*</span>
                                </label>

                                <input id="setting-{{ $key }}" type="{{ $field['type'] }}"
                                    wire:model.live.debounce.300ms="{{ $key }}"
                                    @if (isset($field['min'])) min="{{ $field['min'] }}" @endif
                                    @if (isset($field['max'])) max="{{ $field['max'] }}" @endif
                                    @if (isset($field['step'])) step="{{ $field['step'] }}" @endif
                                    @if (isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                                    class="w-full bg-[#FAFAF8] border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-900 outline-none focus:border-brand-600 focus:bg-white">
                            @endif

                            @if (isset($field['hint']))
                                <p class="text-[11px] text-slate-500 mt-1">
                                    {{ $field['hint'] }}
                                </p>
                            @endif

                            @error($key)
                                <p class="text-red-500 mt-1" role="alert">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    @endforeach
                </div>

                @error('save')
                    <p class="text-red-500" role="alert">{{ $message }}</p>
                @enderror

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" x-bind:disabled="confirmingSettings" wire:loading.attr="disabled"
                        wire:target="requestSave,saveConfirmed,cancelSave"
                        class="w-full sm:w-auto bg-brand-700 hover:bg-brand-800 text-white font-bold px-7 py-3 rounded-2xl shadow-md inline-flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Lưu {{ $tabs[$activeTab]['label'] }}</span>
                    </button>
                </div>
            </fieldset>
        </form>
    </div>

    {{-- Xác nhận dùng chung cho cả bốn tab --}}
    @script
        <script>
            let confirmationOpen = false;

            function setConfirmationState(busy) {
                $wire.$el.dispatchEvent(
                    new CustomEvent('setting-confirm-state', {
                        detail: {
                            busy
                        },
                    })
                );
            }

            $wire.on('confirm-setting-save', async ({
                title
            }) => {
                if (confirmationOpen) return;

                confirmationOpen = true;
                setConfirmationState(true);

                try {
                    const result = await Swal.fire({
                        icon: 'question',
                        title,
                        text: 'Chọn Hủy để tiếp tục chỉnh sửa dữ liệu đang nhập.',
                        showCancelButton: true,
                        confirmButtonText: 'Xác nhận',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#15803d',
                        cancelButtonColor: '#64748b',
                        reverseButtons: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        heightAuto: false,
                        scrollbarPadding: false,
                    });

                    if (result.isConfirmed) {
                        await $wire.saveConfirmed();
                    } else {
                        await $wire.cancelSave();
                    }
                } catch (error) {
                    if (
                        Number(error?.status) === 422 ||
                        (error?.errors && Object.keys(error.errors).length > 0)
                    ) {
                        return;
                    }

                    await Swal.fire({
                        icon: 'error',
                        title: 'Chưa thể xác nhận kết quả',
                        text: 'Kiểm tra kết nối. Nếu đã xác nhận lưu, hãy tải lại trang để kiểm tra dữ liệu.',
                        confirmButtonColor: '#15803d',
                        heightAuto: false,
                        scrollbarPadding: false,
                    });
                } finally {
                    confirmationOpen = false;
                    setConfirmationState(false);
                }
            });
        </script>
    @endscript
</div>
