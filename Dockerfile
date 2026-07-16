FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    nodejs \
    npm \
    oniguruma-dev \
    postgresql-dev \
    sqlite-dev \
    unzip \
    && docker-php-ext-install bcmath intl mbstring opcache pdo pdo_pgsql pdo_sqlite zip

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/performance.ini /usr/local/etc/php/conf.d/performance.ini
COPY docker/php/fpm-pool.conf /usr/local/etc/php-fpm.d/zz-performance.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g 1000 laravel && adduser -D -G laravel -u 1000 laravel

CMD ["php-fpm"]
