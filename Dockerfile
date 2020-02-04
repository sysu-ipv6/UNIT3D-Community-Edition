FROM node:13 as node
COPY . /app
WORKDIR /app
RUN npm install && npm install --save-dev socket.io-client && npm run prod
FROM composer:1.9 as composer
FROM php:7.4-fpm
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --from=node /app /app
WORKDIR /app
RUN apt-get update && apt-get install -y zlib1g-dev libicu-dev g++ git curl wget zip unzip build-essential
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl bcmath
RUN set -xe \
 && composer install --no-dev --no-scripts --no-suggest --no-interaction --prefer-dist --optimize-autoloader \
 && composer require predis/predis
# RUN composer dump-autoload --no-dev --optimize --classmap-authoritative
CMD ["php-fpm", "--nodaemonize"]