FROM composer:2 AS composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts

FROM php:8.4-fpm-alpine
RUN apk add --no-cache libpq-dev linux-headers postgresql-client $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del linux-headers $PHPIZE_DEPS
WORKDIR /var/www/html
COPY --from=composer /app/vendor ./vendor
COPY . .
RUN chown -R www-data:www-data storage bootstrap/cache
EXPOSE 9000
CMD ["php-fpm"]
