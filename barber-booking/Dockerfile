# Dockerfile for Barber Booking App - Dokploy Deployment
# Multistage build: vendor → assets → php-fpm → nginx

ARG PHP_VERSION=8.2

# ─────────────────────────────────────────────
# Stage 1: Vendor — Composer dependencies
# ─────────────────────────────────────────────
FROM composer:latest AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-progress \
    --optimize-autoloader \
    --ignore-platform-req=ext-* \
    --ignore-platform-req=php

# ─────────────────────────────────────────────
# Stage 2: Assets — Build Vue/Vite with Node
# ─────────────────────────────────────────────
FROM node:25-alpine AS assets

WORKDIR /app

# Copy package files (leverage cache)
COPY package*.json ./
RUN npm ci

# Copy source + vendor (needed for ziggy import)
COPY . .
COPY --from=vendor /app/vendor ./vendor

# Build assets with Vite
RUN npm run build

# ─────────────────────────────────────────────
# Stage 3: PHP-FPM (Laravel Runtime)
# ─────────────────────────────────────────────
FROM php:${PHP_VERSION}-fpm-alpine AS php-fpm

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    git \
    unzip \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    postgresql-dev \
    sqlite-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        intl \
        opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory for Laravel
WORKDIR /var/www

# Copy vendor from vendor stage (already optimized)
COPY --from=vendor /app/vendor ./vendor

# Copy composer files
COPY composer.json composer.lock ./

# Copy the rest of the application
COPY . .

# Copy compiled assets from assets stage
COPY --from=assets /app/public/build ./public/build

# Copy production PHP configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/production.ini

# Ensure SQLite database file exists (for cache locks / database cache driver)
RUN touch database/database.sqlite

# Set proper permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database/database.sqlite \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose PHP-FPM port (internal)
EXPOSE 9000

# ─────────────────────────────────────────────
# Stage 4: Nginx (Web Server - Final Stage)
# ─────────────────────────────────────────────
FROM nginx:alpine AS nginx

# Copy the entire application from php-fpm stage
COPY --from=php-fpm /var/www /var/www

# Copy custom Nginx configuration
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Remove default nginx config if it exists
RUN rm -f /etc/nginx/conf.d/default.conf.bak

# Expose port 80 for web traffic
EXPOSE 80

# Start Nginx in foreground mode
CMD ["nginx", "-g", "daemon off;"]
