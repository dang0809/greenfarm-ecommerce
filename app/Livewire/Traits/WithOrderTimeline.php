<?php

namespace App\Livewire\Traits;

use App\Models\Order;

trait WithOrderTimeline
{
    /**
     * Dòng thời gian lịch sử trạng thái đơn hàng (chuẩn hóa dùng chung cho Khách hàng & Nhân viên bán hàng)
     */
    public function getOrderTimeline(?Order $order): array
    {
        if (!$order) {
            return [];
        }

        $isVip = $order->order_type === 'subscription_delivery';
        $batchInfo = ($isVip && $order->deliverySchedule)
            ? " (Đợt {$order->deliverySchedule->delivery_order}/8)"
            : '';

        $events = [];
        $hasVipPending = false;

        foreach ($order->statusHistories as $h) {
            $st   = $h->new_status;
            $old  = $h->old_status;
            $time = $h->created_at;
            $user = $h->changer?->name ?? 'Hệ thống';

            if ($st === 'delivered') {
                $events[] = [
                    'title'   => 'Giao hàng thành công',
                    'desc'    => $isVip ? "Đã giao thành công đợt rau VIP{$batchInfo} đến bạn!" : 'Đơn hàng đã được giao thành công đến bạn! Cảm ơn bạn đã tin chọn nông sản sạch GreenFarm.',
                    'icon'    => 'fa-circle-check',
                    'color'   => 'emerald',
                    'time'    => $time,
                    'changer' => $user,
                    'order'   => 10,
                ];
            } elseif ($st === 'shipped') {
                $events[] = [
                    'title'   => 'Đang giao hàng',
                    'desc'    => 'Shipper đã lấy hàng thành công và đang trên đường giao đơn hàng này tới bạn. Vui lòng chú ý điện thoại nhé!',
                    'icon'    => 'fa-truck-fast',
                    'color'   => 'blue',
                    'time'    => $time,
                    'changer' => $user,
                    'order'   => 5,
                ];
            } elseif ($st === 'processing') {
                $events[] = [
                    'title'   => 'Chờ lấy hàng',
                    'desc'    => $isVip ? "Nông trại đang thu hoạch & đóng gói thùng rau VIP{$batchInfo} theo chuẩn VietGAP, chờ shipper đến lấy hàng." : 'Đơn hàng đã được xác nhận & đang đóng gói, chờ shipper đến lấy hàng.',
                    'icon'    => 'fa-box-open',
                    'color'   => 'amber',
                    'time'    => $time,
                    'changer' => $user,
                    'order'   => 4,
                ];
            } elseif ($st === 'pending') {
                if ($isVip) {
                    if (!$hasVipPending) {
                        $events[] = [
                            'title'   => "Chờ xác nhận đợt giao VIP{$batchInfo}",
                            'desc'    => "Đợt giao rau định kỳ{$batchInfo} đã được hệ thống kích hoạt từ Gói VIP. Hệ thống đang chờ nhân viên Nông trại Đất Lành kiểm tra và xác nhận đơn hàng.",
                            'icon'    => 'fa-crown',
                            'color'   => 'purple',
                            'time'    => $time,
                            'changer' => 'Hệ thống tự động',
                            'order'   => 2,
                        ];
                        $hasVipPending = true;
                    }
                } elseif ($old === 'pending') {
                    $events[] = [
                        'title'   => 'Chờ xác nhận',
                        'desc'    => 'Khách hàng đã thanh toán thành công. Hệ thống đang chờ nhân viên Nông trại Đất Lành kiểm tra và xác nhận đơn hàng.',
                        'icon'    => 'fa-credit-card',
                        'color'   => 'emerald',
                        'time'    => $time,
                        'changer' => $user,
                        'order'   => 2,
                    ];
                } elseif (empty($old)) {
                    $isPaid = ($order->payment?->status === 'success') || (float)$order->total == 0;
                    $events[] = [
                        'title'   => $isPaid ? 'Đơn hàng đã được đặt' : 'Chờ thanh toán',
                        'desc'    => $isPaid
                            ? 'Đơn hàng của bạn đã được hệ thống ghi nhận.'
                            : 'Đơn hàng của bạn đã được hệ thống ghi nhận. Vui lòng hoàn tất thanh toán để nông trại bắt đầu chuẩn bị hàng nhé!',
                        'icon'    => 'fa-file-lines',
                        'color'   => 'gray',
                        'time'    => $time,
                        'changer' => $user,
                        'order'   => 1,
                    ];
                }
            } elseif ($st === 'cancelled') {
                $events[] = [
                    'title'   => 'Đơn hàng đã hủy',
                    'desc'    => $order->cancel_reason ? "Lý do: {$order->cancel_reason}" : 'Đơn hàng đã được hủy.',
                    'icon'    => 'fa-circle-xmark',
                    'color'   => 'red',
                    'time'    => $time,
                    'changer' => $user,
                    'order'   => 20,
                ];
            }
        }

        if ($order->complaint) {
            $c = $order->complaint;
            $cTime = $c->created_at ?? $order->updated_at;
            $events[] = [
                'title'   => 'Khách gửi khiếu nại hoàn tiền',
                'desc'    => 'Nội dung: "' . $c->reason . '"',
                'icon'    => 'fa-triangle-exclamation',
                'color'   => 'amber',
                'time'    => $cTime,
                'changer' => 'Khách hàng',
                'order'   => 25,
            ];
            if ($c->status === 'resolved') {
                $events[] = [
                    'title'   => 'Bồi hoàn tiền thành công',
                    'desc'    => $c->resolution_note ?: 'Nông trại đã chuyển khoản bồi hoàn tiền cho đơn hàng này.',
                    'icon'    => 'fa-hand-holding-dollar',
                    'color'   => 'emerald',
                    'time'    => $c->updated_at ?? $cTime,
                    'changer' => 'Ban quản lý',
                    'order'   => 30,
                ];
            } elseif ($c->status === 'rejected') {
                $events[] = [
                    'title'   => 'Từ chối khiếu nại bồi hoàn',
                    'desc'    => $c->resolution_note ?: 'Nông trại từ chối khiếu nại bồi hoàn cho đơn hàng này.',
                    'icon'    => 'fa-circle-xmark',
                    'color'   => 'red',
                    'time'    => $c->updated_at ?? $cTime,
                    'changer' => 'Ban quản lý',
                    'order'   => 30,
                ];
            }
        }

        usort($events, function ($a, $b) {
            if ($a['time']->equalTo($b['time'])) {
                return $b['order'] <=> $a['order'];
            }
            return $b['time'] <=> $a['time'];
        });

        return $events;
    }
}

