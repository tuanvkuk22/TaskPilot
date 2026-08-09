Copilot Code Review Instructions
Dự án là hệ thống quản lý công việc và dự án tương tự Jira, sử dụng:
Laravel
MySQL
Cloudflare R2
Blade
jQuery
1. Architecture
   Sử dụng Modular Monolith + Module-based Architecture:
   app/
   ├── Modules/
   │   ├── Auth/
   │   ├── Users/
   │   ├── Projects/
   │   ├── Issues/
   │   ├── Boards/
   │   ├── Sprints/
   │   ├── Comments/
   │   └── Notifications/
   ├── Services/
   └── Shared/

Mỗi module có thể gồm:
Controllers/
Handlers/
Repositories/
Models/
Requests/
Resources/

Dependency flow duy nhất:
Controller → Handler → Repository
Controller → Handler → Service → Repository

Quy tắc:
Controller chỉ gọi Handler.
Controller không chứa business logic, DB query hoặc data transformation.
Handler xử lý một use case cụ thể.
Handler được gọi Repository/Service.
Repository xử lý database.
Service dùng cho reusable/external services.
Không gọi Controller từ layer khác.
Không dùng DTO nếu không thực sự cần thiết.

2. Dependency Injection
   Bắt buộc constructor injection:
   public function __construct(
   private readonly CreateIssueHandler $handler
   ) {
   }

VIOLATION:
app(...)
resolve(...)
App::make(...)
new SomeService(...)

Không tự khởi tạo dependency trong Handler/Service.

3. Clean Code
   Flag các violation rõ ràng:
   Class/method có nhiều trách nhiệm.
   Handler chứa nhiều use case.
   Method quá dài hoặc nesting quá sâu.
   Logic bị lặp lại.
   Tên class/method/variable không rõ nghĩa.
   Magic number/string có ý nghĩa nghiệp vụ.
   Dead code, unused variable/import.
   Business logic bị copy giữa nhiều nơi.
   Ưu tiên:
   Single Responsibility
   Clear Naming
   Small Methods
   Low Duplication
   Simple Control Flow


4. PHPDoc & Type
   Mọi public method phải có PHPDoc:
   /**
* Create a new issue.
*
* @param array $data
*
* @return Issue
  */

Quy tắc:
Description kết thúc bằng ..
@param chỉ gồm type + name.
Có blank line giữa description/params/throws/return.
Có @return.
Không xóa hoặc làm yếu native type/union type hiện có.
Method ordering:
public
protected
private


5. Formatting
   Tuân theo tools/.php-cs-fixer.dist.php.
   Flag:
   Extra spaces để căn = hoặc =>.
   FQCN inline thay vì use.
   Multiple blank lines.
   Trailing whitespace.
   Conditional/query block quá dense.
   Thiếu blank line giữa các block logic cần tách biệt.

6. Comments
   Chỉ giữ comment khi logic thực sự phức tạp.
   VIOLATION:
   Comment mô tả lại code.
   Comment tiếng Việt.
   TODO/placeholder không có giá trị.
   Debug comment.
   Commented-out code.

7. Laravel / Blade / JavaScript
   Flag:
   DB/Eloquent query trong Controller.
   Query hoặc business logic trong Blade.
   FQCN inline trong Blade.
   app(), resolve() ngoài Service Provider.
   Fat Controller/Fat Handler không hợp lý.
   JavaScript:
   Không dùng var.
   Dùng const nếu không reassignment.
   Dùng let khi cần reassignment.
   Flag unused/dead/debug code.

8. Security & Database
   Flag các vấn đề rõ ràng:
   SQL injection.
   XSS.
   Unsafe mass assignment.
   Authorization bypass.
   Hard-coded secret/password/token.
   Sensitive data exposure.
   N+1 query.
   Query lặp lại không cần thiết.
   Thiếu transaction khi một use case cần nhiều DB changes thành công cùng nhau.
   Repository là layer chính để truy cập database.

9. Review Scope
   Luôn scan diff trước, đặc biệt các dòng thay đổi.
   Nếu thay đổi làm phát sinh violation về:
   Architecture.
   Clean Code.
   Security.
   PHPDoc.
   Type.
   Formatting.
   JavaScript.
   thì phải report.

10. Review Output
    Chỉ báo cáo violation. Không praise, không recommendation, không giải thích best practice.
    Format:
    1/ `app/Modules/Issues/Controllers/IssueController.php:25` — Controller gọi Repository trực tiếp, phải thông qua Handler.
    2/ `app/Modules/Issues/Handlers/CreateIssueHandler.php:42` — Khởi tạo Service bằng `new`, phải sử dụng constructor injection.
    3/ `app/Modules/Issues/Controllers/IssueController.php:18` — Public method thiếu PHPDoc.

done (3 violations)

Nếu không có violation:
done



