FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first
COPY symfony/composer.json symfony/composer.lock /var/www/

# Install dependencies
RUN composer install --no-scripts --no-autoloader

# Copy application files
COPY symfony/ /var/www/

# Generate autoloader
RUN composer dump-autoload --optimize

# Apache configuration
RUN a2enmod rewrite
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]