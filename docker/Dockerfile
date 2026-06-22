FROM php:8.4-fpm

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    git \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip dom \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files first
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-req=php

# Copy rest of application
COPY . .

# Fix permissions
RUN chown -R www-data:www-data /app

EXPOSE 9000

CMD ["php-fpm"]
