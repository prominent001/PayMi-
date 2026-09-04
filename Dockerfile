FROM php:8.2-apache

# Install Postgres PDO driver
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copy your code
COPY . /var/www/html/

# Enable Apache rewrite for routes like /migrate
RUN a2enmod rewrite
RUN echo 'AllowOverride All' >> /etc/apache2/apache2.conf
