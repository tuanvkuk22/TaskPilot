# ĐẶC TẢ GIAO DIỆN LẬP TRÌNH ỨNG DỤNG (API SPECIFICATION & CONTRACTS)

```text
Document ID:     TP-API-SPEC-001
Classification:  Internal & Partner Integration
Version:         2.0.0
Author:          API Architecture Team
Status:          Active
Base URL:        https://taskpilot.yourdomain.com/api
Date:            2026-10-14
```

---

## 1. QUY CHUẨN THIẾT KẾ API CHUNG (API GUIDELINES)

* **Chuẩn giao thức**: RESTful over HTTPS, định dạng dữ liệu truyền thông qua `application/json`.
* **Xác thực (Authentication)**: Xác thực qua Session Cookie (cho Web Client) hoặc Bearer API Token (cho Mobile/Third-party).
* **Bảo vệ CSRF**: Tất cả các yêu cầu `POST`, `PUT`, `DELETE` từ trình duyệt bắt buộc gửi header `X-CSRF-TOKEN`.

### Cấu trúc Phản hồi Chuẩn (Response Envelope):

```json
{
  "success": true,
  "code": 200,
  "message": "Thao tác thành công",
  "data": {},
  "meta": {
    "page": 1,
    "per_page": 15,
    "total": 120
  }
}
```

Trường hợp lỗi:
```json
{
  "success": false,
  "code": 422,
  "message": "Dữ liệu gửi lên không hợp lệ",
  "errors": {
    "name": ["Tên công việc không được để trống"]
  }
}
```

---

## 2. DANH MỤC ENDPOINTS QUAN TRỌNG

### 2.1. Tìm kiếm Toàn cục (Global Quick Search)

* **Endpoint**: `GET /api/quick-search`
* **Mô tả**: Tìm kiếm nhanh tức thì phục vụ ô Search trên Topbar và phím tắt `Ctrl + K`.
* **Query Parameters**:
  * `q` *(string, tùy chọn)*: Từ khóa tìm kiếm (Tên dự án, Mã issue task, Tên team, v.v.). Nếu để trống sẽ trả về các Lối tắt nhanh (Quick Links).
* **Response Sample (200 OK)**:
```json
{
  "query": "website",
  "results": {
    "tasks": [
      {
        "id": 142,
        "title": "[PROJ-12] Thiết kế trang chủ website",
        "subtitle": "Dự án: TaskPilot Rebranding",
        "url": "/tasks/142",
        "icon": "fa-check-square-o",
        "badge": "in_progress"
      }
    ],
    "projects": [
      {
        "id": 8,
        "title": "[WEB] Nâng cấp Website Doanh Nghiệp",
        "subtitle": "Xem chi tiết / Kanban Board",
        "url": "/projects/8/board",
        "icon": "fa-folder-open-o",
        "badge": "active"
      }
    ],
    "teams": [],
    "companies": []
  }
}
```

---

### 2.2. Phân hệ Phân bổ Công việc bằng AI (AI Recommendation)

#### `POST /tasks/recommend-assignee`
* **Mô tả**: Nhận diện và gợi ý nhân sự phù hợp nhất cho 1 công việc cụ thể.
* **Payload**:
```json
{
  "task_id": 142,
  "project_id": 8,
  "title": "Tối ưu hóa truy vấn SQL báo cáo doanh thu",
  "description": "Cần tối ưu query join 5 bảng MySQL và thêm Redis cache",
  "skills": ["MySQL", "Redis", "Backend"]
}
```
* **Response Sample (200 OK)**:
```json
{
  "status": "success",
  "recommendations": [
    {
      "user_id": 4,
      "name": "Nguyễn Văn Tuấn",
      "email": "tuan.nguyen@example.com",
      "score": 0.94,
      "matched_skills": ["MySQL", "Redis", "Backend"],
      "current_workload_hours": 12.5,
      "reason": "Khớp 3/3 kỹ năng yêu cầu và khối lượng việc trong tuần thấp."
    },
    {
      "user_id": 9,
      "name": "Trần Thị Lan",
      "email": "lan.tran@example.com",
      "score": 0.81,
      "matched_skills": ["MySQL", "Backend"],
      "current_workload_hours": 28.0,
      "reason": "Khớp 2 kỹ năng cốt lõi."
    }
  ],
  "engine_used": "FastPath_Heuristic",
  "latency_ms": 3.8
}
```

#### `POST /tasks/recommend-assignee-batch`
* **Mô tả**: Gợi ý phân bổ nhân sự hàng loạt cho toàn bộ các task chưa có người nhận (unassigned tasks) trong dự án hoặc sprint.

#### `POST /tasks/ai-feedback`
* **Mô tả**: Ghi nhận phản hồi của Project Manager khi chấp nhận hoặc từ chối gợi ý của AI, phục vụ việc fine-tune lại mô hình.

---

### 2.3. Quản lý Nhiệm vụ (Tasks & Kanban)

* `GET /tasks`: Lấy danh sách nhiệm vụ có lọc theo `project_id`, `status`, `priority`.
* `POST /tasks`: Tạo mới một nhiệm vụ.
* `GET /tasks/{id}`: Xem chi tiết nhiệm vụ, bình luận và log thời gian.
* `PUT /tasks/{id}`: Cập nhật thông tin nhiệm vụ.
* `POST /tasks/{id}/move`: Di chuyển thẻ trên bảng Kanban (Cập nhật `status` và `order_index`).
* `POST /tasks/{id}/worklog`: Ghi nhận nhật ký thời gian làm việc thực tế (giờ, phút, ghi chú).

---

### 2.4. Phân hệ Thông báo Thời gian thực (Notifications)

* `GET /notifications/unread-count`: Lấy số lượng thông báo chưa đọc (hiển thị trên Badge chuông).
* `GET /notifications/recent`: Lấy danh sách 10 thông báo mới nhất hiển thị trong dropdown.
* `POST /notifications/{id}/read`: Đánh dấu 1 thông báo cụ thể là đã đọc.
* `POST /notifications/read-all`: Đánh dấu toàn bộ thông báo là đã đọc.

---

## 3. BẢNG MÃ LỖI HTTP CHUẨN (HTTP STATUS CODES)

| Mã HTTP | Tên trạng thái | Diễn giải |
| :--- | :--- | :--- |
| `200 OK` | Thành công | Yêu cầu được xử lý trọn vẹn và trả về dữ liệu. |
| `201 Created` | Đã tạo mới | Tài nguyên mới (Task, Project) đã được khởi tạo thành công. |
| `400 Bad Request` | Yêu cầu sai | Cú pháp request không hợp lệ hoặc thiếu header bắt buộc. |
| `401 Unauthorized` | Chưa xác thực | Phiên đăng nhập đã hết hạn hoặc token không hợp lệ. |
| `403 Forbidden` | Bị cấm truy cập | Người dùng không có quyền trên workspace/dự án này. |
| `404 Not Found` | Không tìm thấy | ID tài nguyên không tồn tại trong hệ thống. |
| `422 Unprocessable` | Dữ liệu không hợp lệ | Vi phạm các quy tắc validation của FormRequest. |
| `500 Server Error` | Lỗi máy chủ | Lỗi ngoại lệ chưa được xử lý trong mã nguồn backend. |

---

*Tài liệu được bảo trì bởi Ban Quản lý Giao diện Lập trình Ứng dụng TaskPilot.*
