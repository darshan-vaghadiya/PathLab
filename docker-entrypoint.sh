#!/bin/bash
set -e

PORT=${PORT:-8080}

echo ">> Configuring Apache to listen on port $PORT..."
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/*.conf

echo ">> Starting Apache on port $PORT..."
apache2-foreground &
APACHE_PID=$!

echo ">> Running migrations..."
php artisan migrate:fresh --force

echo ">> Seeding database..."
php artisan db:seed --class=DatabaseSeeder --force || echo ">> Seeding failed, continuing..."

echo ">> Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo ">> Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">> Setup complete! Apache is running on port $PORT."
wait $APACHE_PID
