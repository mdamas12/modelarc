#!/usr/bin/env bash
# Bootstrap Modelarc on Ubuntu 24.04 DigitalOcean droplet.
# Usage (as root): bash /var/www/modelarc/deploy/setup-server.sh
set -euo pipefail

REPO_DIR="${REPO_DIR:-/var/www/modelarc}"
DOMAIN_WEB="modelarcve.com"
DOMAIN_ADMIN="admin.modelarcve.com"
DOMAIN_API="api.modelarcve.com"
API_URL="https://${DOMAIN_API}/api"
EMAIL_SSL="${EMAIL_SSL:-info@modelarcve.com}"

export DEBIAN_FRONTEND=noninteractive

echo "==> Updating apt"
apt-get update -y
apt-get upgrade -y

echo "==> Installing base packages"
apt-get install -y \
  ca-certificates curl gnupg lsb-release software-properties-common \
  git unzip zip ufw fail2ban \
  nginx certbot python3-certbot-nginx \
  redis-tools mysql-client \
  supervisor

echo "==> Installing PHP 8.3 + extensions"
apt-get install -y \
  php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-pgsql \
  php8.3-sqlite3 php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip \
  php8.3-gd php8.3-bcmath php8.3-intl php8.3-redis php8.3-tokenizer \
  php8.3-opcache

# Upload limits for panoramas
PHP_INI="/etc/php/8.3/fpm/php.ini"
sed -i 's/^upload_max_filesize.*/upload_max_filesize = 128M/' "$PHP_INI"
sed -i 's/^post_max_size.*/post_max_size = 140M/' "$PHP_INI"
sed -i 's/^memory_limit.*/memory_limit = 512M/' "$PHP_INI"
sed -i 's/^max_execution_time.*/max_execution_time = 300/' "$PHP_INI"
systemctl enable --now php8.3-fpm
systemctl restart php8.3-fpm

echo "==> Installing Composer"
if ! command -v composer >/dev/null 2>&1; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo "==> Installing Node.js 22"
if ! command -v node >/dev/null 2>&1 || [[ "$(node -v | cut -d. -f1 | tr -d v)" -lt 22 ]]; then
  curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
  apt-get install -y nodejs
fi

echo "==> Installing Docker Engine + Compose plugin"
if ! command -v docker >/dev/null 2>&1; then
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg | gpg --dearmor -o /etc/apt/keyrings/docker.gpg
  chmod a+r /etc/apt/keyrings/docker.gpg
  echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
    $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
    > /etc/apt/sources.list.d/docker.list
  apt-get update -y
  apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
  systemctl enable --now docker
fi

echo "==> Firewall"
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable || true

mkdir -p "$REPO_DIR"
cd "$REPO_DIR"

if [[ ! -f .env ]]; then
  if [[ -f deploy/env.docker.production.example ]]; then
    cp deploy/env.docker.production.example .env
    # Generate strong passwords if placeholders remain
    if grep -q 'CHANGE_ME' .env; then
      MYSQL_PASS=$(openssl rand -base64 24 | tr -d '/+=' | head -c 24)
      ROOT_PASS=$(openssl rand -base64 24 | tr -d '/+=' | head -c 24)
      sed -i "s/CHANGE_ME_STRONG_PASSWORD/${MYSQL_PASS}/" .env
      sed -i "s/CHANGE_ME_ROOT_PASSWORD/${ROOT_PASS}/" .env
      echo "Generated Docker DB passwords in ${REPO_DIR}/.env"
    fi
  fi
fi

echo "==> Starting MySQL + Redis (Docker)"
docker compose up -d
sleep 8
docker compose ps

echo "==> Composer install (API)"
cd "$REPO_DIR/apps/api"
if [[ ! -f .env ]]; then
  echo "ERROR: apps/api/.env missing. Copy it from local before continuing."
  exit 1
fi
composer install --no-dev --optimize-autoloader --no-interaction

# Ensure production URLs
php -r '
$path = ".env";
$env = file_get_contents($path);
$replacements = [
  "APP_ENV=local" => "APP_ENV=production",
  "APP_DEBUG=true" => "APP_DEBUG=false",
];
foreach ($replacements as $from => $to) {
  $env = str_replace($from, $to, $env);
}
file_put_contents($path, $env);
'

