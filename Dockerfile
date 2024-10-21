# Use the official PHP image as the base image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev 

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN pecl install redis && docker-php-ext-enable redis  # Install and enable PHP Redis extension

# Install Composer
COPY --from=composer:2.5 /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www/html

# Copy .env.example to .env
# COPY .env.example .env

# Set folders permissions
RUN chown -R www-data:www-data /var/www/html/storage
RUN chmod -R 775 /var/www/html/storage
RUN chown www-data:www-data /var/www/html/public
RUN chown www-data:www-data /var/www/html/bootstrap/cache

# Install Laravel dependencies
RUN composer install --prefer-dist --no-progress --no-interaction

# Copy custom entrypoint script into the container
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Make the entrypoint script executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set entrypoint
ENTRYPOINT [ "entrypoint.sh" ]

# Expose port 9000 for PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]