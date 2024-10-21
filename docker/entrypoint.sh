#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Copy .env.example to .env
echo "cp .env.example to .env..."
cp .env.example .env

# Set Folder pemissions...
echo "Set Folder pemissions..."
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
chown www-data:www-data /var/www/html/public
chown www-data:www-data /var/www/html/bootstrap/cache

# Install Laravel dependencies
echo "Installing Laravel dependencies... composer install"
composer install --prefer-dist --no-progress --no-interaction

# Generate application key
echo "Generating application key..."
php artisan key:generate


# Run php artisan optimize
echo "Running php artisan optimize"
php artisan optimize

# Run the main PHP-FPM process 
exec "$@"