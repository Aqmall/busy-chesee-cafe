#!/usr/bin/env bash
    2     # Exit on error
    3     set -e
    4 
    5     # Install frontend dependencies and build assets
    6     npm install
    7     npm run build
    8 
    9     # Install PHP dependencies
   10     composer install --no-dev --optimize-autoloader
   11 
   12     # Run database migrations
   13     php artisan migrate --force
   14 
   15     # Cache configuration
   16     php artisan config:cache
   17     php artisan route:cache
   18     php artisan view:cache