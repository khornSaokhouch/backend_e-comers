#!/bin/bash

# Exit on error
set -e

# Run Laravel optimizations
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force

# Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000
