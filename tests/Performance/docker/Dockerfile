FROM php:8.4-cli-bookworm AS bench-base

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && docker-php-ext-install sockets \
    && rm -rf /var/lib/apt/lists/*

COPY tests/Performance/docker/zz-benchmark.ini /usr/local/etc/php/conf.d/zz-benchmark.ini

WORKDIR /app

FROM bench-base AS bench-deps

COPY composer.json composer.lock composer.phar /app/

RUN php /app/composer.phar install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

FROM bench-base AS bench-app

WORKDIR /app

COPY --from=bench-deps /app/vendor /app/vendor
COPY --from=bench-deps /app/composer.lock /app/composer.lock
COPY --from=bench-deps /app/composer.json /app/composer.json
COPY . /app

ENTRYPOINT ["php"]
