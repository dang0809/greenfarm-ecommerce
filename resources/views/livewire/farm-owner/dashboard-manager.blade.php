<div class="flex flex-1 min-h-0 min-w-0 flex-col overflow-hidden">
    {{-- Header --}}
    <header
        class="min-h-[4.5rem] sm:min-h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 flex flex-wrap items-center justify-between px-4 sm:px-8 py-3 shrink-0 z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <button type="button" onclick="toggleSidebar()" aria-label="Mở menu" aria-controls="sidebar"
                class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition shrink-0">
                <i class="fa-solid fa-bars text-lg" aria-hidden="true"></i>
            </button>

            <h1 class="text-base sm:text-2xl font-black text-slate-900 tracking-tight truncate leading-tight">
                Tổng quan quản trị
            </h1>
        </div>

        <div class="w-full sm:w-auto flex items-center gap-2">
            <span
                class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold font-mono">
                <i class="fa-regular fa-calendar-days" aria-hidden="true"></i>
                {{ $report['from'] }} – {{ $report['to'] }}
            </span>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6 custom-scrollbar pb-28 sm:pb-12">

        {{-- Tác vụ và Bộ lọc --}}
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3.5 sm:p-4 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft">
            <div class="flex flex-wrap items-center gap-2.5">
                <label for="time-filter" class="text-xs font-bold text-slate-600 whitespace-nowrap">
                    <i class="fa-regular fa-calendar-days text-brand-700 mr-1" aria-hidden="true"></i>
                    Kỳ báo cáo:
                </label>

                <div class="relative">
                    <select id="time-filter" wire:model.live="period" wire:loading.attr="disabled" wire:target="period"
                        class="bg-[#FAFAF8] border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3 py-2 pr-7 outline-none focus:border-brand-600 focus:bg-white transition cursor-pointer appearance-none disabled:opacity-60 disabled:cursor-wait">
                        <option value="today">Hôm nay</option>
                        <option value="7days">7 ngày qua</option>
                        <option value="this_month">
                            Tháng này ({{ now()->format('m/Y') }})
                        </option>
                        <option value="this_quarter">
                            Quý này (Q{{ now()->quarter }}/{{ now()->year }})
                        </option>
                        <option value="this_year">
                            Cả năm {{ now()->year }}
                        </option>
                    </select>

                    <i class="fa-solid fa-chevron-down absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"
                        aria-hidden="true"></i>
                </div>
            </div>

            <span wire:loading.delay wire:target="period" role="status" class="text-xs font-semibold text-brand-700">
                <i class="fa-solid fa-spinner fa-spin mr-1" aria-hidden="true"></i>
                Đang cập nhật số liệu...
            </span>
        </div>

        {{-- THỐNG KÊ DOANH THU --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-5">

            {{-- TỔNG DOANH THU --}}
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex flex-col justify-between hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Doanh thu đã
                        thanh toán</span>
                    <div
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-emerald-50 text-brand-700 flex items-center justify-center text-base sm:text-lg shrink-0">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                </div>
                <div>
                    <span
                        class="text-xl sm:text-2xl xl:text-3xl font-black text-slate-900 tracking-tight font-mono block truncate"
                        id="stat-revenue">{{ number_format($report['revenue'], 0, ',', '.') }}đ</span>
                    <div class="flex flex-wrap items-center gap-1.5 mt-2 text-xs font-bold">
                        <span @class([
                            'inline-flex items-center gap-1 border px-2 py-0.5 rounded-full text-[10px] sm:text-[11px]',
                        
                            'bg-slate-50 border-slate-200 text-slate-500' =>
                                $report['change'] === null || $report['change'] == 0,
                        
                            'bg-emerald-50 border-emerald-200 text-emerald-700' =>
                                $report['change'] !== null && $report['change'] > 0,
                        
                            'bg-rose-50 border-rose-200 text-rose-700' =>
                                $report['change'] !== null && $report['change'] < 0,
                        ])>
                            @if ($report['change'] === null)
                                Kỳ trước chưa có doanh thu
                            @else
                                {{ $report['change'] > 0 ? '+' : '' }}{{ number_format($report['change'], 1, ',', '.') }}%
                            @endif
                        </span>

                        @if ($report['change'] !== null)
                            <span class="text-slate-400 font-medium text-[10px] sm:text-[11px]">
                                so với toàn bộ kỳ trước
                            </span>
                        @endif
                    </div>
                    <p class="mt-2 text-[10px] sm:text-[11px] text-slate-400 leading-relaxed">
                        Tổng thanh toán mua lẻ và gói VIP, chưa trừ khoản hoàn tiền.
                    </p>
                </div>
            </div>

            {{-- ĐƠN HÀNG HOÀN THÀNH --}}
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex flex-col justify-between hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Đơn giao hoàn thành
                    </span>
                    <div
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base sm:text-lg shrink-0">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-xl sm:text-2xl xl:text-3xl font-black text-slate-900 tracking-tight font-mono"
                            id="stat-orders">{{ number_format($report['orders'], 0, ',', '.') }}</span>
                        <span class="text-xs text-slate-400 font-bold">đơn</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 mt-2 text-xs font-bold text-blue-700">
                        <span class="text-slate-400 font-medium text-[10px] sm:text-[11px]">
                            Đơn mua lẻ và lượt giao VIP hoàn thành trong kỳ.
                        </span>
                    </div>
                </div>
            </div>

            {{-- DOANH THU GÓI ĐỊNH KỲ --}}
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft flex flex-col justify-between hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] sm:text-xs font-bold text-amber-700 uppercase tracking-wider">Doanh Thu Gói
                        VIP</span>
                    <div
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-base sm:text-lg shrink-0">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                </div>
                <div>
                    <span
                        class="text-xl sm:text-2xl xl:text-3xl font-black text-amber-700 tracking-tight font-mono block truncate"
                        id="stat-sub-revenue">{{ number_format($report['vip'], 0, ',', '.') }}đ</span>
                    <div class="flex flex-wrap items-center gap-1.5 mt-2 text-xs font-bold text-amber-800">
                        <span
                            class="inline-flex items-center gap-1 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full text-[10px] sm:text-[11px]">
                            <i class="fa-solid fa-chart-pie"></i>
                            {{ number_format($report['vip_share'], 1, ',', '.') }}%
                        </span>
                        <span class="text-slate-400 font-medium text-[10px] sm:text-[11px]">
                            trong tổng doanh thu đã thanh toán
                        </span>
                    </div>
                </div>
            </div>

        </div>

        {{-- KHIẾU NẠI VÀ ĐỀN BÙ --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-5">

            {{-- TỔNG ĐƠN KHIẾU NẠI --}}
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-rose-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-rose-600 uppercase tracking-wider block">
                        Khiếu nại phát sinh
                    </span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-2xl sm:text-3xl font-black text-rose-600 font-mono"
                            id="stat-complaint-total">{{ $report['complaints'] }}</span>
                        <span class="text-xs text-slate-400 font-bold">trường hợp</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">
                        Tất cả khiếu nại được tạo trong kỳ báo cáo.
                    </p>
                </div>
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>

            {{-- TỔNG ĐƠN HOÀN TIỀN --}}
            <div
                class="bg-white p-4 sm:p-5 rounded-2xl sm:rounded-3xl border border-amber-200 shadow-soft flex items-center justify-between">
                <div>
                    <span class="text-[11px] sm:text-xs font-bold text-amber-700 uppercase tracking-wider block">Hoàn
                        tiền đã duyệt</span>
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1 mt-1">
                        <span id="stat-refund-count" class="text-2xl sm:text-3xl font-black text-slate-900 font-mono">
                            {{ number_format($report['refund_count'], 0, ',', '.') }}
                        </span>

                        <span class="text-xs text-slate-400 font-bold">
                            khiếu nại
                        </span>
                    </div>

                    <p id="stat-refund-amount" class="mt-1 text-sm font-black text-rose-600 font-mono break-words">
                        {{ number_format($report['refund_amount'], 0, ',', '.') }}đ
                    </p>
                    <p class="text-[10px] sm:text-[11px] text-slate-400 font-medium mt-1">Các khiếu nại phát sinh trong
                        kỳ hiện đã được duyệt hoàn</p>
                </div>
                <div
                    class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg sm:text-xl shrink-0">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>

        </div>

        {{-- 2 biểu đồ --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">

            {{-- BIỂU ĐỒ DOANH THU --}}
            <div
                class="lg:col-span-8 min-w-0 bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="min-w-0">
                        <h3
                            class="font-extrabold text-sm sm:text-base text-slate-900 flex items-start gap-2 leading-snug">
                            <i class="fa-solid fa-chart-column text-brand-700 mt-0.5 shrink-0" aria-hidden="true"></i>

                            <span>Doanh thu đã thanh toán: Mua lẻ và gói VIP</span>
                        </h3>

                        <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5 leading-relaxed">
                            Phân bổ theo thời điểm thanh toán. Đơn vị: triệu đồng.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-semibold shrink-0">
                        <span class="inline-flex items-center gap-1.5 text-brand-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-600"></span>
                            Mua lẻ
                        </span>

                        <span class="inline-flex items-center gap-1.5 text-amber-700">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            Gói VIP
                        </span>
                    </div>
                </div>

                <div class="relative h-60 sm:h-72 lg:h-80 w-full min-w-0">
                    <div class="relative h-full w-full" wire:ignore>
                        <canvas id="revenueTrendChart"></canvas>
                    </div>

                    @if ($report['revenue'] <= 0)
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span
                                class="bg-white/95 border border-slate-100 rounded-xl px-4 py-2 text-xs text-slate-500 shadow-sm">
                                Chưa có doanh thu đã thanh toán trong kỳ này.
                            </span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- BIỂU ĐỒ DANH MỤC --}}
            <div
                class="lg:col-span-4 min-w-0 bg-white p-4 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft space-y-4 flex flex-col justify-between">
                <div class="min-w-0">
                    <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-start gap-2 leading-snug">
                        <i class="fa-solid fa-chart-pie text-amber-600 mt-0.5 shrink-0" aria-hidden="true"></i>

                        <span>Cơ cấu giá trị hàng bán lẻ</span>
                    </h3>

                    <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5 leading-relaxed">
                        Tỷ trọng giá trị dòng hàng của các đơn mua lẻ đã thanh toán trong kỳ.
                    </p>
                </div>

                <div class="relative h-52 sm:h-60 w-full min-w-0">
                    <div class="relative h-full w-full flex items-center justify-center" wire:ignore>
                        <canvas id="categoryShareChart"></canvas>
                    </div>

                    @if (array_sum($report['charts']['category_values']) <= 0)
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <span
                                class="bg-white/95 border border-slate-100 rounded-xl px-4 py-2 text-xs text-slate-500 shadow-sm">
                                Chưa có giá trị bán lẻ trong kỳ này.
                            </span>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
                    @forelse ($report['categories'] as $category)
                        <div wire:key="owner-category-{{ $loop->index }}" class="flex items-center gap-2 min-w-0"
                            title="{{ $category['name'] }}: {{ number_format($category['value'], 0, ',', '.') }}đ">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                style="background-color: {{ $category['color'] }}"></span>

                            <span class="text-slate-600 truncate">
                                {{ $category['name'] }}:
                                <b>{{ number_format($category['percent'], 1, ',', '.') }}%</b>
                            </span>
                        </div>
                    @empty
                        <p class="col-span-2 text-center text-slate-400 py-2">
                            Chưa có dữ liệu bán lẻ trong kỳ này.
                        </p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- BẢNG SẢN PHẨM CÓ DOANH THU CAO --}}
        <div
            class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden space-y-3">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="font-extrabold text-sm sm:text-base text-slate-900 flex items-center gap-2 truncate">
                        <i class="fa-solid fa-medal text-amber-500"></i>
                        <span>Top nông sản bán lẻ theo giá trị dòng hàng</span>
                    </h3>
                    <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5 leading-relaxed">
                        Giá trị dòng hàng của các đơn mua lẻ đã thanh toán trong kỳ.
                        Điểm sao là trung bình tất cả đánh giá hiện có của sản phẩm.
                    </p>
                </div>
                <span
                    class="text-xs font-bold text-brand-700 bg-brand-50 border border-brand-200 px-3 py-1.5 rounded-xl shrink-0">
                    Tối đa 5 sản phẩm
                </span>
            </div>

            <div class="w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse text-xs sm:text-sm whitespace-nowrap">
                    <thead>
                        <tr
                            class="bg-slate-50 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-100">
                            <th class="py-3.5 px-4 pl-5">Tên nông sản</th>
                            <th class="py-3.5 px-4">Nhóm sản phẩm</th>
                            <th class="py-3.5 px-4 text-center">Số lượng</th>
                            <th class="py-3.5 px-4 text-right">Giá trị dòng hàng</th>
                            <th class="py-3.5 px-4 pr-5 text-center">Đánh giá hiện tại</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse ($report['top'] as $product)
                            <tr wire:key="owner-top-product-{{ $product['id'] }}"
                                class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 pl-5">
                                    <a href="{{ route('farm-owner.products.show', ['product' => $product['id']]) }}"
                                        class="flex items-center gap-3">
                                        @if ($product['image'])
                                            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"
                                                loading="lazy"
                                                class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
                                        @else
                                            <div
                                                class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 border border-brand-100 flex items-center justify-center shrink-0">
                                                <i class="fa-solid fa-seedling"></i>
                                            </div>
                                        @endif

                                        <b class="text-slate-900 text-xs sm:text-sm hover:text-brand-700">
                                            {{ $product['name'] }}
                                        </b>
                                    </a>
                                </td>

                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $product['category'] }}
                                </td>

                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                    {{ number_format($product['quantity'], 0, ',', '.') }}
                                    {{ $product['unit'] }}
                                </td>

                                <td class="py-3.5 px-4 text-right font-mono font-extrabold text-brand-800 text-sm">
                                    {{ number_format($product['value'], 0, ',', '.') }}đ
                                </td>

                                <td class="py-3.5 px-4 pr-5 text-center font-mono text-amber-500 font-bold">
                                    @if ($product['rating'] !== null)
                                        {{ number_format((float) $product['rating'], 1, ',', '.') }} ★
                                    @else
                                        <span class="text-slate-400 font-medium">
                                            Chưa có
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">
                                    <i class="fa-solid fa-box-open block text-2xl text-slate-300 mb-2"></i>
                                    Chưa có dữ liệu bán lẻ đã thanh toán trong kỳ này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@script
    <script>
        const root = $wire.$el;
        const initial = @js($report['charts']);

        const money = value => new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
            maximumFractionDigits: 0
        }).format(value);

        const revenueChart = new Chart(root.querySelector('#revenueTrendChart'), {
            type: 'bar',
            data: {
                labels: initial.labels,
                datasets: [{
                        label: 'Đơn mua lẻ',
                        data: initial.retail,
                        backgroundColor: 'rgba(22, 163, 74, 0.9)',
                        borderColor: '#15803d',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: 'Gói định kỳ VIP',
                        data: initial.vip,
                        backgroundColor: 'rgba(245, 158, 11, 0.9)',
                        borderColor: '#d97706',
                        borderWidth: 1,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: context =>
                                context.dataset.label + ': ' + money(context.parsed.y)
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value =>
                                new Intl.NumberFormat('vi-VN', {
                                    maximumFractionDigits: 2
                                }).format(value / 1000000) + ' Tr'
                        }
                    }
                }
            }
        });

        const categoryChart = new Chart(root.querySelector('#categoryShareChart'), {
            type: 'doughnut',
            data: {
                labels: initial.category_labels,
                datasets: [{
                    data: initial.category_values,
                    backgroundColor: initial.category_colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: context => {
                                const value = Number(context.parsed) || 0;

                                const total = context.dataset.data.reduce(
                                    (sum, item) => sum + (Number(item) || 0),
                                    0
                                );

                                const percent = total > 0 ?
                                    value / total * 100 :
                                    0;

                                const formattedPercent = new Intl.NumberFormat('vi-VN', {
                                    minimumFractionDigits: 1,
                                    maximumFractionDigits: 1
                                }).format(percent);

                                return context.label + ': ' + money(value) +
                                    ' (' + formattedPercent + '%)';
                            }
                        }
                    }
                }
            }
        });

        $wire.on('owner-charts-updated', ({
            charts
        }) => {
            revenueChart.data.labels = charts.labels;
            revenueChart.data.datasets[0].data = charts.retail;
            revenueChart.data.datasets[1].data = charts.vip;
            revenueChart.update();

            categoryChart.data.labels = charts.category_labels;
            categoryChart.data.datasets[0].data = charts.category_values;
            categoryChart.data.datasets[0].backgroundColor = charts.category_colors;
            categoryChart.update();
        });

        document.addEventListener('livewire:navigating', () => {
            revenueChart.destroy();
            categoryChart.destroy();
        }, {
            once: true
        });
    </script>
@endscript
