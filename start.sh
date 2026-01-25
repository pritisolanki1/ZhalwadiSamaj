#!/bin/bash
set -e

echo "Starting Laravel application on Railway..."

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    composer install --no-dev --optimize-autoloader
fi

# Clear all caches
echo "Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate
fi

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database if needed (uncomment if required)
# php artisan db:seed --force

echo "Application startup complete. Starting server..."

# Start the application
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
