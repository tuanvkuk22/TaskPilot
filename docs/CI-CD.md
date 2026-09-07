# CI/CD TaskPilot

## Workflow

- `CI`: chạy trên pull request, merge queue, push vào `main`, `master`, `develop`, hoặc thủ công; cũng được Deploy gọi lại.
- `backend`: PHPUnit trên SQLite memory và MySQL 8.4 riêng biệt, PHP 8.4 + PCOV; kiểm tra Composer/lockfile/cú pháp PHP, xuất JUnit và coverage Clover. MySQL chạy thêm migrate fresh → rollback → migrate.
- `frontend`: Node 22, npm lockfile, build Vite và lưu frontend artifact.
- `ai`: Python 3.11, PyTorch CPU, pip check, Ruff kiểm tra lỗi cú pháp/tên chưa định nghĩa, compileall, unittest và coverage XML.
- `webrtc`: Go theo go.mod; xác minh module, go vet, build và test ngắn với race detector/coverage.
- `deployment-scripts`: actionlint kiểm tra workflow, Bash syntax và kiểm thử activation/rollback giả lập.
- `production-smoke`: dùng cùng script đóng gói với Deploy, vendor không có dev dependency, giải nén artifact rồi chạy package discovery, config/view cache, migration SQLite và HTTP server. Chromium kiểm tra Vue login, asset build, đăng nhập sai, chuyển khách về login và trang chào mừng. Lưu trace/screenshot khi lỗi.
- `security`: gọi workflow audit npm (cả browser tooling), Composer, Python và govulncheck. Các audit chạy ở job độc lập; npm chặn high/critical, các công cụ còn lại dùng chính sách mặc định. Không bỏ qua lỗi bằng continue-on-error.
- `CI required`: chỉ thành công khi tất cả nhóm trên thành công. Chọn check này trong branch protection/ruleset và yêu cầu PR trước khi merge.
- `Deploy`: chạy thủ công trên main/master, staging/production; chạy lại CI mở rộng trước đóng gói và triển khai.
- `Dependency security` vẫn chạy hằng tuần/thủ công ngoài CI để phát hiện advisory mới.
- Dependabot cập nhật dependency và GitHub Actions hằng tuần, bao gồm tooling riêng ở tests/browser.

Coverage hiện là báo cáo, chưa áp ngưỡng phần trăm tùy ý. Browser smoke chưa thay thế E2E mọi nghiệp vụ. Go dùng -short nên vẫn không bao phủ toàn bộ kiểm thử hạ tầng LiveKit. CI không train model hoặc đánh giá toàn bộ dataset; không truy cập database/server production.

**Gate bảo mật mới có thể làm CI đỏ với dependency cũ.** Cần xử lý findings trước khi merge/deploy; không coi việc thêm workflow là chứng nhận dependency hiện tại an toàn.

## Runtime và dependency

Ứng dụng khóa chính xác Laravel Framework **13.24.0**, dùng PHP 8.4+, Tinker 3 và PHPUnit 12.5. `scripts/ci/check-runtime.php` xác nhận phiên bản framework trong lockfile. Commit đồng thời `composer.json` và `composer.lock` khi thay đổi dependency; không dựa vào `vendor/` của máy phát triển.

PHPUnit local dùng SQLite memory được cấu hình trong `phpunit.xml`; máy chạy test cần `pdo_sqlite`. CI sinh `phpunit.ci.xml` bằng `scripts/ci/configure-phpunit.php` để chọn SQLite/MySQL mà không sửa cấu hình local. CI tự cài extension này. Các giá trị mail/cache/queue trong test được cô lập, không dùng database từ `.env`.

Frontend dùng `package-lock.json` và `npm ci --ignore-scripts`; cập nhật lockfile cùng `package.json`. `yarn.lock` cũ không được dùng trong pipeline. Bỏ lifecycle scripts vì Laravel Mix cũ kéo theo node-sass 4 không tương thích Node hiện đại; Vite dùng native optional packages trong lockfile và không cần build node-sass. Nếu thêm package cần install script, đánh giá và thêm bước rebuild riêng. AI CI chỉ cài dependency inference/test, không tải dataset hoặc train model. Go tests không thay thế kiểm thử tích hợp LiveKit với hạ tầng thật.

## Cấu hình GitHub

Tạo hai Environments `staging`, `production`. Giới hạn deployment branch về main/master; đặt required reviewers cho production nếu gói GitHub hỗ trợ. Workflow chưa tự push hoặc triển khai khi chỉ merge code.

Khai báo cho từng Environment:

| Loại | Tên | Giá trị |
| --- | --- | --- |
| Secret | `DEPLOY_HOST` | DNS hoặc IPv4 server |
| Secret | `DEPLOY_USER` | User SSH chuyên dùng để deploy |
| Secret | `DEPLOY_SSH_KEY` | Private key của user deploy |
| Secret | `DEPLOY_KNOWN_HOSTS` | Dòng known_hosts được xác minh fingerprint qua kênh tin cậy |
| Variable | `DEPLOY_PATH` | Ví dụ `/var/www/taskpilot`, không có khoảng trắng |
| Variable | `HEALTH_URL` | URL HTTPS kiểm tra ứng dụng, trả 2xx khi ứng dụng sẵn sàng |

SSH dùng cổng 22. Không tắt host key checking. Không đưa `.env`/private key vào repository hoặc artifact. GitHub Actions cần quyền đọc contents và quyền sử dụng các action được tham chiếu.

