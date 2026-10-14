# TASKPILOT ENGINEERING DOCUMENTATION PORTAL
## CỔNG TÀI LIỆU KỸ THUẬT & KIẾN TRÚC DOANH NGHIỆP

```text
Repository:     TaskPilot (Intelligent Enterprise Work & Project Management System)
Classification: Internal Engineering Wiki
Maintained By:  Architecture & Engineering Core Team
Last Updated:   2026-10-14
Status:         Active / Master Reference
```

---

## 1. GIỚI THIỆU (WELCOME)

Chào mừng bạn đến với cổng tài liệu kỹ thuật chính thức của **TaskPilot**. Bộ tài liệu này được thiết kế và biên soạn theo quy chuẩn tài liệu kỹ thuật doanh nghiệp (Enterprise Engineering Handbook), mô phỏng quy trình tài liệu hóa tại các tập đoàn công nghệ hàng đầu thế giới (Google, Atlassian, GitLab, Uber).

Tất cả các kỹ sư phần mềm, kiến trúc sư hệ thống, QA/QC và DevOps tham gia phát triển dự án bắt buộc phải tham chiếu các tài liệu này để đảm bảo tính nhất quán về kiến trúc, chất lượng mã nguồn, quy chuẩn bảo mật và quy trình phát hành.

---

## 2. BẢN ĐỒ TÀI LIỆU HỆ THỐNG (DOCUMENTATION DIRECTORY)

```
docs/
├── README.md                              <- [BẠN ĐANG Ở ĐÂY] Cổng thông tin & Chỉ mục tài liệu
├── ARCHITECTURE.md                        <- Kiến trúc hệ thống Modular Monolith & Luồng dữ liệu
├── CODE_REVIEW_AND_IMPROVEMENT_ROADMAP.md <- Đánh giá mã nguồn hiện trạng & Lộ trình mở rộng tối đa
├── CODING_STANDARDS.md                    <- Quy chuẩn lập trình PHP, Vue 3, Clean Code & Git
├── API_SPECIFICATION.md                   <- Đặc tả API RESTful, QuickSearch & AI Services
├── DATABASE_DESIGN.md                     <- Thiết kế CSDL, ERD, Indexing Strategy & Quan hệ thực thể
├── AI_SYSTEM_DESIGN.md                    <- Thiết kế hệ thống AI Recommendation & Đánh giá MRR
├── CI-CD.md                               <- Quy trình CI/CD, Test Pipeline, Artifacts & Deploy
└── SECURITY_AND_COMPLIANCE.md             <- Chính sách bảo mật, RBAC, Kiểm soát rủi ro & Audit
```

---

## 3. CHỈ MỤC CHI TIẾT CÁC TÀI LIỆU (DETAILED INDEX)

### 📌 [1. Kiến trúc Hệ thống (ARCHITECTURE.md)](file:///home/tuantn1807/project/TaskPilot/docs/ARCHITECTURE.md)
* **Mục tiêu**: Định hình tổng thể kiến trúc Modular Monolith của TaskPilot.
* **Nội dung chính**:
  * Mô hình C4 (Context, Container, Component).
  * Quy tắc ranh giới phân tầng (`Controller -> Handler -> Repository`).
  * Mô hình tích hợp giữa Laravel Core, Dịch vụ AI (Python/PyTorch) và WebRTC Signaling (Go).
  * Quy hoạch bộ nhớ đệm (Redis), Hàng đợi (Queue Workers) và Lưu trữ đám mây (Cloudflare R2).

### 📌 [2. Đánh giá Mã nguồn & Lộ trình Mở rộng (CODE_REVIEW_AND_IMPROVEMENT_ROADMAP.md)](file:///home/tuantn1807/project/TaskPilot/docs/CODE_REVIEW_AND_IMPROVEMENT_ROADMAP.md)
* **Mục tiêu**: Phân tích chi tiết hiện trạng mã nguồn và vạch ra lộ trình nâng cấp toàn diện.
* **Nội dung chính**:
  * Đánh giá 5 trụ cột: Frontend UI/UX, Backend, Database, AI, Security.
  * Kế hoạch tối ưu hóa các tính năng đang có (Topbar Search, Sidebar Collapse, Kanban, Sprint).
  * **12 Phân hệ mở rộng đến tối đa**: Agile Backlog Grooming, Workflow Engine, Automation Rules, Time Tracking, Interactive Gantt, Notion-like Wiki, Customer Portal, Realtime Collaboration, AI Copilot 2.0, DORA Metrics, Ecosystem API, Enterprise RBAC.

