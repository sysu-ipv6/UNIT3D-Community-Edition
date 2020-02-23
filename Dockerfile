FROM node:13 as node
COPY . /app
WORKDIR /app
RUN npm install && npm install --save-dev socket.io-client && npm run prod && rm -rf node_modules

FROM composer:1.9 as composer
FROM php:7.4-fpm-alpine
ENV COMPOSER_MEMORY_LIMIT=-1
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN set -xe \
    && apk add --update \
        icu openssl-dev pcre-dev curl zip unzip git \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        zlib-dev \
        icu-dev \
        build-base \
    && pecl install swoole \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl bcmath pdo pdo_mysql \
    && docker-php-ext-enable intl bcmath pdo pdo_mysql swoole \
    && { find /usr/local/lib -type f -print0 | xargs -0r strip --strip-all -p 2>/dev/null || true; } \
    && apk del .build-deps \
    && rm -rf /tmp/* /usr/local/lib/php/doc/* /var/cache/apk/* /usr/src/nginx/*

COPY ./docker/php/*.conf /usr/local/etc/php-fpm.d/

WORKDIR /app
COPY --from=node /app/composer.* /app/
RUN composer install --prefer-dist --no-autoloader --no-scripts --no-dev --quiet && composer require --prefer-dist --no-scripts --quiet swooletw/laravel-swoole 
COPY --from=node /app /app

RUN set -xe \
    && chown -R www-data: storage bootstrap public config && find . -type d -exec chmod 0775 '{}' + -or -type f -exec chmod 0644 '{}' + \
    && composer install --prefer-dist --optimize-autoloader --no-dev --quiet \
    && php artisan vendor:publish --tag=laravel-swoole \
    && rm -rf /usr/bin/composer

USER www-data
CMD ["/bin/sh", "-c", "php artisan swoole:http start"]