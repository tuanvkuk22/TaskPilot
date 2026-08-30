# Copilot Code Review Instructions

## Project Context

This project is a Jira-like project and task management system built with:

* Laravel
* MySQL
* Cloudflare R2
* Blade
* jQuery

The project follows a **Modular Monolith + Module-based Architecture**.

Expected module structure:

```text
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
```

A module may contain:

```text
Controllers/
Handlers/
Repositories/
Models/
Requests/
Resources/
```

---

# 1. Review Scope

When reviewing a Pull Request:

1. Scan the entire diff first.
2. Focus especially on added and modified lines.
3. Check whether changed code violates any rule in this document.
4. Report only actual violations introduced or directly affected by the PR.
5. Do not report unrelated pre-existing issues unless the changed code directly depends on or worsens them.
6. Do not praise the code.
7. Do not provide general recommendations.
8. Do not explain best practices unless necessary to identify the violation.

The review must cover:

* Architecture
* Dependency Injection
* Clean Code
* PHPDoc
* Native PHP types
* Formatting
* Comments
* Laravel conventions
* Blade
* JavaScript/jQuery
* Security
* Database/query usage

---

# 2. Architecture

The project follows:

**Modular Monolith + Module-based Architecture**

The preferred dependency flow is:

```text
Controller → Handler → Repository
Controller → Handler → Service → Repository
```

## Controller

Controllers must:

* Only handle HTTP-level concerns.
* Delegate use cases to Handlers.
* Use constructor dependency injection.
* Return the appropriate response/resource/view.

Controllers must NOT:

* Contain business logic.
* Execute database queries.
* Call repositories directly.
* Call Eloquent models directly for business operations.
* Perform complex data transformation.
* Implement use cases directly.
* Instantiate services or handlers manually.

### Violation

```php
class IssueController
{
    public function store(Request $request)
    {
        $issue = Issue::create($request->all());

        return response()->json($issue);
    }
}
```

### Expected

```text
Controller → Handler → Repository
```

---

## Handler

A Handler represents a specific use case.

Examples:

```text
CreateIssueHandler
UpdateIssueHandler
DeleteIssueHandler
AssignIssueHandler
```

Handlers must:

* Implement one clearly defined use case.
* Coordinate business logic for that use case.
* Call Repository and/or Service through dependency injection.

Handlers must NOT:

* Directly instantiate dependencies.
* Contain unrelated use cases.
* Become an unnecessarily large "god class".
* Contain database access that belongs in Repository when the operation is reusable or clearly persistence-related.

---

## Repository

Repositories are the primary layer for database access.

Repositories may:

* Query Eloquent models.
* Build database queries.
* Persist entities.
* Encapsulate reusable database operations.

Flag database access performed directly from Controllers.

Flag database access performed in other layers when it clearly violates the established Repository architecture.

---

## Service

Services are intended for:

* Reusable business/application services.
* External integrations.
* Infrastructure-related operations.
* Operations shared by multiple use cases.

Examples:

```text
CloudflareR2Service
NotificationService
EmailService
```

Do not introduce a Service merely to wrap a single trivial operation unless the existing architecture requires it.

---

## Dependency Restrictions

Do not call Controllers from other application layers.

Do not introduce unnecessary dependency directions that violate:

```text
Controller → Handler → Repository
Controller → Handler → Service → Repository
```

Do not use DTOs unless they provide a clear architectural benefit and are actually necessary.

---

# 3. Dependency Injection

Constructor injection is mandatory.

Expected:

```php
public function __construct(
    private readonly CreateIssueHandler $handler
) {
}
```

Flag service/dependency resolution such as:

```php
app(SomeService::class);

resolve(SomeService::class);

App::make(SomeService::class);

new SomeService(...);
```

inside Controllers, Handlers, Services, Repositories, or other application classes when the dependency should be injected.

Dependencies must not be manually instantiated when constructor injection is possible.

Do not flag Laravel's dependency resolution when it is explicitly required by framework infrastructure or Service Provider configuration.

---

# 4. Clean Code

Report clear and objective violations.

Flag:

* Classes with multiple unrelated responsibilities.
* Methods implementing multiple unrelated use cases.
* Excessively long methods when responsibilities can clearly be separated.
* Excessive nesting.
* Repeated business logic.
* Copy-pasted logic between modules.
* Unclear class names.
* Unclear method names.
* Unclear variable names.
* Magic numbers with business meaning.
* Magic strings with business meaning.
* Dead code.
* Unused variables.
* Unused imports.
* Duplicate conditions or unnecessary repeated operations.

Prefer:

* Single Responsibility.
* Clear Naming.
* Small Methods.
* Low Duplication.
* Simple Control Flow.

Do not report subjective stylistic preferences as violations.

---

# 5. PHPDoc and Type Safety

Every public method must have PHPDoc.

Expected structure:

```php
/**
 * Create a new issue.
 *
 * @param array $data
 *
 * @return Issue
 */
```

Rules:

* Description must end with `.`.
* Include `@return`.
* Use `@param` only when applicable.
* `@param` must contain the type and parameter name.
* Keep a blank line between description, `@param`, `@throws`, and `@return` sections when multiple sections exist.
* Preserve existing native PHP types.
* Do not weaken existing type declarations.
* Do not remove existing union/intersection types without a valid reason.
* Report missing or incorrect PHPDoc on public methods.

Method ordering:

```text
public
protected
private
```

Do not report missing PHPDoc for private methods unless the project code style explicitly requires it.

---

# 6. Formatting

Follow the project's formatting rules defined in:

```text
tools/.php-cs-fixer.dist.php
```

Flag clear formatting violations such as:

