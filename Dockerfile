# Build stage for Vue.js
FROM node:18-alpine as frontend-build
WORKDIR /app/frontend
COPY frontend/package*.json ./
RUN npm ci  # Better than 'npm install' — faster, more reliable in CI
COPY frontend/ .
RUN npm run build

# Intermediate stage for Composer (optional but good for caching)
FROM php:8.2-cli as backend-build
WORKDIR /app/backend
RUN apt-get update && apt- install -y --no-install-recommends \
    zip unzip git curl libzip-dev \
    && docker-php-ext-install zip pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Final production stage
FROM php:8.2-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql zip gd mbstring exif pcntl bcmath \
    && rm -rf /var/lib/apt/lists/*

# Copy Composer binary
COPY --from=backend-build /usr/local/bin/composer /usr/local/bin/composer

# Set document root to public (important!)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copy Laravel backend
COPY backend/ .

# Copy built Vue assets into public/
COPY --from=frontend-build /app/frontend/dist/ ./public/

# Dump autoloader and optimize
RUN composer dump-autoload --optimize --no-dev
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R 775 storage bootstrap/cache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Optional: Copy your custom Apache config (recommended)
COPY backend/apache-config.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
CMD ["apache2-foreground"]