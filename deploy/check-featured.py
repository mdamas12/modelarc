#!/usr/bin/env python3
import json, urllib.request, subprocess
from pathlib import Path

home = json.load(urllib.request.urlopen("https://api.modelarcve.com/api/public/home"))["data"]
for p in home["featured_projects"]:
    print("project", p.get("title"), "has_virtual_tour=", p.get("has_virtual_tour"), "slug=", p.get("slug"))
print("services", len(home.get("services") or []))

env = {}
for line in Path("/var/www/modelarc/.env").read_text().splitlines():
    if "=" in line and not line.startswith("#"):
        k, v = line.split("=", 1)
        env[k] = v

sql = "SELECT id,title,slug,is_featured,status FROM projects; SELECT id,project_id,name,status FROM virtual_tours;"
out = subprocess.check_output([
    "docker", "exec", "-i", "modelarc-mysql",
    "mysql", "-uroot", f"-p{env['MYSQL_ROOT_PASSWORD']}", "-N", "-e", sql, "bd_modelarc"
], stderr=subprocess.DEVNULL)
print(out.decode())

# try tour endpoint for featured with tour
for p in home["featured_projects"]:
    if p.get("has_virtual_tour"):
        url = f"https://api.modelarcve.com/api/public/projects/{p['slug']}/tour"
        try:
            r = urllib.request.urlopen(url)
            print("tour ok", p["slug"], r.status)
        except Exception as e:
            print("tour fail", p["slug"], e)
