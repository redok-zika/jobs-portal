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
    unzip \
    gnupg

# Install Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && \
    apt-get update && \
    apt-get install -y symfony-cli

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Create symfony directory
RUN mkdir symfony

# Copy composer files
COPY symfony/composer.json symfony/composer.lock symfony/

# Set working directory to symfony
WORKDIR /var/www/symfony

# Install dependencies
RUN composer install --no-scripts --no-autoloader

# Copy application files
COPY symfony/ .

# Create cache and log directories with proper permissions
RUN mkdir -p var/cache var/log && \
    chown -R www-data:www-data var

# Generate autoloader
RUN composer dump-autoload --optimize

# Apache configuration
RUN a2enmod rewrite && \
    a2dismod ssl && \
    a2dissite default-ssl

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]