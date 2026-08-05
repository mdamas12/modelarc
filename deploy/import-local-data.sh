#!/usr/bin/env bash
set -euo pipefail

ROOT_PASS=$(grep MYSQL_ROOT_PASSWORD /var/www/modelarc/.env | cut -d= -f2-)
DB_PASS=$(grep MYSQL_PASSWORD /var/www/modelarc/.env | cut -d= -f2-)

echo "==> Importing database"
docker exec -i modelarc-mysql mysql -uroot -p"$ROOT_PASS" <<SQL
DROP DATABASE IF EXISTS bd_modelarc;
CREATE DATABASE bd_modelarc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON bd_modelarc.* TO 'modelarc'@'%';
FLUSH PRIVILEGES;
SQL

docker exec -i modelarc-mysql mysql -uroot -p"$ROOT_PASS" bd_modelarc < /tmp/bd_modelarc_dump.sql

echo "==> Table counts"
docker exec -i modelarc-mysql mysql -uroot -p"$ROOT_PASS" -N -e "
SELECT 'media', COUNT(*) FROM media;
SELECT 'projects', COUNT(*) FROM projects;
SELECT 'project_media', COUNT(*) FROM project_media;
SELECT 'users', COUNT(*) FROM users;
SELECT 'services', COUNT(*) FROM services;
SELECT 'virtual_tours', COUNT(*) FROM virtual_tours;
" bd_modelarc

echo "==> Restoring storage files"
mkdir -p /var/www/modelarc/apps/api/storage/app/public
tar -xf /tmp/storage_public.tar -C /var/www/modelarc/apps/api/storage/app/public
cd /var/www/modelarc/apps/api
php artisan storage:link || true
chown -R www-data:www-data storage bootstrap/cache
find storage/app/public -type d -exec chmod 775 {} \;
find storage/app/public -type f -exec chmod 664 {} \;

php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache

echo "==> Sample files"
ls -lah storage/app/public/images | head -10
ls -lah storage/app/public/panoramas 2>/dev/null | head -5 || true
du -sh storage/app/public

echo "==> Done"
