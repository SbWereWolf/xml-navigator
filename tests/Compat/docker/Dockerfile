ARG PHP_IMAGE=php:8.0-cli
FROM ${PHP_IMAGE}
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

ARG XDEBUG_VERSION=

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libxml2-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install xml \
    && if [ -n "$XDEBUG_VERSION" ]; then \
        pecl install xdebug-"$XDEBUG_VERSION"; \
    else \
        pecl install xdebug; \
    fi \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
