#!/usr/bin/env bash
set -euo pipefail
cd /var/www/modelarc
git fetch origin
git checkout master
git pull --ff-only origin master

# Keep production CSP after rebuild (source templates already include API)
cd /var/www/modelarc/apps/web
npm ci
VITE_API_URL=https://api.modelarcve.com/api npm run build

# Ensure CSP has API host in built index
python3 - <<'PY'
from pathlib import Path
import re
path = Path('/var/www/modelarc/apps/web/dist/spa/index.html')
text = path.read_text(encoding='utf-8')
if 'https://api.modelarcve.com' not in text:
    text = text.replace("connect-src 'self'", "connect-src 'self' https://api.modelarcve.com")
    path.write_text(text, encoding='utf-8')
    print('CSP patched')
else:
    print('CSP already includes API')
print(re.search(r'connect-src[^"]*', text).group(0)[:200])
PY

systemctl reload nginx
echo "Deploy web OK"
