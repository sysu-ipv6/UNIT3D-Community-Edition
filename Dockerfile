FROM node:13 as node
COPY . /app
WORKDIR /app
RUN npm install && npm install --save-dev socket.io-client && npm run prod && rm -rf node_modules
FROM composer:1.9 as composer
FROM php:7.4-fpm-alpine
ENV NGINX_VERSION 1.17.6
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN set -xe \
    && apk add --update \
        icu openssl-dev pcre-dev wget curl zip unzip git \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        zlib-dev \
        icu-dev \
        build-base \
    && mkdir -p /usr/src/nginx \
    && mkdir -p /var/lib/nginx \
    && curl -SL http://nginx.org/download/nginx-${NGINX_VERSION}.tar.gz | tar -xzC /usr/src/nginx \
    && cd /usr/src/nginx/nginx-${NGINX_VERSION} \
    && ./configure --prefix=/usr/share/nginx  --sbin-path=/usr/sbin/nginx --modules-path=/usr/lib/nginx/modules --conf-path=/etc/nginx/nginx.conf --error-log-path=/var/log/nginx/error.log --http-log-path=/var/log/nginx/access.log --pid-path=/run/nginx.pid --lock-path=/var/lock/nginx.lock --http-client-body-temp-path=/var/lib/nginx/body --http-fastcgi-temp-path=/var/lib/nginx/fastcgi --http-proxy-temp-path=/var/lib/nginx/proxy --http-scgi-temp-path=/var/lib/nginx/scgi --http-uwsgi-temp-path=/var/lib/nginx/uwsgi --user=www-data --group=www-data --with-http_realip_module  --with-http_ssl_module --with-http_v2_module --with-http_auth_request_module \
    && make -j$(nproc) && make install \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        intl bcmath pdo pdo_mysql \
    && docker-php-ext-enable intl bcmath pdo pdo_mysql \
    && { find /usr/local/lib -type f -print0 | xargs -0r strip --strip-all -p 2>/dev/null || true; } \
    && apk del .build-deps \
    && rm -rf /tmp/* /usr/local/lib/php/doc/* /var/cache/apk/* /usr/src/nginx/*

COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/php/*.conf /usr/local/etc/php-fpm.d/

WORKDIR /app
COPY --from=node /app/composer.* /app/
RUN composer install --no-autoloader --no-scripts --no-dev
COPY --from=node /app /app

RUN set -xe \
    && chown -R www-data: storage bootstrap public config && find . -type d -exec chmod 0755 '{}' + -or -type f -exec chmod 0644 '{}' + \
    && composer install --no-dev \
    && rm -rf /usr/bin/composer

CMD ["/bin/sh", "-c", "nginx && php-fpm"]