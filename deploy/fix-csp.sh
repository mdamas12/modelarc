#!/usr/bin/env bash
set -euo pipefail

fix_csp() {
  local file="$1"
  python3 - "$file" <<'PY'
import sys
from pathlib import Path
path = Path(sys.argv[1])
text = path.read_text(encoding='utf-8')
old = text
# Ensure production API is allowed in connect-src / media-src
for needle, insert in [
    ("connect-src 'self'", "connect-src 'self' https://api.modelarcve.com"),
    ("media-src 'self' blob: https:", "media-src 'self' blob: https: https://api.modelarcve.com"),
]:
    if "https://api.modelarcve.com" not in text or needle in text:
        if "https://api.modelarcve.com" not in text.split("connect-src")[1].split(";")[0] if "connect-src" in text else True:
            pass
# More robust: if api host missing from connect-src, inject it
import re
def inject(directive, host):
    global text
    m = re.search(rf"{directive} ([^;]+);", text)
    if not m:
        return
    vals = m.group(1)
    if host not in vals:
        text = text[:m.start(1)] + vals + " " + host + text[m.end(1):]

inject("connect-src", "https://api.modelarcve.com")
inject("media-src", "https://api.modelarcve.com")
if text != old:
    path.write_text(text, encoding='utf-8')
    print(f"patched {path}")
else:
    print(f"unchanged {path}")
PY
}

fix_csp /var/www/modelarc/apps/web/dist/spa/index.html
fix_csp /var/www/modelarc/apps/admin/dist/spa/index.html

# Also update source templates on server for future rebuilds
WEB_SRC=/var/www/modelarc/apps/web/index.html
ADMIN_SRC=/var/www/modelarc/apps/admin/index.html
if [[ -f "$WEB_SRC" ]]; then fix_csp "$WEB_SRC"; fi
if [[ -f "$ADMIN_SRC" ]]; then fix_csp "$ADMIN_SRC"; fi

echo "==> connect-src now:"
grep -o "connect-src[^\"]*" /var/www/modelarc/apps/web/dist/spa/index.html | head -1
grep -o "connect-src[^\"]*" /var/www/modelarc/apps/admin/dist/spa/index.html | head -1
