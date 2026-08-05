#!/usr/bin/env bash
set -euo pipefail
echo "WEB CSP connect-src:"
grep -o 'connect-src[^"]*' /var/www/modelarc/apps/web/dist/spa/index.html
echo
echo "ADMIN CSP connect-src:"
grep -o 'connect-src[^"]*' /var/www/modelarc/apps/admin/dist/spa/index.html
echo
python3 - <<'PY'
import json, urllib.request, subprocess, os
h = json.load(urllib.request.urlopen('https://api.modelarcve.com/api/public/home'))
d = h['data']
print('projects', len(d.get('featured_projects') or []))
print('services', len(d.get('services') or []))
ft = d.get('featured_tour')
print('featured_tour', bool(ft), (ft or {}).get('title') if ft else None)
env = {}
for line in open('/var/www/modelarc/.env'):
    if '=' in line and not line.strip().startswith('#'):
        k,v=line.strip().split('=',1); env[k]=v
cmd=['docker','exec','-i','modelarc-mysql','mysql','-uroot',f'-p{env["MYSQL_ROOT_PASSWORD"]}','-N','-e',
     'SELECT id, name, slug, is_published, project_id FROM virtual_tours','bd_modelarc']
print(subprocess.check_output(cmd, stderr=subprocess.DEVNULL).decode())
PY
