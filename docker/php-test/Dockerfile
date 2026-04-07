ARG PHP_TAG=8.3-cli
FROM php:${PHP_TAG}

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ARG XDEBUG_VERSION=

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && if [ -n "${XDEBUG_VERSION}" ]; then \
        pecl install xdebug-${XDEBUG_VERSION}; \
    else \
        pecl install xdebug; \
    fi \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
