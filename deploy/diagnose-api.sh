#!/usr/bin/env bash
set -euo pipefail

echo "==> Baked API URLs in web build"
grep -RhoE 'https?://[^"'\'' ]+' /var/www/modelarc/apps/web/dist/spa/assets/*.js 2>/dev/null \
  | grep -E 'api\.|localhost|8000' | sort -u | head -30 || true

echo "==> CORS env"
grep -E '^(CORS|APP_URL|FRONTEND)' /var/www/modelarc/apps/api/.env || true

echo "==> OPTIONS preflight"
curl -sI -H 'Origin: https://modelarcve.com' \
  -H 'Access-Control-Request-Method: GET' \
  -H 'Access-Control-Request-Headers: content-type,accept' \
  -X OPTIONS 'https://api.modelarcve.com/api/public/home' | tr -d '\r'

echo "==> GET with Origin"
curl -sI -H 'Origin: https://modelarcve.com' \
  'https://api.modelarcve.com/api/public/home' | tr -d '\r'

echo "==> Endpoint codes"
for p in /api/public/home /api/public/services /api/public/tours /api/public/projects; do
  code=$(curl -s -o /tmp/out.json -w '%{http_code}' "https://api.modelarcve.com$p")
  echo "$code $p"
  head -c 120 /tmp/out.json; echo
done

echo "==> Config cached CORS?"
php -r 'require "/var/www/modelarc/apps/api/vendor/autoload.php"; $app=require "/var/www/modelarc/apps/api/bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); print_r(config("cors.allowed_origins"));'
