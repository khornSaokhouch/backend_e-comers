FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libjpeg-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip curl git \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

# Copy nginx config
COPY ./docker/nginx.conf /etc/nginx/sites-available/default

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

EXPOSE 8000

# Start PHP-FPM and Nginx together
CMD service nginx start && php-fpm
