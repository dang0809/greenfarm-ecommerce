<?php

namespace App\Livewire\FarmOwner;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class DashboardManager extends Component
{

    public function boot(): void
    {
        $owner = Auth::id()
            ? User::find(Auth::id())
            : null;

        abort_unless(
            $owner
            && $owner->role === 'farm_owner'
            && $owner->status === 'active',
            403,
            'Bạn không có quyền truy cập khu vực chủ trang trại.'
        );
    }

    public string $period = 'this_month';

    public function updatedPeriod(): void
    {
        if (
            !in_array($this->period, [
                'today',
                '7days',
                'this_month',
                'this_quarter',
                'this_year',
            ], true)
        ) {
            $this->period = 'this_month';
        }

        unset($this->report);

        $this->dispatch(
            'owner-charts-updated',
            charts: $this->report['charts']
        );
    }

    private function range(): array
    {
        $today = CarbonImmutable::today();

        return match ($this->period) {
            'today' => [
                $today,
                $today->addDay(),
                $today->subDay(),
                'hour',
            ],
            '7days' => [
                $today->subDays(6),
                $today->addDay(),
                $today->subDays(13),
                'day',
            ],
            'this_quarter' => [
                $today->startOfQuarter(),
                $today->startOfQuarter()->addQuarter(),
                $today->startOfQuarter()->subQuarter(),
                'month',
            ],
            'this_year' => [
                $today->startOfYear(),
                $today->startOfYear()->addYear(),
                $today->startOfYear()->subYear(),
                'month',
            ],
            default => [
                $today->startOfMonth(),
                $today->startOfMonth()->addMonth(),
                $today->startOfMonth()->subMonth(),
                'day',
            ],
        };
    }

    private function paidPayments(
        CarbonImmutable $start,
        CarbonImmutable $end
    ): Builder {
        return DB::table('payments as pay')
            ->join('orders as o', 'o.id', '=', 'pay.order_id')
            ->where('pay.status', 'success')
            ->where('o.status', '!=', 'cancelled')
            ->whereIn('o.order_type', ['retail', 'subscription'])
            ->where('pay.paid_at', '>=', $start)
            ->where('pay.paid_at', '<', $end);
    }

    #[Computed]
    public function report(): array
    {
        [$start, $end, $previous, $unit] = $this->range();

        $format = match ($unit) {
            'hour' => 'Y-m-d H',
            'month' => 'Y-m',
            default => 'Y-m-d',
        };

        $buckets = [];

        for ($point = $start; $point->lt($end); ) {
            $buckets[$point->format($format)] = [
                'label' => $point->format(match ($unit) {
                    'hour' => 'H:i',
                    'month' => 'm/Y',
                    default => 'd/m',
                }),
                'retail' => 0,
                'vip' => 0,
            ];

            $point = match ($unit) {
                'hour' => $point->addHour(),
                'month' => $point->addMonth(),
                default => $point->addDay(),
            };
        }

        $payments = $this->paidPayments($start, $end)
            ->select('pay.paid_at', 'pay.amount', 'o.order_type')
            ->orderBy('pay.id')
            ->cursor();

        foreach ($payments as $payment) {
            $key = CarbonImmutable::parse($payment->paid_at)->format($format);
            $column = $payment->order_type === 'subscription' ? 'vip' : 'retail';

            if (isset($buckets[$key])) {
                $buckets[$key][$column] += (float) $payment->amount;
            }
        }

        $retail = array_sum(array_column($buckets, 'retail'));
        $vip = array_sum(array_column($buckets, 'vip'));
        $revenue = $retail + $vip;

        $previousRevenue = (float) $this
            ->paidPayments($previous, $start)
            ->sum('pay.amount');

        $deliveredHistory = DB::table('order_status_histories')
            ->select('order_id')
            ->selectRaw('MIN(created_at) AS delivered_at')
            ->where('new_status', 'delivered')
            ->groupBy('order_id');

        $completedOrders = DB::table('orders as o')
            ->joinSub($deliveredHistory, 'd', 'd.order_id', '=', 'o.id')
            ->whereIn('o.order_type', ['retail', 'subscription_delivery'])
            ->where('o.status', 'delivered')
            ->where('d.delivered_at', '>=', $start)
            ->where('d.delivered_at', '<', $end)
            ->count();

        $complaints = Complaint::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);

        // Các khiếu nại phát sinh trong kỳ, hiện đã được duyệt hoàn.
        $refunds = (clone $complaints)
            ->where('status', 'resolved')
            ->where('refund_amount', '>', 0);

        // Giá trị dòng hàng bán lẻ có thanh toán thành công trong kỳ.
        // whereExists tránh nhân đôi dòng hàng khi một đơn có nhiều Payment.
        $items = DB::table('order_items as i')
            ->join('orders as o', 'o.id', '=', 'i.order_id')
            ->join('products as p', 'p.id', '=', 'i.product_id')
            ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
            ->where('o.order_type', 'retail')
            ->where('o.status', '!=', 'cancelled')
            ->whereExists(function ($query) use ($start, $end) {
                $query->selectRaw('1')
                    ->from('payments as pay')
                    ->whereColumn('pay.order_id', 'o.id')
                    ->where('pay.status', 'success')
                    ->where('pay.paid_at', '>=', $start)
                    ->where('pay.paid_at', '<', $end);
            });

