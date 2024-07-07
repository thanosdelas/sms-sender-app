# Use PHP 8.1 official image as base
FROM php:8.1.29-fpm

# Install dependencies
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       libpq-dev \
       libpng-dev \
       libjpeg-dev \
       libfreetype6-dev \
       libonig-dev \
       libxml2-dev \
       zip \
       unzip \
       supervisor \
       redis-tools \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo pdo_pgsql gd mbstring xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port 8000 and start PHP-FPM server
EXPOSE 8000

# Start PHP-FPM
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]
