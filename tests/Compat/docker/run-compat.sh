#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -lt 3 ]; then
    echo "usage: $0 <php-image> <xdebug-version> <command...>" >&2
    exit 2
fi

php_image="$1"
shift
xdebug_version="$1"
shift

image_tag="xml-browser-compat:${php_image//[:\/]/-}"

docker build \
    --build-arg "PHP_IMAGE=$php_image" \
    --build-arg "XDEBUG_VERSION=$xdebug_version" \
    -t "$image_tag" \
    -f tests/Compat/docker/Dockerfile \
    .

docker run --rm \
    -v "$PWD:/app" \
    -w /app \
    "$image_tag" \
    bash -lc "$*"
