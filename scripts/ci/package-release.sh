#!/usr/bin/env bash
set -Eeuo pipefail
output=${1:?absolute output archive path required}
[[ "$output" == /* ]]
tar --exclude=public/hot --exclude=public/storage --exclude='bootstrap/cache/*.php' \
    -czf "$output" app bootstrap config database routes resources/views resources/lang \
    public AI/*.py AI/requirements-ci.txt artisan composer.json composer.lock vendor scripts/deploy/activate.sh
