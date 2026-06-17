# Báo cáo Chuyên sâu Kiến trúc Backend & Phân tích Design Patterns: SkyLink Airline System
  ---

## 1. Tìm kiếm và Trả về kết quả chuyến bay (Flight Search Engine)
* **Chức năng Backend đã làm:** API `/api/flights` tìm kiếm và tự động sinh dữ liệu chuyến bay nếu trong DB bị trống, đảm bảo tính nhất quán (Deterministic) khi sinh dữ liệu.
* **Design Pattern áp dụng:** `Proxy Pattern` (qua `FlightSearchProxy`) và `Factory / Generator` (qua `FlightGeneratorService`).
* **Luồng chạy của Code (Code Flow):**
  1. **Frontend:** Người dùng bấm tìm kiếm trên file `HomePage.jsx` -> Chuyển sang trang `FlightResults.jsx` -> Gửi API `GET /api/flights`.
  2. **Controller:** API gọi vào `FlightController@index` (`app/Http/Controllers/Api/FlightController.php`).
  3. **Proxy:** Thay vì chọc thẳng vào Database, Controller gọi qua `FlightSearchProxy` (`app/Services/FlightSearchProxy.php`).
  4. **Factory:** Proxy kiểm tra DB nếu chưa có chuyến bay nào trong ngày đó, nó sẽ gọi `FlightGeneratorService` (Factory) để tự động sinh ra dữ liệu mẫu.
  5. **Trả kết quả:** Proxy ném dữ liệu về lại Controller -> Trả về JSON cho Frontend.
* **Giải quyết bài toán (Problem-First):** Controller không được phép phình to để chứa các logic kiểm tra Database và tự động tạo chuyến bay giả lập.
* **Ưu điểm:**
  - Tách bạch hoàn toàn trách nhiệm (SRP). Controller trở nên rất mỏng (skinny).
  - Proxy đứng ra làm "Bảo vệ", dễ dàng gắn thêm Redis Cache vào sau này mà không đập đi viết lại code tìm kiếm.
* **Nhược điểm:**
  - Số lượng file và class tăng lên (phải có thêm Interface và class Proxy). Các lập trình viên mới vào dự án có thể gặp khó khăn khi dò tìm luồng dữ liệu thực sự được xử lý ở đâu (vì Controller chỉ trỏ tới Proxy).

## 2. Tính toán Giá vé Động (Dynamic Pricing Engine)
* **Chức năng Backend đã làm:** Hệ thống tính toán giá vé thay đổi linh hoạt theo loại hình vé (Một chiều / Khứ hồi) và áp dụng giảm giá tự động nếu khách là VIP.
* **Design Pattern áp dụng:** `Strategy Pattern`, `Factory Method`, và `Decorator Pattern`.
* **Luồng chạy của Code (Code Flow):**
  1. **Tạo vé:** Quá trình tính giá bắt đầu khi hệ thống cần chốt tổng tiền.
  2. **Factory:** Gọi `PricingStrategyFactory::resolve($loai_ve)` (`app/Services/Pricing/PricingStrategyFactory.php`) để lấy công thức tính giá. Factory tự động chọn `OneWayPricingStrategy.php` hoặc `RoundTripPricingStrategy.php`.
  3. **Strategy:** Strategy bắt đầu tính toán giá gốc nhân với hệ số.
  4. **Decorator:** Nếu người dùng đang đăng nhập là hạng VIP, Strategy gốc sẽ được "bọc" (Wrap) vào trong `VipPricingDecorator.php` để giảm thêm 10% tổng tiền mà không cần sửa code cũ.
* **Giải quyết bài toán (Problem-First):** Tránh viết các câu lệnh `if...else if...else` khổng lồ trong hàm tính tiền khi logic giá ngày càng phức tạp (khuyến mãi, lễ tết, VIP).
* **Ưu điểm:**
  - Tuân thủ Tuyệt đối **OCP (Open/Closed Principle)**. Thêm chiến lược giá mới chỉ cần tạo file mới.
  - `Decorator Pattern` cho phép "bọc" các lớp giảm giá chồng lên nhau một cách linh hoạt (VD: Giá Khứ hồi + Giảm VIP + Giảm Sinh nhật) rất dễ dàng.
* **Nhược điểm:**
  - `Decorator Pattern` tạo ra một chuỗi các object bọc lấy nhau. Khi debug xem giá cuối cùng được tính ra sao, dev phải step-into (F11) qua rất nhiều tầng class nhỏ lẻ.

## 3. Tích hợp Thanh toán (Payment Gateway Integration)
* **Chức năng Backend đã làm:** Xử lý luồng thanh toán Momo và VNPay, bảo vệ Database bằng Transaction, tích hợp logic khóa chống thanh toán trùng lặp. Đã hoàn thiện 100%.
* **Design Pattern áp dụng:** `Adapter Pattern` kết hợp `Simple Factory`.
* **Luồng chạy của Code (Code Flow):**
  1. **Frontend:** Người dùng bấm "Thanh toán ngay" ở trang `Checkout.jsx` -> Gửi API `POST /api/payments`.
  2. **Controller:** Route điều hướng vào `PaymentController@store` (`app/Http/Controllers/Api/PaymentController.php`).
  3. **Factory:** Controller gọi `PaymentFactory::create($gateway)` (`app/Services/Payment/PaymentFactory.php`).
  4. **Adapter:** Factory trả về một Adapter tương ứng (ví dụ: `MockMomoAdapter.php` hoặc `MockVnPayAdapter.php`). Tất cả các Adapter này đều dùng chung Interface `PaymentGatewayInterface.php`.
  5. **Xử lý:** Adapter dịch các thông số nội bộ thành chuỗi JSON chuẩn của từng ngân hàng và trả về link thanh toán.