## Chuẩn bị server lần đầu

Server Linux cần PHP 8.4 CLI/FPM và extensions ứng dụng, MySQL, curl, tar, sha256sum, flock, SSH và GNU coreutils. Web server trỏ document root tới `/var/www/taskpilot/current/public`; cấu hình PHP-FPM dùng realpath (`$realpath_root`) để tránh cache đường dẫn release cũ. Deploy user và PHP-FPM user cần quyền phù hợp trên shared storage và bootstrap/cache của release. Queue workers cần process manager tự khởi động lại sau `queue:restart`.

Tạo:

```text
/var/www/taskpilot/
  shared/
    .env
    storage/app/public/
    storage/framework/cache/data/
    storage/framework/sessions/
    storage/framework/views/
    storage/logs/
    venv/                       # virtualenv Python nếu dùng AI
    models/                     # checkpoint đã kiểm duyệt, nếu có
  releases/
  incoming/
  current -> releases/<release-id>
```

Đặt `.env` production trong `shared/.env`: APP_KEY cố định, APP_ENV=production, APP_DEBUG=false, APP_URL, thông tin MySQL/mail/LiveKit và đường dẫn Python theo cấu hình ứng dụng. Không generate key mỗi lần deploy. Nếu bật AI, tạo virtualenv Python 3.11 trong `shared/venv`, cài `AI/requirements-ci.txt` và đặt checkpoint `assignee_recommender.pt` trong `shared/models` nếu cần. Việc cập nhật dependency Python trên server là bước vận hành riêng; artifact không chứa virtualenv/model/dataset.

LiveKit trong `webrtc/` được build/test trong CI nhưng không được triển khai chung với PHP: vận hành service này độc lập theo `webrtc/deploy/README.md`, với cấu hình TLS, Redis, UDP và firewall riêng.

## Release và rollback

Script giữ `.env`, storage, virtualenv và model ngoài release. Chạy package discovery, config/view cache, migration trước khi chuyển symlink; restart queue rồi kiểm tra HTTPS với retry. Nếu lỗi sau chuyển symlink, khôi phục release trước và đánh dấu job thất bại. Lần deploy đầu chưa có release trước để rollback. Health URL phải kiểm tra ứng dụng thực, tránh trang tĩnh/CDN hoặc redirect đăng nhập; nên kiểm tra DB và dependency thiết yếu.

Rollback chỉ đổi code, không đảo migration. Dùng migration tương thích cả phiên bản cũ/mới và backup DB trước migration có rủi ro. Release cũ và incoming được giữ lại để điều tra/rollback; quản trị viên cần dọn định kỳ, luôn giữ current và ít nhất một bản tốt trước đó. Worker dài hạn có thể cần restart lại sau rollback.

Để rollback thủ công trên server, xác định release tốt rồi dùng `ln -s <release-tốt> <DEPLOY_PATH>/rollback` và `mv -Tf <DEPLOY_PATH>/rollback <DEPLOY_PATH>/current`, restart workers và xác nhận health check. Không chạy rollback migration tự động.

## Kiểm tra tại máy phát triển

```bash
composer validate --no-check-publish
php scripts/ci/check-runtime.php
# Dùng môi trường test riêng, không trỏ DB production:
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: vendor/bin/phpunit
npm ci --ignore-scripts
npm run vite:build
python -m pip install -r AI/requirements-ci.txt
python -m unittest discover -s AI -p 'test_*.py' -v
bash -n scripts/deploy/activate.sh
bash scripts/ci/test-deploy.sh
```

Tham khảo: [GitHub Environments](https://docs.github.com/en/actions/concepts/workflows-and-actions/deployment-environments), [workflow artifacts](https://docs.github.com/en/actions/tutorials/store-and-share-data).

## Chạy kiểm tra mở rộng

- SQLite: `CI_DATABASE=sqlite php scripts/ci/configure-phpunit.php`, rồi `vendor/bin/phpunit -c phpunit.ci.xml`.
- MySQL: chỉ dùng DB tạm tên `taskpilot_ci`, đặt CI_DATABASE=mysql và DB_HOST/PORT/USERNAME/PASSWORD của DB tạm; sinh config như trên rồi chạy PHPUnit. Job migration có migrate:fresh nên tuyệt đối không trỏ vào DB có dữ liệu cần giữ.
- Browser: `npm ci --prefix tests/browser`, `tests/browser/node_modules/.bin/playwright install chromium`, rồi `tests/browser/node_modules/.bin/playwright test --config tests/browser/playwright.config.cjs` với ứng dụng test đang chạy. CI_BASE_URL đổi URL test, CI_CHROMIUM_PATH cho phép dùng Chromium có sẵn ở local.
- Python: `ruff check AI --select E9,F63,F7,F82`; coverage chạy qua `python -m coverage run --source=AI -m unittest discover -s AI -p 'test_*.py' -v`.
- Workflow: `go run github.com/rhysd/actionlint/cmd/actionlint@v1.7.7 -shellcheck="" -pyflakes=""`.
- Báo cáo PHP/AI/Go/browser được lưu artifact 14 ngày. Không có coverage threshold ở giai đoạn này.

Tham khảo triển khai: [Playwright CI](https://playwright.dev/docs/ci-intro), [reusable workflows](https://docs.github.com/en/actions/how-tos/reuse-automations/reuse-workflows), [Go vulnerability management](https://go.dev/doc/security/vuln/).
