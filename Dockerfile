FROM node:13 as node
COPY . /app
WORKDIR /app
RUN npm install && npm install --save-dev socket.io-client && npm run prod
FROM composer:1.9 as composer
FROM php:7.4-fpm
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=node /app /app
WORKDIR /app
RUN apt-get update && apt-get install -y zlib1g-dev libicu-dev g++ git curl wget zip unzip build-essential libpcre3 libpcre3-dev openssl libssl-dev apt-utils 
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl bcmath
RUN set -xe \
 && composer install \
 && composer require predis/predis
# RUN composer dump-autoload --no-dev --optimize --classmap-authoritative
COPY ./docker/php/*.conf /usr/local/etc/php-fpm.d/

# Build nginx
RUN mkdir -p /usr/src/nginx \
    && mkdir -p /var/lib/nginx \
    && curl -SL http://nginx.org/download/nginx-1.17.3.tar.gz \
    | tar -xzC /usr/src/nginx
RUN cd /usr/src/nginx/nginx-1.17.3 \
    && ./configure --prefix=/usr/share/nginx  --sbin-path=/usr/sbin/nginx --modules-path=/usr/lib/nginx/modules --conf-path=/etc/nginx/nginx.conf --error-log-path=/var/log/nginx/error.log --http-log-path=/var/log/nginx/access.log --pid-path=/run/nginx.pid --lock-path=/var/lock/nginx.lock --http-client-body-temp-path=/var/lib/nginx/body --http-fastcgi-temp-path=/var/lib/nginx/fastcgi --http-proxy-temp-path=/var/lib/nginx/proxy --http-scgi-temp-path=/var/lib/nginx/scgi --http-uwsgi-temp-path=/var/lib/nginx/uwsgi --user=www-data --group=www-data --with-http_realip_module  --with-http_ssl_module --with-http_v2_module --with-http_auth_request_module \
    && make -j$(nproc) && make install

COPY ./docker/nginx/default.conf /etc/nginx/nginx.conf
CMD ["/bin/sh", "-c", "nginx && php-fpm"]