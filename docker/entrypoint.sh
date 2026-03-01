#!/bin/bash
set -e

composer install --no-interaction --optimize-autoloader

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

php artisan migrate --force
php artisan serve --host=0.0.0.0 --port=8000
