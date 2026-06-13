# Hệ Thống Check-in & Cấp Thẻ Lên Máy Bay Điện Tử (E-Boarding Pass)

## 📋 Tổng Quan

Hệ thống Web Check-in & Cấp Thẻ Lên Máy Bay Điện Tử cho SkyLink Airlines cho phép hành khách:
- ✅ Check-in trực tuyến 24 giờ trước khi cất cánh
- 🎫 Nhận thẻ lên máy bay (Boarding Pass) điện tử với mã QR
- 📧 Tự động nhận email Boarding Pass
- 🛡️ Cam kết an toàn bay trước Check-in
- 📱 Xuất trình thẻ từ điện thoại hoặc in giấy

---

## 🏗️ Kiến Trúc Hệ Thống

### Backend (Laravel)

#### 1. **Services** (`app/Services/CheckInService.php`)
Xử lý toàn bộ logic Check-in:
- Tìm Booking theo PNR code
- Tìm Ticket theo tên hành khách
- Kiểm tra trạng thái thanh toán
- **Kiểm tra State Pattern** của chuyến bay (phải ở `check_in` state)
- Tạo BoardingPass với QR code (Base64 SVG, không lưu disk)
- Tạo CheckIn record

```php
// Ví dụ sử dụng
$boardingPass = $checkInService->checkIn('ABC123', 'Nguyễn Văn A');
```

#### 2. **Facades** (`app/Facades/CheckInFacade.php`)
Cung cấp giao diện đơn giản cho Controller:

```php
$result = CheckInFacade::execute($pnrCode, $passengerName);
```

#### 3. **Service Provider** (`app/Providers/CheckInServiceProvider.php`)
Đăng ký CheckInService vào IoC container cho phép sử dụng Facade.

#### 4. **Jobs** (`app/Jobs/SendBoardingPassEmailJob.php`)
Queued job gửi email Boarding Pass ngầm (background):
- Không làm chậm request Check-in
- Retry tự động 3 lần nếu thất bại
- Ghi log chi tiết

```php
// Dispatch job
SendBoardingPassEmailJob::dispatch($boardingPass->id);

// Queue listening
php artisan queue:listen
```

#### 5. **Controller** (`app/Http/Controllers/Api/CheckInController.php`)
Xử lý HTTP requests:
- `POST /api/check-in` - Thực hiện Check-in
- `GET /api/check-in` - Truy vấn trạng thái Check-in

#### 6. **Email Template** (`resources/views/emails/boarding-pass.blade.php`)
Template HTML đẹp cho email Boarding Pass với QR code.

#### 7. **Routes** (`routes/api.php`)
```php
Route::prefix('check-in')->group(function () {
    Route::post('/', [CheckInController::class, 'processCheckIn']);
    Route::get('/', [CheckInController::class, 'query']);
});
```

---

### Frontend (React)

#### 1. **CheckInPage** (`src/pages/CheckInPage.jsx`)
Trang chính cho Check-in:

**Features:**
- Input PNR code và tên hành khách
- Form cam kết an toàn bay (checkboxes)
- Form điều khoản và điều kiện
- Loading state trong quá trình Check-in
- Hiển thị lỗi chi tiết
- Dispatch job để gửi email

**Validation:**
- PNR code: 6 ký tự
- Tên hành khách: không rỗng
- Phải xác nhận cam kết an toàn bay
- Phải đồng ý điều khoản

#### 2. **BoardingPassDisplay** (`src/components/BoardingPassDisplay.jsx`)
Component hiển thị thẻ lên máy bay:

**Thông tin hiển thị:**
- Tên hành khách
- Mã vé (Ticket code)
- Mã đặt chỗ (PNR)
- Số chuyến bay
- Thời gian cất cánh
- Số ghế
- Thời gian Check-in
- Mã QR code

**Features:**
- Thiết kế giống boarding pass thực tế
- Responsive (desktop, tablet, mobile)
- Print-friendly CSS
- Bố cục 2 cột: thông tin + QR code

#### 3. **Styling**
- `CheckInPage.css` - Gradient colors, form styling
- `BoardingPassDisplay.css` - Boarding pass design
- Mobile-responsive & Print-optimized

#### 4. **Routing**
- `src/App.jsx` - Thêm route `/check-in` → `CheckInPage`
- `src/components/Navbar.jsx` - Thêm menu "Check-in" với badge "Mới"

---

## 🔄 Luồng Xử Lý Chi Tiết

