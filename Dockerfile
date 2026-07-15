FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    mariadb-client libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip \
    && a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html/atri/
COPY index.php /var/www/html/index.php
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Create lowercase symlink for case-insensitive URL compatibility
RUN ln -sf /var/www/html/atri/Pages /var/www/html/atri/pages

RUN chown -R www-data:www-data /var/www/html/atri /var/www/html/index.php

COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/docker-entrypoint.sh"]
