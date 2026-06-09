# Hướng Dẫn Cài Đặt Khởi Chạy Dự Án Lần Đầu Tiên
**Dự án:** SkyLink Airline System

> [!WARNING]
> Kéo code (Clone) về xong **TUYỆT ĐỐI KHÔNG ĐƯỢC CHẠY LỆNH SERVE NGAY LẬP TỨC!** Vì code trên GitHub không bao giờ chứa thư viện (`vendor`, `node_modules`) và cấu hình bảo mật (`.env`). Các bạn phải làm đúng các bước setup dưới đây mới chạy được web.

---

## PHẦN 1: SETUP BACKEND (LARAVEL)

Mở Terminal, di chuyển vào thư mục backend:
```bash
cd airline-backend
```

**Bước 1: Tải bộ thư viện PHP (Rất quan trọng)**
```bash
composer install
```
*(Đợi 1-2 phút để tải toàn bộ thư viện).*

**Bước 2: Tạo file cấu hình môi trường (.env)**
Copy file mẫu thành file cấu hình thật:
```bash
cp .env.example .env
```
*(Nếu dùng Windows CMD, chạy lệnh: `copy .env.example .env`)*

**Bước 3: Tạo mã bảo mật cho Ứng dụng**
```bash
php artisan key:generate
```

**Bước 4: Cấu hình Cơ sở dữ liệu (Database)**
- Mở file `.env` vừa tạo ra, tìm dòng `DB_DATABASE=...` và đổi thành:
  ```env
  DB_DATABASE=airline_db
  ```
- Bật XAMPP (hoặc Laragon) lên, mở phpMyAdmin tạo một database mới tinh tên là `airline_db`.

**Bước 5: Chạy Database Migration & Sinh dữ liệu mẫu (Seeder)**
```bash
php artisan migrate:fresh --seed
```
*(Lệnh này sẽ tự động tạo bảng và nhồi sẵn danh sách Sân bay, Chuyến bay, Ghế ngồi và Tài khoản Admin vào máy bạn).*

**Bước 6: Khởi động Server**
```bash
php artisan serve
```
Backend sẽ chạy ở: `http://127.0.0.1:8000`

---

## PHẦN 2: SETUP FRONTEND (VITE + REACT)

Mở một Terminal mới (giữ nguyên Terminal Backend đang chạy), di chuyển vào thư mục frontend:
```bash
cd airline-frontend
```

**Bước 1: Tải bộ thư viện Javascript (Node_modules)**
```bash
npm install
```
*(Đợi khoảng 1-2 phút).*

**Bước 2: Khởi động Giao diện**
```bash
npm run dev
```

---
**🎉 CHÚC MỪNG BẠN!** 
Lúc này mở link `http://localhost:5173` lên, bạn sẽ thấy giao diện siêu xịn sò của SkyLink hiện ra và đã kết nối thành công với Database. Các lần bật máy tính sau này, bạn không cần làm lại các bước trên nữa, chỉ cần `serve` và `dev` là xong!
