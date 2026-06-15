# syntax=docker/dockerfile:1
FROM php:8.5.5RC1-apache-bookworm

RUN apt update \
    && apt install zip -y

WORKDIR /var/www/html

# Install Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions xdebug pdo_mysql

COPY composer.* .

RUN composer install --no-interaction

COPY . .

# Dump the autoloader
RUN composer dump-autoload
