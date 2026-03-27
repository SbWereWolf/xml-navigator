#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
FIXTURES_DIR="${1:-$ROOT_DIR/task/performance-refactor-benchmark/fixtures}"
OUTPUT_PATH="${2:-$ROOT_DIR/task/performance-refactor-benchmark/reports/acceptance-benchmark.json}"
IMAGE_TAG="${3:-xml-browser-bench:local}"

mkdir -p "$FIXTURES_DIR"
mkdir -p "$(dirname "$OUTPUT_PATH")"

docker build \
    -f "$ROOT_DIR/tests/Performance/docker/Dockerfile" \
    -t "$IMAGE_TAG" \
    "$ROOT_DIR"

if [[ ! -f "$FIXTURES_DIR/manifest.json" ]]; then
    docker run --rm \
        -v "$FIXTURES_DIR:/fixtures" \
        "$IMAGE_TAG" \
        /app/tests/Performance/generate_fixture_pack.php --fixtures-dir=/fixtures
fi

docker run --rm \
    -v "$FIXTURES_DIR:/fixtures:ro" \
    -v "$(dirname "$OUTPUT_PATH"):/out" \
    "$IMAGE_TAG" \
    /app/tests/Performance/acceptance_benchmark.php \
    --fixtures-dir=/fixtures \
    --output="/out/$(basename "$OUTPUT_PATH")"
