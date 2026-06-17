# syntax=docker/dockerfile:1

# ---- Composer dependencies ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction

# ---- Runtime image ----
FROM php:8.5-fpm-alpine AS runtime

# Web server + process manager
RUN apk add --no-cache nginx supervisor

# PHP extensions via the extension installer (resolves versions per PHP release)
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions \
        pdo_mysql \
        bcmath \
        zip \
        intl \
        pcntl \
        opcache \
        redis

WORKDIR /var/www/html

# Composer binary (needed to finish the autoloader below)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Application code + production vendor
COPY --from=vendor /app/vendor ./vendor
COPY . .

# Finish the autoloader and run package discovery now that the full app is present
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && php artisan package:discover --ansi

# Config files
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/nginx.conf /etc/nginx/nginx.conf
COPY docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Writable dirs owned by the php-fpm user
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