# Patch key production settings via artisan / sed helpers
set_env() {
  local key="$1" val="$2" file="$REPO_DIR/apps/api/.env"
  if grep -q "^${key}=" "$file"; then
    sed -i "s|^${key}=.*|${key}=${val}|" "$file"
  else
    echo "${key}=${val}" >> "$file"
  fi
}

# Read docker DB password
source "$REPO_DIR/.env"
set_env APP_URL "https://${DOMAIN_API}"
set_env FRONTEND_URL "https://${DOMAIN_WEB}"
set_env ADMIN_URL "https://${DOMAIN_ADMIN}"
set_env DB_HOST "127.0.0.1"
set_env DB_PORT "3306"
set_env DB_DATABASE "${MYSQL_DATABASE}"
set_env DB_USERNAME "${MYSQL_USER}"
set_env DB_PASSWORD "\"${MYSQL_PASSWORD}\""
set_env REDIS_HOST "127.0.0.1"
set_env REDIS_PORT "${REDIS_PORT:-6379}"
set_env CACHE_STORE "redis"
set_env QUEUE_CONNECTION "database"
set_env FILESYSTEM_DISK "public"
set_env CORS_ALLOWED_ORIGINS "https://${DOMAIN_WEB},https://www.${DOMAIN_WEB},https://${DOMAIN_ADMIN}"
set_env SANCTUM_STATEFUL_DOMAINS "${DOMAIN_WEB},www.${DOMAIN_WEB},${DOMAIN_ADMIN},${DOMAIN_API}"
set_env SESSION_DOMAIN ".${DOMAIN_WEB}"

# Wait for MySQL
echo "==> Waiting for MySQL"
for i in $(seq 1 30); do
  if docker exec modelarc-mysql mysqladmin ping -h localhost -uroot -p"${MYSQL_ROOT_PASSWORD}" --silent 2>/dev/null; then
    break
  fi
  sleep 2
done

php artisan key:generate --force || true
php artisan migrate --force
php artisan db:seed --force || true
php artisan storage:link || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

echo "==> Build web + admin"
cd "$REPO_DIR/apps/web"
npm ci
VITE_API_URL="${API_URL}" npm run build

cd "$REPO_DIR/apps/admin"
npm ci
VITE_API_URL="${API_URL}" npm run build

echo "==> Nginx sites"
cp "$REPO_DIR/deploy/nginx/${DOMAIN_WEB}.conf" /etc/nginx/sites-available/
cp "$REPO_DIR/deploy/nginx/${DOMAIN_ADMIN}.conf" /etc/nginx/sites-available/
cp "$REPO_DIR/deploy/nginx/${DOMAIN_API}.conf" /etc/nginx/sites-available/
ln -sfn /etc/nginx/sites-available/${DOMAIN_WEB}.conf /etc/nginx/sites-enabled/
ln -sfn /etc/nginx/sites-available/${DOMAIN_ADMIN}.conf /etc/nginx/sites-enabled/
ln -sfn /etc/nginx/sites-available/${DOMAIN_API}.conf /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable --now nginx
systemctl reload nginx

echo "==> SSL certificates (Let's Encrypt)"
certbot --nginx \
  -d "${DOMAIN_WEB}" -d "www.${DOMAIN_WEB}" \
  -d "${DOMAIN_ADMIN}" \
  -d "${DOMAIN_API}" \
  --non-interactive --agree-tos -m "${EMAIL_SSL}" --redirect || {
    echo "WARN: certbot failed (DNS may not be ready). Retry later:"
    echo "  certbot --nginx -d ${DOMAIN_WEB} -d www.${DOMAIN_WEB} -d ${DOMAIN_ADMIN} -d ${DOMAIN_API} --redirect"
  }

echo "==> Queue worker"
cp "$REPO_DIR/deploy/systemd/modelarc-queue.service" /etc/systemd/system/
systemctl daemon-reload
systemctl enable --now modelarc-queue.service

echo "==> Permissions"
chown -R www-data:www-data "$REPO_DIR/apps/api/storage" "$REPO_DIR/apps/api/bootstrap/cache"
chown -R www-data:www-data "$REPO_DIR/apps/web/dist" "$REPO_DIR/apps/admin/dist" || true

echo ""
echo "============================================"
echo " Modelarc setup complete"
echo " Web:   https://${DOMAIN_WEB}"
echo " Admin: https://${DOMAIN_ADMIN}"
echo " API:   https://${DOMAIN_API}"
echo "============================================"
