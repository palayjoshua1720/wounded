# Stage 1: Build Vue.js frontend
FROM node:18-alpine AS frontend-build
WORKDIR /app/frontend
COPY frontend/package*.json ./
RUN npm ci
COPY frontend/ .
RUN npm run build

# Stage 2: Composer dependencies (cached layer)
FROM composer:latest AS backend-build
WORKDIR /app/backend
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader

# Stage 3: Final production image
FROM php:8.2-apache AS final

# Install system dependencies and PHP extensions required/recommended for Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        fileinfo \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite module (needed for Laravel pretty URLs)
RUN a2enmod rewrite

# Set Apache DocumentRoot to Laravel's public folder (best practice)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy Composer binary
COPY --from=backend-build /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy Laravel backend code
COPY backend/ .

# Copy full Composer dependencies from build stage
COPY --from=backend-build /app/backend/vendor ./vendor

# Copy built Vue assets into public/
COPY --from=frontend-build /app/frontend/dist/ ./public/

# Final Composer optimizations
RUN composer dump-autoload --optimize --no-dev \
    && composer install --no-dev --optimize-autoloader --no-interaction

# Permissions for Laravel storage and cache
RUN chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R 775 storage bootstrap/cache

# Optional: Copy your custom Apache config if you still need overrides
# COPY backend/apache-config.conf /etc/apache2/sites-available/000-default.conf

# Environment variables (production defaults)
ENV APP_ENV=production \
    APP_DEBUG=false

EXPOSE 80
CMD ["apache2-foreground"]