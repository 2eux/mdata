FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip \
    && a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html/atri/

# Set DocumentRoot to the atri directory so the app is served from root
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/atri|' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory /var/www/html>|<Directory /var/www/html/atri>|' /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html/atri

EXPOSE 80
CMD ["apache2-foreground"]
