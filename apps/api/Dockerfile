# ---- Stage 1: install PHP dependencies with Composer ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --optimize

# ---- Stage 2: runtime image ----
FROM php:8.4-fpm-alpine AS runtime

# Installed libzip, icu-libs, and pcntl for Reverb/Queues.
# nginx + supervisor added so this image actually serves over php-fpm
# instead of Laravel's dev-only `artisan serve` — see docker/*.conf below
# and PROJECT_STATE.md Known Issues for why that mattered.
RUN apk add --no-cache postgresql-libs sqlite-libs curl bash libzip icu-libs nginx supervisor \
    && apk add --no-cache --virtual .build-deps \
        postgresql-dev sqlite-dev libzip-dev icu-dev oniguruma-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_pgsql pgsql pdo_sqlite bcmath zip intl opcache pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html

RUN addgroup -g 1000 www && adduser -G www -u 1000 -s /bin/sh -D www \
    && mkdir -p storage/app/public storage/framework/views storage/framework/sessions storage/framework/cache bootstrap/cache public \
    && mkdir -p /run/nginx /var/lib/nginx/tmp /var/log/nginx /var/log/supervisor \
    && sed -i 's/^user nginx;/user www;/' /etc/nginx/nginx.conf \
    && chown -R www:www /var/www/html /run/nginx /var/lib/nginx /var/log/nginx /var/log/supervisor

COPY docker/fpm-pool.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor.d/supervisord.ini

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www

EXPOSE 8000
ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor.d/supervisord.ini"]
