# Hướng Dẫn Kéo & Đẩy Code (Dành Cho Team Member)
**Dự án:** SkyLink Airline System
**Kiến trúc:** Git Submodule (Repo Mẹ chứa 2 Repo Con)

> [!WARNING]
> Đọc kỹ tài liệu này trước khi đụng vào code. Hệ thống dùng Git Submodule nên việc Clone/Push/Pull sẽ hơi khác so với các dự án bình thường. Nếu làm sai sẽ bị mất code hoặc gây lỗi Conflict cho cả team.

---

## PHẦN 1: KÉO CODE LẦN ĐẦU TIÊN (CLONE)
Để kéo toàn bộ dự án Mẹ cùng với code bên trong của cả Backend và Frontend, bạn **BẮT BUỘC** phải dùng thêm tham số `--recursive` (đệ quy). 

Mở Terminal và chạy lệnh:
```bash
git clone --recursive https://github.com/quangg199/SkyLink-Airline-System.git
```

> [!NOTE] 
> **Lỡ quên chữ `--recursive` thì sao?**
> Đừng xóa thư mục đi tải lại. Hãy chui vào trong thư mục dự án và chạy câu lệnh "chữa cháy" này:
> `git submodule update --init --recursive`

*(Sau khi Clone xong, nhớ vào từng thư mục để chạy `composer install` cho Backend và `npm install` cho Frontend nhé).*

---

## PHẦN 2: QUY TRÌNH SỬA VÀ ĐẨY CODE LÊN (PUSH)

> [!IMPORTANT]
> **Quy tắc Vàng:** Sửa code ở thư mục nào (Backend/Frontend), bắt buộc phải CD (chui) vào đúng thư mục đó để đẩy code lên Github con trước. Sau đó mới ra ngoài thư mục mẹ để đẩy cập nhật. Không bao giờ đứng ở thư mục mẹ để đẩy thẳng code của thư mục con.

**Giả sử bạn vừa sửa code ở thư mục `airline-backend`. Hãy làm theo ĐÚNG 2 CHẶNG sau:**

### Chặng 1: Đẩy Code thật lên Repo Con
Mở Terminal, chui vào thư mục Backend:
```bash
cd airline-backend
git add .
git commit -m "Sửa tính năng XYZ ở Backend"
git push
```

### Chặng 2: Cập nhật Lịch sử (Pointer) cho Repo Mẹ
Sau khi Repo con đã đẩy thành công, lùi ra ngoài thư mục Mẹ và cập nhật con trỏ (Để báo cho Repo Mẹ biết Backend vừa có phiên bản mới):
```bash
cd ..
git add airline-backend
git commit -m "Cập nhật con trỏ Backend lên bản mới nhất"
git push
```

*(Làm tương tự nếu bạn sửa code ở thư mục `airline-frontend`).*

---

## PHẦN 3: KÉO CODE MỚI NHẤT VỀ KHI NGƯỜI KHÁC VỪA PUSH (PULL)

Khi có một thành viên khác trong team vừa Push code mới, bạn muốn cập nhật máy của bạn cho đồng bộ thì làm theo 2 bước sau:

**Bước 1: Kéo bản cập nhật của Repo Mẹ (đứng ở thư mục Mẹ)**
```bash
git pull origin master
```

**Bước 2: Cập nhật code thật cho các Repo Con**
Lệnh `git pull` ở trên chỉ kéo con trỏ về chứ chưa kéo code thật. Để kéo code thật của Backend và Frontend về, chạy tiếp lệnh này (vẫn đứng ở thư mục Mẹ):
```bash
git submodule update --remote --merge
```

---
**Chúc các bạn Code vui vẻ và không bao giờ bị Conflict! 🚀**
