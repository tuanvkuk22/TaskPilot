# Hướng dẫn thao tác AI gợi ý người xử lý công việc

Tính năng **✨ AI Đề xuất** giúp chọn người phù hợp để làm một công việc, dựa trên tên/mô tả task, kỹ năng thành viên và tải việc hiện tại.

---

## 1. Trước khi dùng

1. Đăng nhập TaskPilot.
2. Công việc nên có **tên** (hoặc mô tả). AI đọc nội dung này để gợi ý.
3. Nên chọn **dự án** trước: AI chỉ xét thành viên đang hoạt động của dự án đó.
4. Nếu không có kết quả, kiểm tra dự án đã có thành viên và các tài khoản đang ở trạng thái `active`.

---

## 2. Các trang đã gắn AI

| Trang | Khi nào dùng | Nút AI nằm ở đâu | Sau khi chọn |
|---|---|---|---|
| **Tạo công việc** `/tasks/create` | Phân công ngay lúc tạo issue | Cạnh ô *Người thực hiện* | Ô chọn người được điền sẵn, bạn bấm *Tạo công việc* để lưu |
| **Sửa công việc** `/tasks/{id}/edit` | Đổi người khi chỉnh sửa form | Cạnh ô *Người phụ trách* | Ô chọn được điền, bấm *Cập nhật công việc* để lưu |
| **Chi tiết công việc** `/tasks/{id}` | Đổi người ngay trên issue | Cạnh nhãn *Người thực hiện* (cột phải) | Hệ thống lưu ngay và tải lại trang |
| **Danh sách / Kanban** `/tasks` | Gán nhanh, hoặc gán hàng loạt | Banner; nút **AI gợi ý tất cả** trên thanh lọc; nút **AI** trên từng thẻ | Lưu ngay |
| **Tổng quan / Bảng dự án** `/projects/{id}` | Gán cả danh sách việc chưa giao | Nút **AI gợi ý tất cả** trên header và trên Board | Lưu ngay |
| **Bảng (Board)** `/projects/{id}/board` | Giống Kanban công việc | Nút **AI gợi ý tất cả** + nút **AI** trên thẻ chưa phân công | Lưu ngay |
| **Timeline** `/projects/{id}/timeline` | Gán hàng loạt trên tiến độ | Nút **AI gợi ý tất cả** cạnh nút tạo Epic/Issue | Lưu ngay |
| **Backlog / Sprint** `/projects/{id}/sprints` | Gán issue còn trong backlog | Cạnh chữ *Unassigned* | Lưu ngay và tải lại trang |
| **Tổng quan Home** `/home` | Xem việc chưa giao trong ngày | Khối *AI gợi ý hôm nay* | Lưu ngay và tải lại trang |

Nút **AI** chỉ hiện với công việc **chưa phân công** (trừ trang Tạo / Sửa / Chi tiết — luôn có nút để đổi người).

---

## 3. Thao tác chuẩn (mọi trang đều giống nhau)

### Gợi ý từng việc
1. Nhập hoặc mở công việc cần gán.
2. Bấm **✨ AI Đề xuất** (hoặc nút tím **AI**).
3. Đợi vài giây. Cửa sổ hiện danh sách ứng viên.
4. Bấm **Chọn người này**.

### Gợi ý tất cả (Danh sách, Board, Timeline)
1. Bấm **AI gợi ý tất cả**.
2. Đợi AI chạy xong cho **toàn bộ** công việc chưa phân công của trang hiện tại.
3. Kiểm tra danh sách: mỗi việc đã có người được chọn sẵn (ứng viên % cao nhất). Bỏ tick dòng không muốn gán, hoặc đổi người trong ô chọn.
4. Bấm **Áp dụng tất cả**. Avatar người thực hiện cập nhật ngay trên bảng/timeline.

Để đóng cửa sổ mà không chọn ai, bấm **×** hoặc bấm ra ngoài.

---

## 4. Mẹo dùng cho kết quả tốt hơn

- Viết tên task cụ thể: `Fix memory leak kết nối MySQL` tốt hơn `Sửa bug`.
- Thêm mô tả ngắn (công nghệ, module, mức độ khẩn).
- Chọn đúng **loại issue** (Bug / Task / Story) và **độ ưu tiên**.
- Nếu AI đề xuất người đang quá tải, cân nhắc ứng viên xếp sau — điểm đã tính tải việc nhưng bạn vẫn quyết định cuối.

---

## 5. Lỗi thường gặp

**“Hãy nhập tên hoặc mô tả công việc trước khi dùng AI đề xuất.”**  
→ Điền tên hoặc mô tả rồi bấm lại.

**“Không thể tải gợi ý AI vào lúc này.” / “Chưa tìm thấy ứng viên phù hợp.”**  
→ Kiểm tra dự án đã chọn, thành viên dự án còn active, và máy chủ còn chạy được script Python (`AI/assignee_recommender.py`).

**Bấm AI trên Kanban nhưng bị nhảy sang trang chi tiết**  
→ Bấm đúng nút tím **AI**, không bấm vào phần còn lại của thẻ.

**Đã chọn người trên form Tạo nhưng chưa thấy trên danh sách**  
→ Phải bấm **Tạo công việc** (hoặc **Cập nhật**) thì phân công mới được lưu.

---

## 6. Quyền và phạm vi

- Cần đăng nhập. Trang chi tiết chỉ hiện nút AI khi bạn được phép sửa.
- AI không tự tạo công việc mới; chỉ **gợi ý người xử lý** cho công việc hiện có (hoặc form đang điền).
- Lịch sử gợi ý được ghi lại khi công việc đã có mã (`task_id`). Chọn một ứng viên sẽ đánh dấu gợi ý đó là *đã chấp nhận*.

Tài liệu kỹ thuật (mô hình, dữ liệu huấn luyện, schema): xem `AI_RECOMMENDATION_EXPLANATION.md`.
