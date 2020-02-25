FROM node:13 as node
COPY . /app
WORKDIR /app
RUN npm install && npm install --save-dev socket.io-client && npm run prod && rm -rf node_modules

FROM composer:1.9 as composer
FROM php:7.4-cli-alpine
ENV COMPOSER_MEMORY_LIMIT=-1
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN set -xe \
    && apk add --update \
        icu openssl-dev pcre-dev curl zip unzip git freetype libpng libjpeg-turbo \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        zlib-dev freetype-dev libpng-dev libjpeg-turbo-dev \
        icu-dev \
        build-base \
    && pecl install swoole \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd \
        --with-gd \
        --with-freetype-dir=/usr/include/ \
        --with-png-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install \
        intl bcmath pdo pdo_mysql gd \
    && docker-php-ext-enable intl bcmath pdo pdo_mysql swoole gd \
    && { find /usr/local/lib -type f -print0 | xargs -0r strip --strip-all -p 2>/dev/null || true; } \
    && apk del .build-deps \
    && rm -rf /tmp/* /usr/local/lib/php/doc/* /var/cache/apk/* /usr/src/nginx/*
    
WORKDIR /app
COPY --from=node /app/composer.* /app/
RUN composer install --prefer-dist --no-autoloader --no-scripts --no-dev --quiet 
COPY --from=node /app /app

RUN set -xe \
    && chown -R www-data: storage bootstrap public config && find . -type d -exec chmod 0775 '{}' + -or -type f -exec chmod 0644 '{}' + \
    && composer require --prefer-dist --quiet swooletw/laravel-swoole \
    && composer install --prefer-dist --optimize-autoloader --no-dev --quiet \
    && php artisan vendor:publish --tag=laravel-swoole

USER www-data
CMD ["php", "artisan", "swoole:http", "start"]