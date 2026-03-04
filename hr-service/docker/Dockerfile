FROM php:8.4-fpm-alpine

# Build deps: install, compile extensions, then remove
RUN apk add --no-cache --virtual .build-deps \
    icu-dev libzip-dev oniguruma-dev postgresql-dev \
    && apk add --no-cache icu-libs libzip postgresql-libs \
    && docker-php-ext-install pdo pdo_pgsql intl zip \
    && apk del .build-deps

# Runtime dependencies
RUN apk add --no-cache bash git curl unzip nginx

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-progress --prefer-dist

# Copy the rest
COPY . .

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set permissions for the application
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R ug+rwx /var/www/html/storage \
    && chmod -R ug+rwx /var/www/html/bootstrap/cache

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]