* **Giải quyết bài toán (Problem-First):** Hệ thống cần giao tiếp với bên thứ ba (Momo, VNPay) có cấu trúc Data JSON và chuẩn mã hóa khác nhau, nhưng không được dính chặt (tight-coupling) logic này vào Controller.
* **Ưu điểm:**
  - API gọi thanh toán hoàn toàn độc lập. Controller không hề biết nó đang gọi Momo hay VNPay, nó chỉ biết đang gọi chung một `PaymentGatewayInterface`.
  - Có thể dễ dàng gắn thêm cổng Stripe hay ZaloPay trong tương lai chỉ bằng cách tạo 1 class Adapter mới.
* **Nhược điểm:**
  - Đôi khi cấu trúc chung của Interface không thể bao quát hết các tham số đặc thù của một cổng thanh toán kỳ lạ nào đó, buộc phải nhét thêm các mảng tùy chọn (`options array`), làm giảm đi tính định kiểu chặt chẽ (type-safety) của PHP.

## 4. Quản lý Đặt chỗ & Hủy ghế tự động (Booking Orchestration)
* **Chức năng Backend đã làm:** Khóa ghế 15 phút an toàn (dùng Cache), tự động dọn dẹp (Passive Cleanup) các vé quá hạn 15 phút mà không cần cài đặt Background Worker (Cronjob).
* **Kiến trúc áp dụng:** Giải pháp **Passive Cleanup** & **Optimistic Locking**.
* **Luồng chạy của Code (Code Flow):**
  1. **Dọn rác thụ động (Passive Cleanup):** Khi có khách hàng vào đặt vé gọi API lấy ghế trống (`FlightController.php`), hệ thống chèn một câu truy vấn ngầm để hủy (`status = cancelled`) toàn bộ các vé `pending` mà `expires_at` đã trễ quá 15 phút. Ghế lập tức được nhả ra.
  2. **Khóa chống trùng (Locking):** Khi khách bấm thanh toán (`PaymentController.php`), hệ thống bật Transaction `DB::beginTransaction()` và gọi `lockForUpdate()`. Dòng dữ liệu chiếc ghế đó sẽ bị khóa cứng ở cấp độ Database, ngăn chặn tuyệt đối tình trạng 2 người cùng mua 1 ghế trong cùng một phần nghìn giây.
* **Giải quyết bài toán (Problem-First):** Xóa bỏ các vé rác giữ chỗ quá hạn mà không làm nặng hệ thống bởi các hàng đợi (Queues) và Job lặp lại phức tạp.
* **Ưu điểm:**
  - Cực kỳ nhẹ máy, hệ thống tự làm sạch dữ liệu cũ mỗi khi có ai đó truy vấn danh sách vé trống (Lazy Evaluation). 
  - Giải quyết triệt để lỗi đua lệnh (Race Condition) bằng `lockForUpdate()` của Database.
* **Nhược điểm:**
  - Vì là dọn dẹp "Thụ động", dữ liệu rác vẫn nằm yên trong Database cho đến khi có request gọi tới nó. Không phù hợp với các hệ thống vé máy bay siêu lớn cần Real-time xả ghế chính xác tự động tới từng mili-giây.

## 5. Quản lý Vòng đời Chuyến bay (Flight Lifecycle)
* **Chức năng Backend đã làm:** Ngăn chặn việc nhảy bước trạng thái chuyến bay (VD: ép cất cánh khi chưa mở quầy check-in). Đã hoàn thiện 100%.
* **Design Pattern áp dụng:** `State Pattern`.
* **Luồng chạy của Code (Code Flow):**
  1. **Model:** Mỗi khi thay đổi trạng thái máy bay, gọi hàm `$flight->transitionTo(new CheckInState())` trên `Flight.php` Model.
  2. **State Object:** Laravel tự khởi tạo class `CheckInState.php` (`app/States/Flight/CheckInState.php`). 
  3. **Validate:** Tại đây, class State tự động kiểm tra xem máy bay ĐANG ở trạng thái `Scheduled` thì mới cho phép lên `CheckIn`. Nếu máy bay đang ở `Boarding` mà bắt lùi về `CheckIn`, nó sẽ ném lỗi (Exception) ngay lập tức để bảo vệ luồng bay.
* **Giải quyết bài toán (Problem-First):** Thay thế vô số câu lệnh `if ($status == 'Scheduled')` rải rác khắp nơi bằng các quy tắc chuyển đổi khép kín, ngăn chặn bug nghiêm trọng về quy trình bay.
* **Ưu điểm:**
  - Ràng buộc cực kỳ chặt chẽ luật kinh doanh. Lỗi nhảy cóc trạng thái là bất khả thi ở tầng mã nguồn.
  - Cột `status` trong DB vẫn giữ nguyên cấu trúc cũ, đảm bảo Backward Compatibility (tương thích ngược) không làm hỏng dữ liệu.
* **Nhược điểm:**
  - Việc phải tạo ra tới 7 Class độc lập (mỗi trạng thái 1 class) cho một thuộc tính duy nhất làm hệ thống bị dư thừa file (Verbose). Đối với các dự án siêu nhỏ thì đây được coi là Over-engineering (Làm quá mức cần thiết).

---

