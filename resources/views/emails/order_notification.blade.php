<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'delivered' ? 'Giao hàng thành công' : 'Xác nhận đơn hàng' }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #333333; line-height: 1.6;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f6f8; padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;" cellspacing="0" cellpadding="0">
                    
                    <!-- 1. HEADER LOGO -->
                    <tr>
                        <td style="padding: 24px 32px; background: linear-gradient(135deg, #15803d 0%, #16a34a 100%); text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: 0.5px;">
                                🌱 NÔNG TRẠI ĐẤT LÀNH
                            </h1>
                            <p style="margin: 4px 0 0; color: #dcfce7; font-size: 12px; font-weight: 500;">
                                Nông sản hữu cơ • Thu hoạch sạch tận vườn • Giao hỏa tốc
                            </p>
                        </td>
                    </tr>

                    <!-- 2. BANNER TRẠNG THÁI -->
                    <tr>
                        <td style="padding: 24px 32px 16px;">
                            @if ($type === 'delivered')
                                <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 16px; text-align: center;">
                                    <div style="font-size: 28px; margin-bottom: 4px;">🎉</div>
                                    <h2 style="margin: 0; color: #065f46; font-size: 17px; font-weight: 700;">Giao Hàng Thành Công!</h2>
                                    <p style="margin: 6px 0 0; color: #047857; font-size: 13px;">
                                        Đơn hàng <strong style="color: #065f46;">#{{ $order->order_code }}</strong> đã được bàn giao tận tay quý khách.
                                    </p>
                                </div>
                            @else
                                <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; text-align: center;">
                                    <div style="font-size: 28px; margin-bottom: 4px;">✅</div>
                                    <h2 style="margin: 0; color: #166534; font-size: 17px; font-weight: 700;">Thanh Toán Đơn Hàng Thành Công!</h2>
                                    <p style="margin: 6px 0 0; color: #15803d; font-size: 13px;">
                                        Đơn hàng <strong style="color: #14532d;">#{{ $order->order_code }}</strong> đã được hệ thống xác nhận qua cổng PayOS.
                                    </p>
                                </div>
                            @endif
                        </td>
                    </tr>

                    <!-- 3. LỜI CHÀO & TÓM TẮT -->
                    <tr>
                        <td style="padding: 0 32px 16px;">
                            <p style="font-size: 14px; color: #374151; margin: 0 0 12px;">
                                Xin chào <strong>{{ $order->user->name ?? $order->recipient_name ?? 'Quý khách' }}</strong>,
                            </p>
                            @if ($type === 'delivered')
                                <p style="font-size: 13px; color: #4b5563; margin: 0 0 16px;">
                                    GreenFarm chân thành cảm ơn bạn đã tin chọn nông sản sạch của Đất Lành. Chúc bạn và gia đình có những bữa cơm ngon lành, giàu dinh dưỡng!
                                </p>
                            @else
                                <p style="font-size: 13px; color: #4b5563; margin: 0 0 16px;">
                                    Nông trại đã nhận được khoản thanh toán của bạn và đang tiến hành thu hoạch, đóng thùng xốp để chuẩn bị giao đến bạn trong thời gian sớm nhất.
                                </p>
                            @endif

                            <!-- Khối Thông tin Giao nhận -->
                            <table role="presentation" width="100%" style="background-color: #f9fafb; border: 1px solid #f3f4f6; border-radius: 12px; padding: 14px; font-size: 12px; color: #4b5563;" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding: 4px 8px; width: 40%; font-weight: 600; color: #6b7280;">Mã đơn hàng:</td>
                                    <td style="padding: 4px 8px; font-weight: 700; color: #111827; font-family: monospace;">#{{ $order->order_code }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 8px; font-weight: 600; color: #6b7280;">Thời gian:</td>
                                    <td style="padding: 4px 8px; color: #111827;">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i - d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 8px; font-weight: 600; color: #6b7280;">Người nhận:</td>
                                    <td style="padding: 4px 8px; color: #111827;">{{ $order->recipient_name }} ({{ $order->recipient_phone }})</td>
                                </tr>
                                <tr>
                                    <td style="padding: 4px 8px; font-weight: 600; color: #6b7280;">Địa chỉ giao:</td>
                                    <td style="padding: 4px 8px; color: #111827;">{{ $order->recipient_address }}</td>
                                </tr>
                                @if(!empty($order->notes))
                                <tr>
                                    <td style="padding: 4px 8px; font-weight: 600; color: #6b7280;">Ghi chú:</td>
                                    <td style="padding: 4px 8px; color: #111827; font-style: italic;">"{{ $order->notes }}"</td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    <!-- 4. DANH SÁCH SẢN PHẨM -->
                    <tr>
                        <td style="padding: 8px 32px 16px;">
                            <h3 style="margin: 0 0 10px; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #374151;">
                                Chi Tiết Nông Sản Đặt Mua
                            </h3>
                            <table role="presentation" width="100%" style="border-collapse: collapse; font-size: 12px;" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr style="border-bottom: 2px solid #e5e7eb; background-color: #f9fafb;">
                                        <th align="left" style="padding: 8px; color: #6b7280; font-weight: 600;">Sản phẩm</th>
                                        <th align="center" style="padding: 8px; color: #6b7280; font-weight: 600;">SL</th>
                                        <th align="right" style="padding: 8px; color: #6b7280; font-weight: 600;">Đơn giá</th>
                                        <th align="right" style="padding: 8px; color: #6b7280; font-weight: 600;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($order->items as $item)
                                        <tr style="border-bottom: 1px solid #f3f4f6;">
                                            <td style="padding: 10px 8px; font-weight: 600; color: #1f2937;">
                                                {{ $item->product_name ?? $item->product?->name ?? 'Nông sản hữu cơ' }}
                                            </td>
                                            <td align="center" style="padding: 10px 8px; color: #4b5563;">
                                                {{ $item->quantity }}
                                            </td>
                                            <td align="right" style="padding: 10px 8px; color: #4b5563;">
                                                {{ number_format($item->unit_price, 0, ',', '.') }}đ
                                            </td>
                                            <td align="right" style="padding: 10px 8px; font-weight: 700; color: #111827;">
                                                {{ number_format($item->total_price, 0, ',', '.') }}đ
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" align="center" style="padding: 12px; color: #9ca3af;">
                                                Gói nông sản định kỳ Đất Lành
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <!-- 5. TỔNG CỘNG CHI PHÍ -->
                    <tr>
                        <td style="padding: 0 32px 20px;">
                            <table role="presentation" width="100%" style="font-size: 13px; margin-top: 8px;" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="right" style="padding: 4px 8px; color: #6b7280;">Tạm tính tiền hàng:</td>
                                    <td align="right" style="padding: 4px 8px; width: 120px; font-weight: 600; color: #111827;">
                                        {{ number_format($order->subtotal, 0, ',', '.') }}đ
                                    </td>
                                </tr>
                                @if ($order->discount_points > 0)
                                <tr>
                                    <td align="right" style="padding: 4px 8px; color: #16a34a;">Khấu trừ điểm thưởng:</td>
                                    <td align="right" style="padding: 4px 8px; font-weight: 600; color: #16a34a;">
                                        -{{ number_format($order->discount_points, 0, ',', '.') }}đ
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td align="right" style="padding: 4px 8px; color: #6b7280;">Phí vận chuyển:</td>
                                    <td align="right" style="padding: 4px 8px; font-weight: 600; color: #111827;">
                                        {{ number_format($order->shipping_fee, 0, ',', '.') }}đ
                                    </td>
                                </tr>
                                <tr style="border-top: 2px solid #e5e7eb;">
                                    <td align="right" style="padding: 10px 8px; font-size: 15px; font-weight: 700; color: #111827;">Tổng thanh toán:</td>
                                    <td align="right" style="padding: 10px 8px; font-size: 18px; font-weight: 800; color: #16a34a;">
                                        {{ number_format($order->total, 0, ',', '.') }}đ
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- 6. CAM KẾT BẢO HIỂM 24H -->
                    <tr>
                        <td style="padding: 0 32px 24px;">
                            <div style="background-color: #fefce8; border: 1px dashed #fde047; border-radius: 12px; padding: 14px; font-size: 12px; color: #854d0e; line-height: 1.5;">
                                <strong style="color: #713f12;">🛡️ Cam Kết Bảo Hiểm Tươi Ngon 24H:</strong><br>
                                Nếu nông sản nhận được có dấu hiệu dập nát, hư hỏng do vận chuyển, quý khách có thể gửi yêu cầu khiếu nại trong vòng <strong>24 giờ</strong> để được <strong>bồi hoàn 100%</strong> hoặc giao bù miễn phí.
                            </div>
                        </td>
                    </tr>

                    <!-- 7. NÚT XEM ĐƠN HÀNG TRÊN WEB -->
                    <tr>
                        <td align="center" style="padding: 0 32px 32px;">
                            <a href="{{ url('/ho-so?tab=orders') }}" 
                               style="background-color: #16a34a; color: #ffffff; padding: 14px 28px; text-decoration: none; font-size: 14px; font-weight: 700; border-radius: 12px; display: inline-block; box-shadow: 0 4px 6px -1px rgba(22, 163, 74, 0.2);">
                                Theo Dõi Đơn Hàng Của Bạn →
                            </a>
                        </td>
                    </tr>

                    <!-- 8. FOOTER -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 32px; text-align: center; border-top: 1px solid #e5e7eb; font-size: 11px; color: #9ca3af;">
                            <p style="margin: 0 0 4px; font-weight: 600; color: #6b7280;">NÔNG TRẠI ĐẤT LÀNH - GREENFARM E-COMMERCE</p>
                            <p style="margin: 0 0 4px;">Thôn 3, Xã Đạ Ròn, Huyện Đơn Dương, Tỉnh Lâm Đồng</p>
                            <p style="margin: 0;">Hotline hỗ trợ: <strong style="color: #16a34a;">0987.654.321</strong> (8h00 - 18h00 hàng ngày)</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
