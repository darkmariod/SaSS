# Dockerfile for Barber Booking App - Dokploy Deployment
# Multistage build: node-builder → php-fpm → nginx

ARG PHP_VERSION=8.2
ARG NODE_VERSION=25

# ─────────────────────────────────────────────
# Stage 1: Node Builder (Compile Vue/Vite Assets)
# ─────────────────────────────────────────────
FROM node:${NODE_VERSION}-alpine AS node-builder

# Set working directory
WORKDIR /app

# Copy package files first (leverage Docker cache)
COPY package*.json ./

# Install Node dependencies
RUN npm ci

# Copy all source code
COPY . .

# Build assets with Vite
RUN npm run build

# ─────────────────────────────────────────────
# Stage 2: PHP-FPM (Laravel Runtime)
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

# Copy composer files first (leverage Docker cache)
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev for production)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copy the rest of the application
COPY . .

# Copy compiled assets from node-builder stage
COPY --from=node-builder /app/public/build ./public/build

# Copy production PHP configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/production.ini

# Set proper permissions for storage and bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose PHP-FPM port (internal)
EXPOSE 9000

# ─────────────────────────────────────────────
# Stage 3: Nginx (Web Server - Final Stage)
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
