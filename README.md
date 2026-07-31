# Modelarc

Plataforma de arquitectura, construcción y remodelación con sitio público, visor 360° propio y dashboard administrativo.

## Arquitectura

```text
modelarc/
├── apps/
│   ├── api/      → Laravel 13 (API + queues + Sanctum)
│   ├── web/      → Quasar Vue 3 (sitio público, puerto 9400)
│   └── admin/    → Quasar Vue 3 (dashboard, puerto 9401)
├── docker/       → Datos locales MySQL/Redis
├── packages/     → (reservado: tour-viewer compartido)
└── docker-compose.yml
```

| Capa | Tecnología |
|------|------------|
| API | Laravel 13, Sanctum, Spatie Permission, Intervention Image |
| Web pública | Vue 3 + Quasar (SSR disponible) |
| Dashboard | Vue 3 + Quasar SPA |
| BD | MySQL (`bd_modelarc`) — local XAMPP o Docker en droplet |
| Archivos | Disco `public` local o DigitalOcean Spaces |
| Visor 360° | Photo Sphere Viewer |

## Requisitos

- PHP 8.3+ (extensiones: `pdo_mysql`, `mysqli`)
- Composer 2
- Node.js 22.12+
- npm 10+
- MySQL 8+ (XAMPP local o Docker en servidor)
- (Opcional) Docker Desktop / Docker en droplet para MySQL + Redis

## Arranque rápido (desarrollo)

### 1. API

Crea la base `bd_modelarc` en MySQL (XAMPP) si aún no existe, y configura `apps/api/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bd_modelarc
DB_USERNAME=root
DB_PASSWORD=
```

```bash
cd apps/api
composer install
copy .env.example .env   # si no existe .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

> **Panoramas grandes:** PHP debe permitir cuerpos > tamaño del archivo. En XAMPP (`C:\xampp\php\php.ini`) usa al menos `post_max_size=128M` (y reinicia `php artisan serve`). La API acepta hasta 100 MB por archivo.

Credenciales admin:

- Email: `admin@modelarc.com`
- Password: `password`

### 2. Sitio público

```bash
cd apps/web
npm install
npm run dev
```

→ http://localhost:9400

### 3. Dashboard

```bash
cd apps/admin
npm install
npm run dev
```

→ http://localhost:9401

Login con las mismas credenciales del API.

## Scripts raíz

Desde `c:\modelarc`:

```bash
npm run dev:api
npm run dev:web
npm run dev:admin
npm run fresh
```

## API principal

### Público

- `GET /api/public/home`
- `GET /api/public/projects`
- `GET /api/public/projects/{slug}`
- `GET /api/public/projects/{slug}/tour`
- `GET /api/public/tours/{slug}`
- `GET /api/public/services`
- `POST /api/public/contact`

### Admin (Bearer token)

- `POST /api/admin/login`
- `GET /api/admin/dashboard`
- CRUD proyectos, tours, escenas, hotspots, medios, servicios, testimonios, leads, settings

## DigitalOcean Spaces

En `apps/api/.env`:

```env
FILESYSTEM_DISK=spaces
DO_SPACES_KEY=
DO_SPACES_SECRET=
DO_SPACES_REGION=nyc3
DO_SPACES_BUCKET=modelarc
DO_SPACES_ENDPOINT=https://nyc3.digitaloceanspaces.com
DO_SPACES_URL=https://modelarc.nyc3.cdn.digitaloceanspaces.com
```

Sin Spaces configurado, los archivos usan el disco `public` local.

## MySQL

### Local (XAMPP)

Base: `bd_modelarc` · usuario: `root` · password vacío · puerto `3306`.

```bash
# crear BD (una vez)
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS bd_modelarc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

cd apps/api
php artisan migrate:fresh --seed
```

### Docker / droplet

```bash
# opcional: copiar variables del host
copy .env.docker.example .env

docker compose up -d mysql redis
```

En el servidor, `apps/api/.env` debe apuntar al servicio Compose:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=bd_modelarc
DB_USERNAME=modelarc
DB_PASSWORD=cambia_esta_password
QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_HOST=redis
```

Si corres la API en el host (no en Docker) y solo MySQL va en contenedor:

```env
DB_HOST=127.0.0.1
DB_USERNAME=modelarc
DB_PASSWORD=cambia_esta_password
```

> En Windows, no levantes el contenedor MySQL si XAMPP ya usa el puerto 3306.

```bash
php artisan migrate:fresh --seed
```

## Identidad visual

- Carbón: `#1A1A1A` / `#111111`
- Acento oro/bronce: `#C4A47C`
- Tipografía web: Cormorant Garamond + DM Sans

## MVP incluido

- Web institucional (Home, servicios, proyectos, contacto, tours)
- Dashboard con KPIs, CRUD de proyectos y leads
- Visor 360° integrado (Photo Sphere Viewer)
- Editor visual de hotspots (yaw/pitch)
- Roles (superadmin / admin / editor)
- Seeders de demo

## Próximas mejoras

- Tiles multirresolución + CDN
- Plano/minimapa interactivo
- Analytics de recorridos
- Integración WhatsApp / agenda
