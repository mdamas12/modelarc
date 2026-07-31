#!/usr/bin/env bash
# Redeploy after git pull. Run as root on the droplet.
set -euo pipefail

REPO_DIR="${REPO_DIR:-/var/www/modelarc}"
API_URL="${API_URL:-https://api.modelarcve.com/api}"

cd "$REPO_DIR"
git fetch origin
git checkout master
git pull --ff-only origin master

cd "$REPO_DIR/apps/api"
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache

cd "$REPO_DIR/apps/web"
npm ci
VITE_API_URL="${API_URL}" npm run build

cd "$REPO_DIR/apps/admin"
npm ci
VITE_API_URL="${API_URL}" npm run build

systemctl reload php8.3-fpm
systemctl restart modelarc-queue.service
systemctl reload nginx

echo "Deploy OK"
