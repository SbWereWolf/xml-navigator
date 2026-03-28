#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
REPORT_DIR="$ROOT_DIR/continuous-integration/autotests-coverage-report"
PHPUNIT_CMD=(
    php
    ./vendor/phpunit/phpunit/phpunit
    --configuration
    ./continuous-integration/phpunit/phpunit.xml
    --testdox
    --colors=always
    --coverage-html
    ./continuous-integration/autotests-coverage-report
    --coverage-filter
    ./src
)

mkdir -p "$REPORT_DIR"

has_local_coverage_driver() {
    php -m | grep -iqE '^(xdebug|pcov)$'
}

run_local() {
    (
        cd "$ROOT_DIR"
        XDEBUG_MODE=coverage "${PHPUNIT_CMD[@]}"
    )
}

run_docker() {
    local image_tag="xml-browser-coverage:local"

    docker build \
        -f "$ROOT_DIR/continuous-integration/phpunit/Dockerfile" \
        -t "$image_tag" \
        "$ROOT_DIR"

    docker run --rm \
        -e XDEBUG_MODE=coverage \
        -v "$ROOT_DIR:/app" \
        -w /app \
        "$image_tag" \
        bash -lc "${PHPUNIT_CMD[*]}"
}

if has_local_coverage_driver; then
    run_local
    exit 0
fi

if command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
    run_docker
    exit 0
fi

echo "Coverage driver is missing and Docker is unavailable." >&2
echo "Install pcov/xdebug locally or start Docker and re-run the command." >&2
exit 3
