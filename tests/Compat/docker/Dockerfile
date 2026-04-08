ARG PHP_IMAGE=php:8.0-cli
FROM ${PHP_IMAGE}
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

ARG XDEBUG_VERSION=

RUN apt-get update \
    || (sed -ri 's@deb.debian.org/debian@archive.debian.org/debian@g; s@security.debian.org/debian-security@archive.debian.org/debian-security@g' /etc/apt/sources.list \
    && sed -ri '/stretch-updates/d; /debian-security/d' /etc/apt/sources.list \
    && printf 'Acquire::Check-Valid-Until "false";\nAcquire::AllowInsecureRepositories "true";\nAPT::Get::AllowUnauthenticated "true";\n' > /etc/apt/apt.conf.d/99archive \
    && apt-get update) \
    && apt-get install -y --allow-unauthenticated --no-install-recommends \
        git \
        unzip \
        libxml2-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install xml \
    && if [ "$XDEBUG_VERSION" = "none" ]; then \
        true; \
    elif [ -n "$XDEBUG_VERSION" ]; then \
        pecl install xdebug-"$XDEBUG_VERSION" \
        && docker-php-ext-enable xdebug; \
    else \
        pecl install xdebug \
        && docker-php-ext-enable xdebug; \
    fi \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