* Extra spaces used to align `=` or `=>`.
* Inline fully qualified class names when a `use` statement should be used.
* Multiple unnecessary blank lines.
* Trailing whitespace.
* Extremely dense conditional/query blocks that violate the project's established formatting.
* Missing blank lines between clearly separated logical blocks.

Do not report formatting that is already automatically handled correctly by the project's configured formatter.

---

# 7. Comments

Comments should explain non-obvious or genuinely complex logic.

Flag:

* Comments that merely repeat what the code does.
* Vietnamese comments.
* Debug comments.
* Temporary comments with no value.
* TODO/placeholder comments without actionable value.
* Commented-out code.
* Obsolete comments that no longer match the implementation.

Example violation:

```php
// Get user by ID
$user = User::find($id);
```

Do not require comments for straightforward code.

---

# 8. Laravel

Flag the following:

### Controller

* Eloquent queries in Controllers.
* Database queries in Controllers.
* Business logic in Controllers.
* Direct Repository calls from Controllers.
* Unnecessary data transformation in Controllers.
* Fat Controllers.

### Handler / Service

* Manual dependency creation.
* Unnecessary responsibility accumulation.
* Direct database access when it should belong to Repository.
* Unrelated use cases inside the same Handler.

### Blade

Flag:

* Database queries in Blade.
* Eloquent queries in Blade.
* Business logic in Blade.
* Complex application logic in Blade.
* Inline fully qualified class names.
* Excessive logic that should be handled by the application layer.

### Laravel Container

Flag:

```php
app(...)
resolve(...)
App::make(...)
```

when used for application dependency resolution outside Service Providers or framework-specific configuration.

---

# 9. JavaScript / jQuery

The project uses jQuery.

Flag:

* `var` declarations.
* `let` when the variable is never reassigned.
* Unused variables.
* Dead JavaScript code.
* Debug statements such as `console.log()` left in production code.
* Duplicated JavaScript logic.
* Unnecessary global variables.
* Clearly unsafe DOM manipulation that can introduce XSS.
* JavaScript code that exposes sensitive information.

Use:

```javascript
const
```

when reassignment is unnecessary.

Use:

```javascript
let
```

only when reassignment is required.

Do not require conversion from jQuery to another framework unless the PR explicitly introduces such a requirement.

---

# 10. Security

Flag clear security vulnerabilities, including:

* SQL injection.
* XSS.
* Unsafe mass assignment.
* Missing authorization checks.
* Authorization bypass.
* Authentication bypass.
* Hard-coded passwords.
* Hard-coded API keys.
* Hard-coded secrets.
* Hard-coded access tokens.
* Sensitive information exposed to clients.
* Sensitive information written to logs.
* Unsafe file upload handling.
* Unsafe Cloudflare R2 object access.
* Trusting user-controlled input without appropriate validation.
* Missing validation where untrusted input reaches sensitive operations.

Do not report environment variables or configuration values as hard-coded secrets when they are correctly referenced through configuration/environment mechanisms.

---

# 11. Database and Query Review

The project uses MySQL.

Flag:

* SQL injection.
* N+1 queries.
* Unnecessary repeated queries.
* Queries executed inside loops when they can clearly be avoided.
* Database access outside the Repository layer when it violates the architecture.
* Missing eager loading where the changed code clearly causes N+1 queries.
* Missing transaction when a single use case performs multiple related database changes that must succeed or fail together.
* Destructive database operations without appropriate constraints or authorization.

Do not flag every multiple-query operation as requiring a transaction. Only report it when atomicity is clearly required by the use case.

---

# 12. Cloudflare R2

Cloudflare R2 is used for object/file storage.

Flag:

* Hard-coded R2 credentials.
* Exposing access keys or secrets.
* Unsafe user-controlled object paths.
* Missing authorization for protected files.
* Public exposure of sensitive files.
* Direct infrastructure logic duplicated across application layers when an existing R2 Service should be used.

Prefer the existing application Service abstraction for R2 operations when one exists.

---

# 13. Validation and Authorization

For user-controlled input:

* Validate data before processing.
* Use Form Requests when appropriate and consistent with the existing module architecture.
* Do not trust request input directly.

Flag:

* Missing validation for sensitive or structured input.
* `$request->all()` used directly for persistence when it creates a mass-assignment or validation risk.
* Missing authorization checks for protected resources.
* Authorization checks that can be bypassed by manipulating IDs or request parameters.

---

# 14. Review Output

The review output must contain **only violations**.

Do NOT:

* Praise the implementation.
* Summarize what was done correctly.
* Provide general recommendations.
* Explain unrelated best practices.
* Report subjective preferences.
* Report pre-existing issues unrelated to the PR.

Each violation must include:

1. File path.
2. Line number when available.
3. Clear description of the violation.
4. The specific rule that is violated when useful.

Use this format:

```text
1/ `app/Modules/Issues/Controllers/IssueController.php:25` — Controller gọi Repository trực tiếp, vi phạm dependency flow `Controller → Handler → Repository`.

2/ `app/Modules/Issues/Handlers/CreateIssueHandler.php:42` — Khởi tạo Service bằng `new`, vi phạm constructor injection.

3/ `app/Modules/Issues/Controllers/IssueController.php:18` — Public method thiếu PHPDoc.
```

If multiple violations exist in the same file, report them separately when they are distinct issues.

At the end:

```text
done (3 violations)
```

If there are no violations:

```text
done
```

---

# 15. Review Priority

When reporting violations, prioritize:

1. Security vulnerabilities.
2. Architecture violations.
3. Functional/logic bugs.
4. Database/query problems.
5. Missing authorization/validation.
6. Dependency Injection violations.
7. Type/PHPDoc violations.
8. Clean Code violations.
9. Formatting violations.

Only report issues that are sufficiently clear and actionable from the diff.

The goal of the review is to identify **real violations that should be fixed before merging**, not to maximize the number of comments.
