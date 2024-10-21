#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Generate application key
echo "Generating application key..."
php artisan key:generate

# Run migrations and seed the database
echo "Running php artisan optimize"
php artisan optimize

# Run the main PHP-FPM process 
exec "$@"