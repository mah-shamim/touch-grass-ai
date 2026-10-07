FROM php:8.5.11-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev libcurl4-openssl-dev curl unzip git \
    && docker-php-ext-install pdo_sqlite curl \
    && a2enmod rewrite headers expires \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html

RUN composer dump-autoload --no-interaction -o \
    && mkdir -p /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/storage

EXPOSE 80
CMD ["apache2-foreground"]
