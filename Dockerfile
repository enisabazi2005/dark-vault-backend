# Use the official PHP image with Apache
FROM php:8.1-apache

# Install dependencies
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev unzip git && docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install gd

# Enable mod_rewrite for Laravel
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy the application files
COPY . .

# Install Composer (PHP dependency manager)
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
