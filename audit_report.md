# BÁO CÁO TỔNG HỢP DỰ ÁN SKYLINK AVIATION

Dưới đây là bản tóm tắt tình trạng dự án hiện tại, các hạng mục cần khắc phục và định hướng chia task cho nhóm 3 thành viên.

---

## 1. CÁC HẠNG MỤC ĐÃ HOÀN THÀNH (Done)

- **UI/UX Trang chủ (Homepage):** Chuyển đổi giao diện Full-Bleed tràn viền, cập nhật thẻ Khuyến mãi dạng Voucher động (Framer Motion), và đưa khu vực Đội bay xuống cuối trang với nền tối (Dark Theme).
- **Hệ thống SkyClub & Khuyến mãi VIP:** Xây dựng trang Dashboard SkyClub. Áp dụng Decorator Pattern ở Backend tự động giảm giá 5% cho khách VIP (mua từ 5 vé thương gia trở lên). Đã fix lỗi đếm sai trạng thái vé.
- **Nâng cấp Đặt vé Đa Hành Khách (Multi-passenger):** Đã đập đi xây lại luồng đặt vé:
  - Cho phép chọn nhiều ghế cùng lúc (`SeatSelection`).
  - Render form nhập liệu tương ứng với số lượng hành khách (`Checkout`).
  - Gộp mảng dữ liệu đẩy lên Backend tạo nhiều vé chung 1 mã PNR.
  - Cập nhật trang Lịch sử vé (`MyBookings`) hiển thị chi tiết tên và ghế của từng người.

---

## 2. CÁC HẠNG MỤC LÀM "NỬA VỜI" (Technical Debt)

> [!WARNING]
> Đây là các phần hệ thống đang "tạm chạy được" nhưng sai về mặt kiến trúc tĩnh hoặc trải nghiệm người dùng. Cần phải khắc phục để đạt điểm tối đa.

| Hạng mục | Đang làm được (Tạm bợ) | Cần làm gì tiếp theo (Khắc phục) |
| :--- | :--- | :--- |
| **Trang Chọn Dịch Vụ** | Đã chọn được dịch vụ, cộng tiền vào tổng bill. | **Chưa hỗ trợ từng người:** Hiện tại mua dịch vụ là áp dụng chung. Cần code lại để User chọn riêng (VD: Người A mua cơm, Người B mua thêm hành lý). Phải sửa DB Table Pivot để nối dịch vụ vào `tickets` thay vì `bookings`. |
| **Logic Hủy Vé (5 phút)** | Gọi hàm xóa vé thủ công mỗi khi có ai tải trang Lịch sử vé. | **Phải dùng Background Job:** Viết Laravel Console Kernel chạy ngầm quét DB mỗi phút, hoặc dùng Database Queue/Redis để tự hủy booking khi hết 5 phút giữ chỗ. |
| **Bộ đếm vé VIP** | Chạy hàm tính toán đếm số lượng trực tiếp trong `User` Model mỗi khi load dữ liệu. | **Tối ưu DB:** Áp dụng Observer Pattern. Tạo cột cứng `business_count` trong bảng Users. Cộng/trừ cứng vào DB mỗi khi vé đổi trạng thái để tránh làm treo Server. |

---

## 3. ĐỀ XUẤT CHỨC NĂNG CƠ BẢN ĐỂ CHIA CHO 3 THÀNH VIÊN

> [!TIP]
> Dưới đây là 3 cụm tính năng độc lập, độ khó vừa phải, rất phù hợp để chia đều cho 3 thành viên trong nhóm phát triển song song mà không sợ bị đụng code (conflict) lẫn nhau.

### Thành viên 1: Quản lý Quản trị viên (Admin Dashboard)
- **Nhiệm vụ:** Xây dựng phần mềm quản lý nội bộ dành cho nhân viên hãng hàng không.
- **Tính năng cần làm:**
  - Viết CRUD Quản lý Chuyến bay (Thêm, Sửa, Xóa chuyến bay, đổi giờ bay).
  - Quản lý Máy bay & Sơ đồ ghế (Khai báo loại máy bay Airbus/Boeing để sinh ra sơ đồ ghế tự động).
  - Thống kê doanh thu cơ bản (Biểu đồ số vé bán ra trong tháng).

### Thành viên 2: Quản lý Trải nghiệm Khách hàng (User Account & Booking Management)
- **Nhiệm vụ:** Hoàn thiện vòng đời trải nghiệm của hành khách sau khi mua vé.
- **Tính năng cần làm:**
  - Hoàn thiện trang Quản lý Hồ sơ (Đổi mật khẩu, tải lên ảnh đại diện).
  - Viết chức năng **Hủy Vé / Hoàn Tiền** (User có thể tự nhấn nút hủy vé nếu vé chưa bay, hệ thống tính toán phí phạt hủy vé theo hạng phổ thông/thương gia).
  - Gửi Email tự động xác nhận Đặt vé thành công kèm mã PNR và hóa đơn điện tử (Dùng Laravel Mailer).

### Thành viên 3: Cổng Thanh Toán & Dịch Vụ Bổ Sung (Checkout & Services)
- **Nhiệm vụ:** Giải quyết các luồng dòng tiền và hoàn thiện các chức năng bán chéo (Cross-sell).
- **Tính năng cần làm:**
  - Giải quyết "Nợ kỹ thuật" của trang Dịch vụ: Thiết kế lại luồng cho phép mỗi hành khách mua gói hành lý / suất ăn riêng biệt.
  - Tích hợp cổng thanh toán Sandbox thật: Cắm API của **VNPay** hoặc **MoMo** vào trang Checkout. Xử lý webhook callback trả về để đổi trạng thái từ `pending` sang `paid` một cách tự động.
