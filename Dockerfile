FROM php:cli

RUN docker-php-ext-configure pdo_mysql && docker-php-ext-install -j$(nproc) pdo_mysql

