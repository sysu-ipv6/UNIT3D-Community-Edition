FROM composer:1.9 as composer
FROM php:7.4-fpm
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY . /app
RUN set -xe \
 && composer install --no-dev --no-scripts --no-suggest --no-interaction --prefer-dist --optimize-autoloader
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative
CMD ["php-fpm", "--nodaemonize"]