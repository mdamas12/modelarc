#!/usr/bin/env bash
set -euo pipefail
ROOT=$(grep MYSQL_ROOT_PASSWORD /var/www/modelarc/.env | cut -d= -f2-)
docker exec -i modelarc-mysql mysql -uroot -p"$ROOT" bd_modelarc -e "SHOW COLUMNS FROM virtual_tours; SELECT id,name,slug,status,project_id FROM virtual_tours;"
php -r '
require "/var/www/modelarc/apps/api/vendor/autoload.php";
$app=require "/var/www/modelarc/apps/api/bootstrap/app.php";
$kernel=$app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$c=app(\App\Http\Controllers\Api\Website\HomeController::class);
$r=$c->__invoke();
$d=$r->getData(true);
echo "featured_tour=".json_encode($d["data"]["featured_tour"] ?? null)."\n";
echo "services=".count($d["data"]["services"] ?? [])."\n";
'
