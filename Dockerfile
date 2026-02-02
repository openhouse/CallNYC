FROM php:8.2-apache

RUN a2enmod rewrite \
  && docker-php-ext-install pdo_mysql

RUN sed -i 's/Listen 80/Listen 5000/' /etc/apache2/ports.conf \
  && sed -i 's/:80>/:5000>/' /etc/apache2/sites-available/000-default.conf

RUN printf '<Directory /var/www/html>\nAllowOverride All\n</Directory>\n' > /etc/apache2/conf-available/allow-override.conf \
  && a2enconf allow-override

WORKDIR /var/www/html
COPY . /var/www/html

EXPOSE 5000
