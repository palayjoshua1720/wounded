# Stage 1: Build Vue.js frontend
FROM node:18-alpine AS frontend-build
WORKDIR /app/frontend
COPY frontend/package*.json ./
RUN npm ci
COPY frontend/ .
RUN npm run build

# Stage 2: Composer dependencies (use Composer 2.x with PHP 8.2 to match runtime)
FROM composer:2 AS backend-build
WORKDIR /app/backend
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader

# Stage 3: Final production image
FROM php:8.2-apache AS final

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl libpng-dev libonig-dev libxml2-dev libzip-dev zip unzip \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring exif pcntl bcmath gd zip \
    && rm -rf /var/lib/apt/lists/*

# Enable rewrite module
RUN a2enmod rewrite

# Critical: Set Apache DocumentRoot to Laravel's public folder
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy Composer binary
COPY --from=backend-build /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy backend code
COPY backend/ .

# Copy vendor from build stage
COPY --from=backend-build /app/backend/vendor ./vendor

# Copy built frontend assets
COPY --from=frontend-build /app/frontend/dist/ ./public/

# Final Composer optimizations
RUN composer dump-autoload --optimize --no-dev

# Permission
RUN chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R 775 storage bootstrap/cache

# Copy entrypoint and make it executable
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Environment
ENV APP_ENV=production \
    APP_DEBUG=false

EXPOSE 80
CMD ["docker-entrypoint.sh"]