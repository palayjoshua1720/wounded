# Build stage for Vue.js
FROM node:18-alpine as frontend-build

WORKDIR /app/frontend
COPY frontend/package*.json ./
RUN npm install
COPY frontend/ .
RUN npm run build

# Build stage for Laravel (use PHP 8.2 so Composer respects lockfile PHP constraints)
FROM php:8.2-cli as backend-build

WORKDIR /app/backend
RUN apt-get update && apt-get install -y --no-install-recommends \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-install zip pdo_mysql && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer --version

COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Final stage
FROM php:8.2-apache

# Install PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql zip gd mbstring exif pcntl bcmath

# Copy Composer from build stage
COPY --from=backend-build /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy Laravel files
COPY backend/ .
COPY --from=frontend-build /app/frontend/dist ./public/

# Copy built Vue assets to Laravel public directory
COPY --from=frontend-build /app/frontend/dist ./public/

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Configure Apache
RUN a2enmod rewrite
COPY backend/.htaccess ./.htaccess
COPY backend/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]