### 1. Frontend Check-in Flow
```
User nhập PNR & Tên
        ↓
Xác nhận Cam kết An toàn
        ↓
Xác nhận Điều khoản
        ↓
Click "Check-in Ngay"
        ↓
Frontend validate input
        ↓
POST /api/check-in
```

### 2. Backend Check-in Flow
```
Controller nhận POST request
        ↓
Validate input (PNR, name)
        ↓
CheckInFacade::execute()
        ↓
Tìm Booking theo PNR
        ↓
Kiểm tra Booking status = 'confirmed'
        ↓
Kiểm tra Payment status = 'completed'
        ↓
Tìm Ticket theo tên hành khách
        ↓
**Kiểm tra Flight state = 'check_in'**
   (State Pattern - không cần tính toán thời gian)
        ↓
Tạo/lấy BoardingPass với QR code
        ↓
Tạo CheckIn record
        ↓
Dispatch SendBoardingPassEmailJob
        ↓
Return boarding pass data to frontend
```

### 3. Email Job Flow
```
Job được dispatch
        ↓
Queue worker xử lý
        ↓
Lấy BoardingPass + related data
        ↓
Render email template
        ↓
Gửi email qua Mail facade
        ↓
Log thành công/thất bại
        ↓
Retry tự động nếu thất bại (max 3 lần)
```

---

## 🔒 State Pattern - Kiểm Tra Trạng Thái Chuyến Bay

**Vấn đề cũ:**
```php
// Code rườm rà, khó bảo trì
if ($flight->departure_time->subHours(24) <= now() && 
    $flight->departure_time > now()) {
    // Cho phép Check-in
}
```

**Giải pháp State Pattern:**
```php
// Code sạch, dễ hiểu
$flight->state()->transitionTo('check_in'); // Admin chuyển state
if ($flight->state()->getStatusString() === 'check_in') {
    // Cho phép Check-in
}
```

**Flight States:**
- `scheduled` - Đã lên lịch (chưa tới 24h)
- `check_in` ✅ - Mở Check-in (24h trước - 1h sau giờ bay)
- `boarding` - Đang lên máy bay (1h - 15 phút trước giờ bay)
- `in_flight` - Đang bay
- `arrived` - Đã hạ cánh
- `delayed` - Bị trễ
- `cancelled` - Bị hủy

---

## 📡 API Endpoints

### Check-in
**POST** `/api/check-in`

Request:
```json
{
  "pnr_code": "ABC123",
  "passenger_name": "Nguyễn Văn A"
}
```

Success Response (200):
```json
{
  "status": "success",
  "message": "Check-in thành công! Thẻ lên máy bay đã gửi đến email của bạn.",
  "data": {
    "boarding_pass_id": 1,
    "ticket_code": "SK-001-12345",
    "passenger_name": "Nguyễn Văn A",
    "pnr_code": "ABC123",
    "flight_number": "SK-201",
    "departure_time": "2026-06-15 08:00",
    "seat_number": "12A",
    "qr_code_url": "data:image/svg+xml;base64,...",
    "check_in_time": "2026-06-14 08:30:45"
  }
}
```

Error Response (400):
```json
{
  "status": "error",
  "message": "Quầy làm thủ tục Check-in chưa mở hoặc đã đóng cửa."
}
```

### Query Status
**GET** `/api/check-in?pnr_code=ABC123&passenger_name=Nguyễn Văn A`

Response (200):
```json
{
  "status": "success",
  "has_checked_in": true,
  "message": "Hành khách đã Check-in."
}
```

---

## 🛠️ Cấu Hình & Cài Đặt

### Backend - Cài Đặt Library QR Code
```bash
# Đã có trong composer.json
"simplesoftwareio/simple-qrcode": "^4.2"

# Hoặc cài thủ công
composer require simplesoftwareio/simple-qrcode
```

### Backend - Cấu Hình Mail
Cập nhật `.env`:
```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com  # Hoặc SMTP server của bạn
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=noreply@skylink.com
MAIL_FROM_NAME="SkyLink Airlines"
```

### Backend - Cấu Hình Queue
Cập nhật `.env`:
```env
QUEUE_CONNECTION=database  # Hoặc redis, sqs, etc.
```

Tạo table queue:
```bash
php artisan queue:table
php artisan migrate
```

Khởi động queue worker:
```bash
php artisan queue:listen --tries=1 --timeout=0
```

