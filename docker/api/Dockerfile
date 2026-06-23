FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    oniguruma-dev \
    autoconf \
    g++ \
    make \
    && docker-php-ext-install \
        pdo_pgsql \
        intl \
        bcmath \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/api
