#!/usr/bin/env bash
set -Eeuo pipefail
base=${1:?deployment path required}
release_id=${2:?release ID required}
health_url=${3:?HTTPS health URL required}
[[ "$base" =~ ^/[a-zA-Z0-9/_-]+$ && "$base" != / ]]
[[ "$release_id" =~ ^[a-zA-Z0-9-]+$ ]]
[[ "$health_url" == https://* ]]
release="$base/releases/$release_id"
mkdir -p "$base/releases"
exec 9>"$base/deploy.lock"
flock -n 9 || { echo 'Another deployment is active.' >&2; exit 1; }
test -f "$base/shared/.env"
test -d "$base/shared/storage"
test ! -e "$release"
previous=$(readlink -f "$base/current" || true)
switched=false
rollback() {
    status=$?
    trap - ERR
    if [[ "$switched" == true && -n "$previous" && -d "$previous" ]]; then
        ln -s "$previous" "$base/rollback-$release_id"
        mv -Tf "$base/rollback-$release_id" "$base/current"
        echo "Restored $previous; database migrations were NOT reversed." >&2
    elif [[ "$switched" == true ]]; then
        echo 'First deployment failed health check; no previous release exists.' >&2
    fi
    exit "$status"
}
trap rollback ERR
mkdir "$release"
tar -xzf "$base/incoming/$release_id/release.tar.gz" -C "$release"
ln -s "$base/shared/.env" "$release/.env"
ln -s "$base/shared/storage" "$release/storage"
mkdir -p "$release/bootstrap/cache"
# The virtualenv and optional model checkpoint are provisioned outside releases.
if [[ -d "$base/shared/venv" ]]; then
    ln -s "$base/shared/venv" "$release/.venv"
fi
if [[ -d "$base/shared/models" ]]; then
    ln -s "$base/shared/models" "$release/AI/models"
fi
cd "$release"
php artisan package:discover --ansi
php artisan config:cache
php artisan view:cache
php artisan storage:link
# Migrations must remain compatible with the previous release for code rollback.
php artisan migrate --force
ln -s "$release" "$base/next-$release_id"
mv -Tf "$base/next-$release_id" "$base/current"
switched=true
php artisan queue:restart
curl --fail --silent --show-error --retry 5 --retry-all-errors --retry-delay 5 --max-time 15 "$health_url" > /dev/null
echo "Deployed $release_id"