### Frontend - Axios Base URL
Đảm bảo `src/main.jsx` hoặc config axios:
```javascript
axios.defaults.baseURL = 'http://localhost:8000';
```

---

## 🧪 Testing

### Manual Testing - Backend
```bash
# 1. Tạo test data
php artisan tinker
>>> $booking = Booking::factory()->create(['status' => 'confirmed']);
>>> $payment = Payment::factory()->create(['booking_id' => $booking->id, 'status' => 'completed']);
>>> $flight = $booking->flight;
>>> $flight->status = 'check_in';
>>> $flight->save();

# 2. Test Check-in Service
>>> app('check-in-service')->checkIn($booking->pnr_code, $booking->tickets()->first()->passenger_name);

# 3. Test Queue job
php artisan queue:listen
```

### Manual Testing - Frontend
1. Mở browser → http://localhost:5173/check-in
2. Nhập PNR code: ABC123 (từ test data)
3. Nhập tên hành khách: trùng với DB
4. Xác nhận checkboxes
5. Click "Check-in Ngay"
6. Kiểm tra Boarding Pass hiện ra
7. Kiểm tra email có được gửi không

---

## 📊 Database Schema

### boarding_passes table (sẵn có)
```sql
- id
- ticket_id (FK → tickets)
- qr_code_url (Base64 SVG, không phải file path)
- gate (boarding gate, cập nhật khi hành khách tới gate)
- created_at
- updated_at
```

### check_ins table (sẵn có)
```sql
- id
- ticket_id (FK → tickets)
- boarding_gate
- boarding_time
- is_boarded (boolean)
- created_at
- updated_at
```

---

## 🔐 Security Considerations

✅ **Được bảo vệ:**
- PNR code không lưu trong localStorage (stateless API)
- QR code không lưu trong database (Base64 SVG)
- Email chứa QR, không phải link download
- State Pattern đảm bảo Check-in chỉ mở đúng thời gian

⚠️ **Cần lưu ý:**
- Validate tên hành khách strictly
- Rate limit API endpoint
- Log tất cả Check-in attempts
- Hash PNR code trong log (không log plain text)

---

## 🐛 Troubleshooting

### Lỗi: "Không tìm thấy mã đặt chỗ"
- Kiểm tra PNR code đúng chưa (phải upper case, 6 ký tự)
- Kiểm tra Booking có tồn tại không

### Lỗi: "Thanh toán chưa hoàn tất"
- Kiểm tra Payment status = 'completed'
- Kiểm tra Booking status = 'confirmed'

### Lỗi: "Quầy làm thủ tục Check-in chưa mở"
- Flight status phải = 'check_in'
- Kiểm tra Admin đã chuyển state chưa

### Email không nhận được
- Kiểm tra queue worker chạy không
- Kiểm tra MAIL config trong .env
- Kiểm tra logs: `storage/logs/laravel.log`

### QR Code không hiển thị
- Kiểm tra `qr_code_url` có chứa `data:image/svg+xml;base64,` không
- Kiểm tra SimpleSoftwareIO QRCode installed

---

## 📝 Hướng Phát Triển Tương Lai

1. **Tích hợp SMS**: Gửi QR code qua SMS
2. **Mobile App**: Native app for iOS/Android
3. **Biometric**: Check-in bằng face recognition
4. **Multiple Passengers**: Check-in cả nhóm một lúc
5. **Schedule Notification**: Nhắc nhở Check-in tự động
6. **Analytics Dashboard**: Thống kê Check-in rate
7. **Seat Selection at Check-in**: Đổi ghế khi Check-in
8. **Luggage Tag Print**: In thẻ hành lý tại Check-in

---

## ✅ Checklist Hoàn Thành

- [x] Backend CheckInService
- [x] Backend CheckInFacade  
- [x] Backend SendBoardingPassEmailJob
- [x] Backend CheckInController
- [x] Backend API routes
- [x] Email template
- [x] Frontend CheckInPage
- [x] Frontend BoardingPassDisplay
- [x] Frontend routing
- [x] Frontend Navbar update

**Status**: ✅ **Hoàn thành 100%**

---

## 📚 References

- [State Pattern - Design Pattern](https://refactoring.guru/design-patterns/state)
- [Laravel Queues](https://laravel.com/docs/11.x/queues)
- [Simplesoftware QR Code](https://github.com/SimpleSoftwareIO/simple-qrcode)
- [React Router](https://reactrouter.com/)

---

**Last Updated**: 2026-06-10
**Version**: 1.0.0