### 📌 [3. Quy chuẩn Lập trình (CODING_STANDARDS.md)](file:///home/tuantn1807/project/TaskPilot/docs/CODING_STANDARDS.md)
* **Mục tiêu**: Đảm bảo toàn bộ lập trình viên viết code đồng nhất, dễ đọc, dễ kiểm thử.
* **Nội dung chính**:
  * Quy tắc Constructor Injection, cấm tự tạo dependency bằng `new` hay `app()`.
  * Chuẩn đặt tên Class, Method, Variable, DB Migrations.
  * Chuẩn PHPDoc type-hinting nghiêm ngặt (Strict Types).
  * Quy chuẩn Vue 3 Composition API và CSS Variable Token System.
  * Quy tắc Commit Git (Conventional Commits: `feat:`, `fix:`, `refactor:`, `test:`).

### 📌 [4. Đặc tả API Hệ thống (API_SPECIFICATION.md)](file:///home/tuantn1807/project/TaskPilot/docs/API_SPECIFICATION.md)
* **Mục tiêu**: Cung cấp tài liệu API chuẩn RESTful cho Frontend, Mobile App và bên thứ ba.
* **Nội dung chính**:
  * Chuẩn Envelope phản hồi (`success`, `code`, `data`, `meta`, `errors`).
  * Danh mục endpoints: `/api/quick-search`, `/api/tasks`, `/api/projects`, `/api/skills`, `/api/notifications`.
  * Hướng dẫn mã lỗi HTTP Status Codes và xử lý Exception toàn cục.

### 📌 [5. Thiết kế Cơ sở Dữ liệu (DATABASE_DESIGN.md)](file:///home/tuantn1807/project/TaskPilot/docs/DATABASE_DESIGN.md)
* **Mục tiêu**: Chuẩn hóa sơ đồ thực thể quan hệ và tối ưu hóa hiệu năng truy vấn.
* **Nội dung chính**:
  * Sơ đồ ERD các thực thể trung tâm: `users`, `projects`, `tasks`, `teams`, `companies`, `sprints`, `worklogs`.
  * Chiến lược Composite Indexes chống chậm truy vấn khi dữ liệu đạt hàng triệu bản ghi.
  * Quy hoạch phân vùng dữ liệu và chính sách Soft Deletes an toàn.

### 📌 [6. Thiết kế Hệ thống Trí tuệ Nhân tạo (AI_SYSTEM_DESIGN.md)](file:///home/tuantn1807/project/TaskPilot/docs/AI_SYSTEM_DESIGN.md)
* **Mục tiêu**: Tài liệu hóa thuật toán phân bổ nhân sự thông minh.
* **Nội dung chính**:
  * Cơ chế lai (Hybrid Architecture): Heuristic FastPath kết hợp Deep Learning Inference.
  * Vector hóa đặc trưng kỹ năng (Skills Embedding) và cân bằng tải công việc (Workload Balancing).
  * Quy chuẩn đo lường đánh giá chất lượng mô hình qua chỉ số Mean Reciprocal Rank (MRR).

### 📌 [7. Quy trình CI/CD & Triển khai (CI-CD.md)](file:///home/tuantn1807/project/TaskPilot/docs/CI-CD.md)
* **Mục tiêu**: Tự động hóa kiểm thử và triển khai không gián đoạn (Zero-downtime Deployment).
* **Nội dung chính**:
  * Các cổng kiểm thử bắt buộc: PHPUnit, SQLite/MySQL, Vite build, Python syntax, Go WebRTC test.
  * Cấu hình SSH Deployment, Atomic Symlinks và cơ chế Rollback tự động.

### 📌 [8. An ninh Thông tin & Tuân thủ (SECURITY_AND_COMPLIANCE.md)](file:///home/tuantn1807/project/TaskPilot/docs/SECURITY_AND_COMPLIANCE.md)
* **Mục tiêu**: Bảo vệ an toàn dữ liệu khách hàng theo tiêu chuẩn ISO 27001 và OWASP.
* **Nội dung chính**:
  * Ma trận phân quyền RBAC (Role-Based Access Control).
  * Phòng chống SQL Injection, XSS, CSRF, IDOR và Rate Limiting.
  * Cơ chế ghi nhật ký hoạt động (Audit Logging).

---

## 4. QUY TRÌNH ĐÓNG GÓP TÀI LIỆU (DOCUMENTATION WORKFLOW)

1. **Nguyên tắc "Code đi kèm Docs"**: Mọi Pull Request (PR) bổ sung tính năng mới hoặc thay đổi kiến trúc hiện có bắt buộc phải cập nhật tài liệu tương ứng trong thư mục `docs/`.
2. **Quy chuẩn Markdown**:
   * Tiêu đề rõ ràng, phân cấp từ `##` đến `####`.
   * Sử dụng bảng (Tables) cho các cấu trúc dữ liệu hoặc so sánh.
   * Sử dụng sơ đồ Mermaid (`mermaid`) cho các luồng quy trình và kiến trúc.
   * Sử dụng định dạng cảnh báo chuẩn GitHub Flavored Markdown: `[!NOTE]`, `[!TIP]`, `[!IMPORTANT]`, `[!WARNING]`.

---

*© 2026 TaskPilot Engineering Core Team. Lưu hành nội bộ.*
