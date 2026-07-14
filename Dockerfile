FROM php:8.1-apache
ARG COOLIFY_URL=https://kw61usi79wt8npgq02qzl84e.servo.host
ARG COOLIFY_FQDN=kw61usi79wt8npgq02qzl84e.servo.host
ARG COOLIFY_BRANCH=main
ARG COOLIFY_RESOURCE_UUID=kw61usi79wt8npgq02qzl84e

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip \
    && a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html/atri/
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html/atri

EXPOSE 80
CMD ["apache2-foreground"]