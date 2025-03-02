# Use the official PHP image with Apache and necessary dependencies
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies for PHP extensions, Composer, and other necessary tools
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    libzip-dev \
    unzip \
    curl \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd intl zip pdo pdo_mysql \
    && a2enmod rewrite

# Install Composer (PHP package manager)
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the timezone (optional)
ENV TZ=UTC
RUN apt-get install -y tzdata && \
    ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    dpkg-reconfigure -f noninteractive tzdata

# Copy application code into the container
COPY . .

# Install Laravel dependencies using Composer
RUN composer install --no-dev --optimize-autoloader

# Expose port 80 for the Apache server
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
