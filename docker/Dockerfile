FROM php:8.3-fpm

WORKDIR /app

# Install all required PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    zip \
    dom \
    gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
COPY . .

# Set permissions
RUN chown -R www-data:www-data /app && chmod -R 755 /app/storage /app/bootstrap/cache

EXPOSE 9000

CMD ["sh", "-c", "if [ ! -d vendor ]; then composer install --no-interaction --ignore-platform-req=php; fi && php-fpm"]
