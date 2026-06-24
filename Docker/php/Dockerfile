FROM php:8.5-fpm-alpine

RUN apk add --no-cache \
    postgresql-dev \
    postgresql17-client \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    build-base \
    gzip \
    docker-cli

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    zip \
    bcmath \
    mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN git config --global --add safe.directory /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-req=ext-gd

RUN mkdir -p storage/app/backups \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["sh", "-c", "php-fpm"]
