#!/bin/bash
set -e

echo ">> Running migrations..."
php artisan migrate --force

echo ">> Seeding database (if not already seeded)..."
php artisan db:seed --force 2>/dev/null || echo ">> Seeding skipped (already seeded or error)"

echo ">> Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo ">> Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">> Starting server on port ${PORT:-8080}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
