<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đăng ký Gói Rau VIP</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #333333; line-height: 1.6;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f6f8; padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.06); border: 1px solid #e5e7eb;" cellspacing="0" cellpadding="0">

                    <!-- 1. HEADER -->
                    <tr>
                        <td style="padding: 24px 32px; background: linear-gradient(135deg, #15803d 0%, #16a34a 100%); text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: 0.5px;">
                                🌱 NÔNG TRẠI ĐẤT LÀNH
                            </h1>
                            <p style="margin: 4px 0 0; color: #dcfce7; font-size: 12px; font-weight: 500;">
                                Dịch Vụ Giao Rau Hữu Cơ Định Kỳ Tận Nhà
                            </p>
                        </td>
                    </tr>

                    <!-- 2. BANNER XÁC NHẬN -->
                    <tr>
                        <td style="padding: 24px 32px 16px;">
                            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 18px; text-align: center;">
                                <div style="font-size: 26px; margin-bottom: 4px;">👑</div>
                                <h2 style="margin: 0; color: #166534; font-size: 18px; font-weight: 800;">
                                    Đăng Ký Gói VIP Thành Công!
                                </h2>
                                <p style="margin: 6px 0 0; color: #15803d; font-size: 13px;">
                                    Gói rau của bạn đã được thanh toán và kích hoạt qua cổng PayOS.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- 3. LỜI CHÀO & TÓM TẮT -->
                    <tr>
                        <td style="padding: 0 32px 16px;">
                            <p style="font-size: 14px; color: #374151; margin: 0 0 14px;">
                                Xin chào <strong>{{ $subscription->user?->name ?? $subscription->recipient_name ?? 'Quý khách' }}</strong>,
                            </p>
                            <p style="font-size: 13px; color: #4b5563; margin: 0 0 16px; line-height: 1.6;">
                                Nông Trại Đất Lành đã nhận được khoản thanh toán của bạn. Để nông trại chuẩn bị thu hoạch đúng kế hoạch, bạn vui lòng truy cập để chọn <strong>2 ngày giao cố định trong tuần</strong> và <strong>thực đơn rau cho 8 đợt giao</strong>.
                            </p>

                            <!-- BẢNG THÔNG TIN CHI TIẾT (DỮ LIỆU THẬT TỪ HỆ THỐNG) -->
                            <table role="presentation" width="100%" style="background-color: #f9fafb; border: 1px solid #f3f4f6; border-radius: 12px; font-size: 13px; color: #4b5563;" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="padding: 10px 14px; width: 40%; font-weight: 600; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Gói đăng ký:</td>
                                    <td style="padding: 10px 14px; font-weight: 700; color: #111827; border-bottom: 1px solid #e5e7eb;">{{ $packageName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 14px; font-weight: 600; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Mã hóa đơn:</td>
                                    <td style="padding: 10px 14px; font-weight: 700; color: #111827; font-family: monospace; border-bottom: 1px solid #e5e7eb;">#{{ $order->order_code }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 14px; font-weight: 600; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Thời gian:</td>
                                    <td style="padding: 10px 14px; color: #111827; border-bottom: 1px solid #e5e7eb;">{{ \Carbon\Carbon::parse($order->created_at)->format('H:i - d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 14px; font-weight: 600; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Người nhận:</td>
                                    <td style="padding: 10px 14px; color: #111827; border-bottom: 1px solid #e5e7eb;">{{ $subscription->recipient_name }} ({{ $subscription->recipient_phone }})</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 14px; font-weight: 600; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Địa chỉ giao hàng:</td>
                                    <td style="padding: 10px 14px; color: #111827; border-bottom: 1px solid #e5e7eb;">{{ $subscription->recipient_address }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 14px; font-weight: 600; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Quy cách giao:</td>
                                    <td style="padding: 10px 14px; color: #111827; border-bottom: 1px solid #e5e7eb;">
                                        <strong>8 đợt giao</strong> • {{ $deliveryMaxWeight }} kg/đợt (Tổng {{ $totalWeight }} kg/tháng)
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 14px; font-weight: 600; color: #6b7280; border-bottom: 1px solid #e5e7eb;">Phí vận chuyển:</td>
                                    <td style="padding: 10px 14px; font-weight: 700; color: #16a34a; border-bottom: 1px solid #e5e7eb;">Miễn phí 100% (8 đợt)</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 14px; font-weight: 700; color: #111827; font-size: 14px;">Tổng thanh toán:</td>
                                    <td style="padding: 10px 14px; font-weight: 800; color: #16a34a; font-size: 16px;">
                                        {{ number_format($order->total, 0, ',', '.') }}đ
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- 4. NÚT KÊU GỌI HÀNH ĐỘNG (CTA) -->
                    <tr>
                        <td align="center" style="padding: 12px 32px 28px;">
                            <a href="{{ route('customer.vip.setup') }}"
                               style="background-color: #16a34a; color: #ffffff; padding: 14px 32px; text-decoration: none; font-size: 14px; font-weight: 700; border-radius: 12px; display: inline-block; box-shadow: 0 4px 8px rgba(22, 163, 74, 0.25);">
                                Thiết Lập Lịch &amp; Chọn Thực Đơn 8 Đợt →
                            </a>
                            <p style="margin: 10px 0 0; font-size: 12px; color: #6b7280;">
                                Bạn có thể thay đổi thực đơn rau củ trước mỗi đợt giao hàng.
                            </p>
                        </td>
                    </tr>

                    <!-- 5. FOOTER -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 32px; text-align: center; border-top: 1px solid #e5e7eb; font-size: 11px; color: #9ca3af;">
                            <p style="margin: 0 0 4px; font-weight: 600; color: #6b7280;">NÔNG TRẠI ĐẤT LÀNH - GREENFARM E-COMMERCE</p>
                            <p style="margin: 0 0 4px;">Thôn 3, Xã Đạ Ròn, Huyện Đơn Dương, Tỉnh Lâm Đồng</p>
                            <p style="margin: 0;">Hotline CSKH VIP: <strong style="color: #16a34a;">0987.654.321</strong> (8h00 - 18h00 hàng ngày)</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>