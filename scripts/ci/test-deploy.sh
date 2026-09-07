#!/usr/bin/env bash
# Isolated deployment integration test: no SSH, real PHP, database or HTTP.
set -Eeuo pipefail
root=$(cd "$(dirname "$0")/../.." && pwd)
tmp=$(mktemp -d /tmp/taskpilot-ci-XXXXXXXX)
trap 'rm -rf "$tmp"' EXIT
mkdir -p "$tmp/bin" "$tmp/source/bootstrap/cache" "$tmp/source/public"
printf '#!/usr/bin/env bash\nexit 0\n' > "$tmp/bin/php"
printf '#!/usr/bin/env bash\nexit "${MOCK_HEALTH_STATUS:-0}"\n' > "$tmp/bin/curl"
chmod +x "$tmp/bin/php" "$tmp/bin/curl"
export PATH="$tmp/bin:$PATH"
base="$tmp/app"
mkdir -p "$base/shared/storage" "$base/releases/old"
touch "$base/shared/.env"
ln -s "$base/releases/old" "$base/current"
for id in good bad; do
    mkdir -p "$base/incoming/$id"
    tar -czf "$base/incoming/$id/release.tar.gz" -C "$tmp/source" .
done
bash "$root/scripts/deploy/activate.sh" "$base" good https://example.test/health
test "$(readlink -f "$base/current")" = "$base/releases/good"
test "$(readlink -f "$base/current/.env")" = "$base/shared/.env"
if MOCK_HEALTH_STATUS=22 bash "$root/scripts/deploy/activate.sh" "$base" bad https://example.test/health; then
    echo 'Expected health failure' >&2
    exit 1
fi
test "$(readlink -f "$base/current")" = "$base/releases/good"
echo 'Deployment activation and health-failure rollback passed.'
