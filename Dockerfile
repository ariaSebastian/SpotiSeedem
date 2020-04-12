FROM php:7.3-apache

LABEL maintainer="ariasebastian96@gmail.com"

# Install Linux Packages
RUN apt-get update && apt-get install -y iputils-ping vim unzip libzip-dev zip

# Install PHP extensions
RUN docker-php-ext-install opcache

# Install zip extension
RUN  docker-php-ext-configure zip --with-libzip \
  && docker-php-ext-install zip

RUN a2enmod rewrite \
 && sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/apache2.conf \
 && sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/000-default.conf

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin \
        --filename=composer

WORKDIR /var/www