        $categoryRows = (clone $items)
            ->selectRaw(
                "COALESCE(c.name, 'Chưa phân loại') AS name, SUM(i.subtotal) AS value"
            )
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('value')
            ->get();

        $categoryTotal = (float) $categoryRows->sum('value');
        $palette = ['#16a34a', '#f59e0b', '#f43f5e', '#6366f1', '#0ea5e9', '#14b8a6'];

        $categories = $categoryRows->values()
            ->map(function ($row, $index) use ($categoryTotal, $palette) {
                return [
                    'name' => $row->name,
                    'value' => (float) $row->value,
                    'percent' => $categoryTotal > 0
                        ? round((float) $row->value / $categoryTotal * 100, 1)
                        : 0,
                    'color' => $palette[$index % count($palette)],
                ];
            })->all();

        $topRows = (clone $items)
            ->select('p.id', 'p.name', 'p.unit', 'c.name as category')
            ->selectRaw('SUM(i.quantity) AS quantity, SUM(i.subtotal) AS value')
            ->groupBy('p.id', 'p.name', 'p.unit', 'c.name')
            ->orderByDesc('value')
            ->limit(5)
            ->get();

        $products = Product::query()
            ->with('coverImage')
            ->withAvg('reviews', 'rating')
            ->whereKey($topRows->pluck('id'))
            ->get()
            ->keyBy('id');

        $top = $topRows->map(function ($row) use ($products) {
            $product = $products->get($row->id);

            return [
                'id' => (int) $row->id,
                'name' => $row->name,
                'category' => $row->category ?? 'Chưa phân loại',
                'unit' => $row->unit,
                'quantity' => (int) $row->quantity,
                'value' => (float) $row->value,
                'image' => $product?->cover_image_url,
                'rating' => $product?->reviews_avg_rating,
            ];
        })->all();

        return [
            'from' => $start->format('d/m/Y'),
            'to' => $end->subDay()->format('d/m/Y'),
            'revenue' => $revenue,
            'vip' => $vip,
            'vip_share' => $revenue > 0 ? round($vip / $revenue * 100, 1) : 0,
            'change' => $previousRevenue > 0
                ? round(($revenue - $previousRevenue) / $previousRevenue * 100, 1)
                : null,
            'orders' => $completedOrders,
            'complaints' => (clone $complaints)->count(),
            'refund_count' => (clone $refunds)->count(),
            'refund_amount' => (float) (clone $refunds)->sum('refund_amount'),
            'reships' => Order::query()
                ->where('order_type', 'compensation')
                ->where('status', '!=', 'cancelled')
                ->where('created_at', '>=', $start)
                ->where('created_at', '<', $end)
                ->count(),
            'categories' => $categories,
            'top' => $top,
            'charts' => [
                'labels' => array_column($buckets, 'label'),
                'retail' => array_column($buckets, 'retail'),
                'vip' => array_column($buckets, 'vip'),
                'category_labels' => array_column($categories, 'name'),
                'category_values' => array_column($categories, 'value'),
                'category_colors' => array_column($categories, 'color'),
            ],
        ];
    }

    public function exportCsv()
    {
        $report = $this->report;

        return response()->streamDownload(function () use ($report) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            $write = static function (array $row) use ($file): void {
                $row = array_map(static function ($value) {
                    return is_string($value)
                        && preg_match('/^[=+\-@\t\r]/u', $value)
                        ? "'" . $value
                        : $value;
                }, $row);

                fputcsv($file, $row, ',', '"', '');
            };

            $write(['Chỉ tiêu', 'Giá trị']);
            $write(['Từ ngày', $report['from']]);
            $write(['Đến ngày', $report['to']]);
            $write(['Doanh thu đã thanh toán', $report['revenue']]);
            $write(['Doanh thu VIP', $report['vip']]);
            $write(['Đơn giao hoàn thành', $report['orders']]);
            $write(['Khiếu nại phát sinh', $report['complaints']]);
            $write(['Khiếu nại trong kỳ đã duyệt hoàn', $report['refund_count']]);
            $write(['Số tiền hoàn đã duyệt', $report['refund_amount']]);
            $write(['Đơn giao bù', $report['reships']]);
            $write([]);
            $write(['Mốc thời gian', 'Thanh toán mua lẻ', 'Thanh toán VIP']);

            foreach ($report['charts']['labels'] as $index => $label) {
                $write([
                    $label,
                    $report['charts']['retail'][$index],
                    $report['charts']['vip'][$index],
                ]);
            }

            $write([]);
            $write(['Sản phẩm bán lẻ', 'Số lượng', 'Đơn vị', 'Giá trị dòng hàng']);

            foreach ($report['top'] as $product) {
                $write([
                    $product['name'],
                    $product['quantity'],
                    $product['unit'],
                    $product['value'],
                ]);
            }

            fclose($file);
        }, 'bao-cao-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        return view('livewire.farm-owner.dashboard-manager', [
            'report' => $this->report,
        ])->layout('layouts.farm-owner.app', [
                    'title' => 'Bảng điều khiển & Báo cáo',
                ]);
    }
}