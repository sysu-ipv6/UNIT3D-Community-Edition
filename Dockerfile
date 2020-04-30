FROM node:14 as node
COPY package*.json /app/
WORKDIR /app
RUN npm install --no-optional && npm install --no-optional --save-dev socket.io-client
COPY resources /app/resources
COPY public /public
COPY webpack.mix.js /app
RUN npm run prod && rm -rf node_modules

FROM composer:1.10 as composer
FROM php:7.4-cli-alpine
ENV COMPOSER_MEMORY_LIMIT=-1
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN set -xe \
    && apk add --no-cache --update \
        icu openssl pcre mysql-client mariadb-connector-c libcurl curl-dev curl zip unzip libzip-dev git freetype libpng libjpeg-turbo \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        zlib-dev freetype-dev libpng-dev libjpeg-turbo-dev openssl-dev pcre-dev \
        icu-dev \
        build-base \
    && pecl install swoole \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        intl bcmath pdo pdo_mysql gd zip curl \
    && docker-php-ext-enable intl bcmath pdo pdo_mysql swoole gd zip curl \
    && { find /usr/local/lib -type f -print0 | xargs -0r strip --strip-all -p 2>/dev/null || true; } \
    && apk del .build-deps \
    && rm -rf /tmp/* /usr/local/lib/php/doc/* /var/cache/apk/* /usr/src/nginx/*
    
WORKDIR /app
COPY composer.* /app/
RUN composer install --prefer-dist --no-autoloader --no-scripts --no-dev --quiet 
COPY . /app
COPY --from=node /public /app/public

RUN set -xe \
    && cp .env.testing .env \
    && chown -R www-data: storage bootstrap public config && find . -type d -exec chmod 0775 '{}' + -or -type f -exec chmod 0644 '{}' + \
    && composer require --prefer-dist --quiet swooletw/laravel-swoole robinwongm/tjupt-to-unit3d \
    && composer install --prefer-dist --optimize-autoloader --no-dev --quiet \
    && php artisan vendor:publish --tag=laravel-swoole \
    && rm .env

USER www-data
CMD ["php", "artisan", "swoole:http", "start"]