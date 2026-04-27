FROM php:8.2-apache

# Update les packages du systeme pour réduire les vulnérabilités
# RUN apt-get update && apt-get upgrade -y && apt-get dist-upgrade -y && apt-get autoremove -y && apt-get clean

# Active rewrite mode
RUN a2enmod rewrite

# Installe les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copie du fichier de configuration Apache
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html