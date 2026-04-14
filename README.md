# ✈️ Hệ Thống Quản Lý Và Đặt Vé Máy Bay Trực Tuyến (Flight Booking System)

> **Mô hình kiến trúc:** Web API (Decoupled Architecture) 
> **Công nghệ lõi:** Laravel 11 (Backend) + ReactJS Vite (Frontend) + MySQL
> **Design Pattern:** Service - Repository Pattern

## 📖 1. Giới Thiệu Dự Án
Dự án được xây dựng nhằm tối ưu hóa quy trình đặt chỗ, quản lý chuyến bay và các dịch vụ hàng không đi kèm. Hệ thống tách biệt hoàn toàn Frontend và Backend để đảm bảo tính mở rộng, hiệu suất cao và chuẩn hóa quy trình làm việc nhóm theo mô hình doanh nghiệp.

### 🌟 Tính năng cốt lõi:
- **Người dùng:** Tìm kiếm chuyến bay thời gian thực, chọn chỗ ngồi trực quan (Seat Map), mua dịch vụ bổ trợ, thanh toán và làm thủ tục trực tuyến (Check-in).
- **Quản trị viên:** Quản lý đội bay, lên lịch trình chuyến bay, thiết lập sơ đồ ghế ngồi và thống kê doanh thu.

---

## 🛠️ 2. Quy Trình Cài Đặt Môi Trường (Local Setup)

**Yêu cầu hệ thống:** PHP >= 8.2, Composer, Node.js (v18+), MySQL (v8.0+).

### 2.1. Cài đặt Backend (Laravel)
```bash
# 1. Di chuyển vào thư mục backend
cd backend

# 2. Cài đặt các gói thư viện PHP
composer install

# 3. Khởi tạo file cấu hình môi trường
cp .env.example .env
php artisan key:generate
///////////////////////////////////////////////////////////////////////////////////
Cấu hình Database (Mở file .env và sửa các dòng sau):
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=airline_db  # Tạo database này trong MySQL trước
DB_USERNAME=root
DB_PASSWORD=
/////////////////////////////////////////////////////////////////////////////
Khởi tạo Bảng và Dữ liệu mẫu:
php artisan migrate --seed
/////////////////////////////////////////////////////////////////////////////
Chạy Server Backend:
php artisan serve


Cài đặt Frontend (ReactJS)
# 1. Mở một Terminal mới, di chuyển vào thư mục frontend
cd frontend

# 2. Cài đặt các gói thư viện Node
npm install

# 3. Chạy Server Frontend
npm run dev




Bước 1: Lấy code mới nhất về máy
git checkout main
git pull origin main
Bước 2: Tạo nhánh riêng để làm việc
# Cú pháp: feature/[tên-viết-thường-không-dấu]
# Ví dụ: feature/api-login, feature/crud-flight
git checkout -b feature/ten-chuc-nang-cua-ban
Bước 3: Code, Lưu và Commit trên nhánh cá nhân
git add .
git commit -m "Hoàn thành API [Tên chức năng], đã test qua Postman"
Bước 4: Đẩy code lên GitHub
git push origin feature/ten-chuc-nang-cua-ban
Bước 5: Tạo Pull Request (PR) & Review
