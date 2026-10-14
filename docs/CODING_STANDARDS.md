# QUY CHUẨN KỸ NGHỆ PHẦN MỀM & LẬP TRÌNH (ENGINEERING STANDARDS & CONVENTIONS)

```text
Document ID:     TP-ENG-STD-001
Classification:  Internal / Engineering Standards
Version:         2.0.0
Author:          Lead QA & Engineering Practice Committee
Status:          Mandatory for all Contributors
Date:            2026-10-14
```

---

## 1. NGUYÊN TẮC THIẾT KẾ CỐT LÕI (CORE PRINCIPLES)

Mọi dòng code được commit vào kho mã nguồn TaskPilot phải tuân thủ các nguyên tắc:
1. **Single Responsibility (Đơn nhiệm)**: Mỗi Class, Handler, Function chỉ giải quyết duy nhất một vấn đề nghiệp vụ.
2. **Explicit Dependency Injection (Tiêm phụ thuộc tường minh)**: Phụ thuộc phải được khai báo rõ ràng trong constructor. Cấm tiêm ẩn (service locator) hoặc tự khởi tạo `new`.
3. **Fail-Fast & Defensive Programming**: Kiểm tra tính hợp lệ của dữ liệu đầu vào ngay tại ranh giới hệ thống (FormRequest). Báo lỗi sớm thay vì để dữ liệu sai lệch trôi sâu vào hệ thống.
4. **Readable Over Clever (Dễ hiểu hơn phức tạp)**: Ưu tiên mã nguồn tường minh, đặt tên có nghĩa, tránh dùng cú pháp viết tắt hoặc mẹo lập trình khó hiểu.

---

## 2. QUY CHUẨN PHP & BACKEND (LARAVEL 13 / PHP 8.4)

### 2.1. Quy tắc Tiêm Phụ thuộc (Dependency Injection)

Bắt buộc sử dụng **Constructor Injection** với cú pháp Constructor Property Promotion của PHP 8:

```php
// ✅ CHUẨN: Tiêm tường minh qua Constructor
class ProjectsController extends Controller
{
    public function __construct(
        private readonly ListProjectsHandler $listProjectsHandler,
        private readonly StoreProjectHandler $storeProjectHandler
    ) {
    }
}
```

```php
// ❌ VI PHẠM NGHIÊM TRỌNG: Cấm tuyệt đối
$handler = app(ListProjectsHandler::class);
$handler = resolve(ListProjectsHandler::class);
$handler = App::make(ListProjectsHandler::class);
$service = new SomeService();
```

### 2.2. Quy tắc Phân Tầng Modular Monolith

* **Controller**:
  * Chỉ được phép gọi `Handler`.
  * Không chứa câu lệnh SQL (`DB::table()`, `Model::where()`).
  * Không chứa logic tính toán nghiệp vụ hay biến đổi dữ liệu phức tạp.
* **Handler**:
  * Mỗi Handler chỉ đại diện cho **1 Use Case duy nhất**. Tên file phải bắt đầu bằng động từ hành động: `StoreTaskHandler`, `GetProjectFormDataHandler`, `ArchiveProjectHandler`.
  * Handler được phép gọi `Repository`, `DomainService` hoặc kích hoạt `Event`.
* **Repository**:
  * Chịu trách nhiệm hoàn toàn về truy vấn cơ sở dữ liệu.
  * Trả về kiểu dữ liệu rõ ràng: `Model`, `Collection`, `LengthAwarePaginator`, hoặc `null`.

### 2.3. Quy chuẩn PHPDoc và Type System

Tất cả các method `public` và `protected` bắt buộc phải có PHPDoc đầy đủ và định kiểu (Strict Types):

```php
/**
 * Create a new task entity for a project.
 *
 * @param int $userId
 * @param int $projectId
 * @param array<string, mixed> $attributes
 *
 * @throws \ProjectsManager\Exceptions\ProjectNotFoundException
 * @return \ProjectsManager\Task
 */
public function create(int $userId, int $projectId, array $attributes): Task
{
    // Implementation
}
```

