# Tài liệu Phân tích & Thiết kế Kỹ thuật
**Chức năng:** Check-in Online & Cấp Boarding Pass
**Dự án:** SkyLink Airline System

> [!NOTE]
> Tài liệu này được thiết kế dành riêng cho lập trình viên Backend phụ trách phần Check-in. Vui lòng đọc kỹ luồng xử lý và tuân thủ tuyệt đối các chuẩn Design Pattern (Facade & Chain of Responsibility) đã được thống nhất.

---

## 1. Tổng quan Nghiệp vụ (Business Requirements)
- **Mục đích:** Cho phép khách hàng tự làm thủ tục trực tuyến, tiết kiệm thời gian chờ đợi tại quầy sân bay.
- **Điều kiện tiên quyết:**
  - Khách phải nhập đúng **Mã PNR** và **Họ tên**.
  - Đơn đặt chỗ (Booking) phải ở trạng thái **Đã thanh toán (paid)**.
  - Chuyến bay phải đang mở quầy thủ tục (Trạng thái `check_in` - thông thường mở trước 24h).
  - Hành khách chưa từng check-in trước đó.

## 2. Thiết kế Database (Gợi ý Cấu trúc)
Không cần tạo bảng mới, chỉ cần bổ sung vào bảng `tickets` hiện tại:
- Cần tạo 1 file migration: `add_checkin_fields_to_tickets_table`
- Bổ sung các cột:
  - `is_checked_in` (boolean, default: false)
  - `boarding_time` (datetime, nullable) - Giờ ra cửa lên máy bay.
  - `gate` (string, nullable) - Cửa ra máy bay (VD: C12).
  - `qr_code_path` (string, nullable) - Đường dẫn lưu file ảnh QR.

---

## 3. Kiến trúc Design Patterns Bắt buộc

> [!IMPORTANT]
> **Nghiêm cấm** việc nhét toàn bộ logic kiểm tra PNR, Tên, Trạng thái chuyến bay vào trong Controller. Phải chia nhỏ bằng 2 pattern dưới đây.

### A. Chuỗi Trách Nhiệm (Chain of Responsibility)
Dùng để kiểm duyệt các điều kiện ngặt nghèo. Hãy tạo một thư mục `app/Services/CheckIn/Handlers/`:

1. `CheckInHandler` (Abstract / Interface): Chứa hàm `setNext()` và `handle()`.
2. **Các lính gác (Concrete Handlers):**
   - `PaymentHandler`: Truy vấn DB xem Booking của mã PNR này đã `paid` chưa? (Ném lỗi 400 nếu `pending` hoặc `cancelled`).
   - `IdentityHandler`: Quét trong vé của PNR đó xem có cái tên nào khớp với Tên khách nhập không? (Ném lỗi 404 nếu không khớp).
   - `FlightStateHandler`: Lấy chuyến bay ra kiểm tra `if ($flight->state()->getStatusString() !== 'check_in')` (Ném lỗi 403 nếu chưa tới giờ mở quầy).
   - `DuplicationHandler`: Kiểm tra cột `is_checked_in` của vé đó xem có phải là `true` chưa? (Ném lỗi 400 nếu đã check-in rồi).

### B. Mặt Tiền (Facade Pattern)
Tạo class `app/Services/CheckIn/CheckInFacade.php` đóng vai trò nhạc trưởng.
- Facade sẽ lắp ráp chuỗi lính gác ở trên lại với nhau.
- Sau khi chuỗi xác thực báo OK, Facade sẽ gọi hàm Generate QR Code.
- Cuối cùng, cập nhật DB `is_checked_in = true` và trả về kết quả.

---

## 4. Luồng xử lý (Code Flow) dành cho Controller

**File:** `CheckInController.php`

```php
public function processCheckIn(Request $request)
{
    // 1. Validate Input (PNR, Passenger Name)
    $validated = $request->validate([
        'pnr_code' => 'required|string|size:6',
        'passenger_name' => 'required|string',
    ]);

    // 2. Delegate toàn bộ cho Facade xử lý
    try {
        $boardingPass = CheckInFacade::execute(
            $validated['pnr_code'], 
            $validated['passenger_name']
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Check-in thành công!',
            'data' => $boardingPass
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 400);
    }
}
```

## 5. Mẹo tạo QR Code
- Bạn Dev có thể cài đặt thư viện `simplesoftwareio/simple-qrcode`.
- Khi hành khách check-in thành công, hãy gen ra 1 đoạn mã String bao gồm: `PNR + Tên + Chuyến Bay + Số Ghế`.
- Dùng thư viện QrCode mã hóa chuỗi đó thành ảnh Base64 hoặc SVG rồi trả thẳng về cho Frontend hiển thị mà không cần lưu ổ cứng (tránh đầy ổ cứng server).

---
**Chúc Dev hoàn thành xuất sắc tính năng này! Kiến trúc nền móng của đồ án (State Pattern) đã dọn sẵn đường, chỉ cần áp dụng đúng logic là hệ thống sẽ chạy cực mượt.**
