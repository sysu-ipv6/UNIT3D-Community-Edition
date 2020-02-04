FROM composer:latest as composer

COPY composer.* /app/
 
# Run composer to build dependencies in vendor folder
RUN set -xe \
 && composer install --no-dev --no-scripts --no-suggest --no-interaction --prefer-dist --optimize-autoloader
 
# Copy everything from project root into composer container's working dir
COPY . /app
 
# Generated optimized autoload files containing all classes from vendor folder and project itself
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative
 
#
# STAGE 2: php
#
FROM php:fpm
 
# Set container's working dir
WORKDIR /app
 
# Copy everything from project root into php container's working dir
COPY . /app
# Copy vendor folder from composer container into php container
COPY --from=composer /app/vendor /app/vendor
 
# Copy necessary files
COPY docker/php/php.ini /usr/local/etc/php/conf.d/php.override.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
 
CMD ["php-fpm", "--nodaemonize"]