**Quy tắc PHPDoc**:
* Câu mô tả ngắn gọn, kết thúc bằng dấu chấm `.`.
* `@param` chỉ gồm kiểu dữ liệu + tên biến (`@param int $userId`).
* Có 1 dòng trống phân cách giữa description, danh sách `@param`, `@throws` và `@return`.
* Thứ tự sắp xếp method trong class: `public` ➔ `protected` ➔ `private`.

---

## 3. QUY CHUẨN FRONTEND (VUE 3, JAVASCRIPT & CSS)

### 3.1. Vue 3 Composition API

* Bắt buộc sử dụng cú pháp `<script setup>` cho các component mới.
* Tách biệt logic trạng thái tái sử dụng thành các **Composables** trong thư mục `resources/js/composables/` (ví dụ: `useAiAssignee.js`, `useKanbanDragDrop.js`).
* Luôn khai báo `defineProps` và `defineEmits` có định kiểu rõ ràng.

### 3.2. Quy chuẩn CSS & Design Tokens

* Sử dụng các biến Design Tokens đã định nghĩa trong `sidebar-layout.css` và `custom-ui.css`:
  * Màu chính: `var(--tp-primary)`
  * Màu nền: `var(--tp-body-bg)`
  * Chiều cao topbar: `var(--tp-topbar-height)`
  * Độ rộng sidebar: `var(--tp-sidebar-width)`
* Cấm hardcode các màu generic như `color: red; background: blue;`. Luôn sử dụng bảng màu HSL/Hex chuẩn của Design System.
* Đảm bảo tính tương thích hiển thị trên thiết bị di động (Mobile Responsive) bằng CSS Media Queries chuẩn: `@media (max-width: 991px)` và `@media (max-width: 640px)`.

---

## 4. QUY CHUẨN CƠ SỞ DỮ LIỆU (DATABASE & MIGRATIONS)

1. **Khóa chính**: Luôn sử dụng `id()` dạng `bigIncrements`.
2. **Khóa ngoại**: Luôn định nghĩa ràng buộc rõ ràng với `constrained()` và cơ chế xóa phù hợp (`cascadeOnDelete()` hoặc `nullOnDelete()`):
   ```php
   $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
   ```
3. **Chỉ mục (Indexes)**:
   * Mọi cột dùng trong mệnh đề `WHERE` thường xuyên, `ORDER BY` hoặc `JOIN` bắt buộc phải đánh Index.
   * Truy vấn tìm kiếm lọc kết hợp nhiều điều kiện bắt buộc dùng **Composite Index**.
4. **Quy tắc an toàn**: Mọi migration xóa cột hoặc sửa đổi cấu trúc lớn bắt buộc phải có phương thức `down()` tương ứng để rollback an toàn.

---

## 5. QUY CHUẨN QUẢN LÝ MÃ NGUỒN VÀ GIT (VERSION CONTROL)

### 5.1. Định dạng Commit (Conventional Commits)

Mọi commit message phải tuân thủ chuẩn: `<type>(<scope>): <mô tả ngắn gọn>`

| Type | Ý nghĩa | Ví dụ |
| :--- | :--- | :--- |
| `feat` | Tính năng mới cho người dùng | `feat(sidebar): add collapsible vertical navigation` |
| `fix` | Sửa lỗi phần mềm | `fix(search): escape wildcard characters in query` |
| `refactor` | Tái cấu trúc mã nguồn không đổi hành vi | `refactor(tasks): extract assignee recommendation logic` |
| `perf` | Cải thiện hiệu năng truy vấn / render | `perf(board): add composite index for task board query` |
| `docs` | Thêm hoặc cập nhật tài liệu kỹ thuật | `docs(api): document quick search endpoints` |
| `test` | Thêm unit test hoặc cập nhật test suite | `test(projects): add test case for archiving project` |

### 5.2. Chiến lược Nhánh (Branching Strategy)

* `main` / `master`: Nhánh production, luôn ở trạng thái sẵn sàng deploy.
* `develop`: Nhánh tích hợp chính cho staging.
* `feature/<ticket-id>-<short-name>`: Nhánh phát triển tính năng (ví dụ: `feature/TP-102-quick-create-modal`).
* `hotfix/<ticket-id>`: Nhánh sửa lỗi khẩn cấp trên production.

---

*Tuân thủ nghiêm ngặt quy chuẩn này là tiêu chí bắt buộc để vượt qua khâu Code Review tại TaskPilot.*
