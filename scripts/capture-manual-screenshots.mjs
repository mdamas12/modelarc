/**
 * Generates Modelarc admin-style screenshots for Manuales → Usuarios.
 * Run: node scripts/capture-manual-screenshots.mjs
 */
import { chromium } from 'playwright'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const outDir = path.resolve(__dirname, '../apps/admin/public/manuals/usuarios')

fs.mkdirSync(outDir, { recursive: true })

const css = `
  @import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0');
  * { box-sizing: border-box; }
  body {
    margin: 0;
    font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
    background: #F5F5F5;
    color: #2c2c2c;
  }
  .layout { display: flex; min-height: 100vh; }
  .drawer {
    width: 260px; background: #1A1A1A; color: #fff; flex-shrink: 0;
    display: flex; flex-direction: column; padding: 18px 0 12px;
  }
  .brand {
    padding: 8px 20px 22px; font-size: 15px; letter-spacing: 0.12em;
    text-transform: uppercase; color: #C4A47C; font-weight: 600;
  }
  .sec { padding: 10px 20px 6px; font-size: 11px; letter-spacing: 0.14em;
    text-transform: uppercase; color: rgba(255,255,255,0.35); }
  .item {
    display: flex; align-items: center; gap: 12px; padding: 10px 20px;
    color: rgba(255,255,255,0.72); font-size: 14px;
  }
  .item.active { background: rgba(196,164,124,0.18); color: #C4A47C; border-right: 3px solid #C4A47C; }
  .ms { font-family: 'Material Symbols Outlined'; font-size: 20px; }
  .main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
  .header {
    height: 72px; background: #fff; border-bottom: 1px solid #e8e8e8;
    display: flex; align-items: center; justify-content: space-between; padding: 0 24px;
  }
  .header-title { font-size: 18px; font-weight: 600; }
  .header-user { display: flex; align-items: center; gap: 12px; }
  .avatar {
    width: 40px; height: 40px; border-radius: 50%; background: #C4A47C; color: #fff;
    display: flex; align-items: center; justify-content: center; font-weight: 700;
  }
  .page { padding: 24px; }
  .page-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; gap: 16px; }
  .page-title { margin: 0; font-size: 28px; font-weight: 400; }
  .page-sub { margin: 4px 0 0; color: #6b6b6b; font-size: 14px; }
  .card {
    background: #fff; border: 1px solid #e8e8e8; border-radius: 10px; padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  }
  .btn {
    border: 0; border-radius: 6px; padding: 10px 16px; font-size: 14px; cursor: default;
    display: inline-flex; align-items: center; gap: 8px;
  }
  .btn-primary { background: #C4A47C; color: #1a1a1a; font-weight: 600; }
  .btn-outline { background: #fff; border: 1px solid #ddd; color: #333; }
  .btn-danger { background: #fff; border: 1px solid #e57373; color: #c62828; }
  .btn-sm { padding: 6px 10px; font-size: 13px; }
  table { width: 100%; border-collapse: collapse; font-size: 14px; }
  th { text-align: left; color: #6b6b6b; font-weight: 600; padding: 10px 8px; border-bottom: 1px solid #eee; }
  td { padding: 12px 8px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
  .chip {
    display: inline-block; background: #C4A47C; color: #fff; border-radius: 999px;
    padding: 2px 10px; font-size: 12px;
  }
  .badge { display: inline-block; border-radius: 4px; padding: 2px 8px; font-size: 12px; color: #fff; }
  .badge-ok { background: #43a047; }
  .badge-off { background: #9e9e9e; }
  .row-hl { background: #fff8ef; outline: 2px solid #C4A47C; outline-offset: -2px; }
  .actions { display: flex; gap: 8px; }
  .overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.45);
    display: flex; align-items: center; justify-content: center; z-index: 20;
  }
  .dialog {
    width: 440px; background: #fff; border-radius: 10px; padding: 22px 24px 20px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
  }
  .dialog h2 { margin: 0 0 16px; font-size: 20px; font-weight: 600; }
  .field { margin-bottom: 14px; }
  .field label { display: block; font-size: 12px; color: #6b6b6b; margin-bottom: 6px; }
  .field input, .field select {
    width: 100%; border: 1px solid #ccc; border-radius: 6px; padding: 10px 12px; font-size: 14px;
    background: #fff;
  }
  .dialog-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
  .toast {
    position: fixed; right: 24px; top: 88px; background: #1a1a1a; color: #fff;
    padding: 12px 16px; border-radius: 8px; font-size: 14px; z-index: 30;
    border-left: 4px solid #C4A47C;
  }
`

function shell({ title = 'Usuarios', active = 'Usuarios', content, overlay = '' }) {
  const items = [
    ['Principal', [['Dashboard', false]]],
    ['Gestión', [['Proyectos', false], ['Recorridos 360°', false], ['Galería de medios', false], ['Servicios', false], ['Testimonios', false], ['Solicitudes', false]]],
    ['Administración', [['Usuarios', active === 'Usuarios'], ['Manuales', false], ['Configuración', false]]],
  ]
  const menu = items.map(([sec, list]) => `
    <div class="sec">${sec}</div>
    ${list.map(([label, on]) => `<div class="item${on ? ' active' : ''}"><span class="ms">circle</span>${label}</div>`).join('')}
  `).join('')

  return `<!doctype html><html><head><meta charset="utf-8"><style>${css}</style></head><body>
  <div class="layout">
    <aside class="drawer">
      <div class="brand">Modelarc Admin</div>
      ${menu}
    </aside>
    <div class="main">
      <header class="header">
        <div class="header-title">${title}</div>
        <div class="header-user">
          <div>
            <div style="font-weight:600;font-size:14px">Admin Modelarc</div>
            <div style="font-size:12px;color:#6b6b6b">Administrador</div>
          </div>
          <div class="avatar">A</div>
        </div>
      </header>
      <div class="page">${content}</div>
    </div>
  </div>
  ${overlay}
  </body></html>`
}

function usersTable({ highlight = null, blocked = false, withNew = false, showCreateBtn = true } = {}) {
  const rows = [
    { name: 'Admin Modelarc', email: 'admin@modelarc.com', role: 'admin', status: 'active', last: '2026-08-06 08:40' },
  ]
  if (withNew) {
    rows.push({ name: 'María Pérez', email: 'maria@modelarcve.com', role: 'editor', status: 'active', last: '—' })
  }
  if (blocked) {
    rows[0] = { ...rows[0], name: 'Colaborador Demo', email: 'colab@modelarcve.com', status: 'blocked', last: '2026-08-05 16:10' }
    rows.push({ name: 'Admin Modelarc', email: 'admin@modelarc.com', role: 'admin', status: 'active', last: '2026-08-06 08:40' })
  }

  const body = rows.map((r, i) => {
    const hl = highlight === i ? ' row-hl' : ''
    const badge = r.status === 'active'
      ? '<span class="badge badge-ok">active</span>'
      : '<span class="badge badge-off">blocked</span>'
    return `<tr class="${hl}">
      <td>${r.name}</td><td>${r.email}</td>
      <td><span class="chip">${r.role || 'admin'}</span></td>
      <td>${badge}</td><td>${r.last}</td>
      <td><div class="actions">
        <button class="btn btn-outline btn-sm">Editar</button>
        <button class="btn btn-danger btn-sm">${r.status === 'blocked' ? 'Activar' : 'Bloquear'}</button>
      </div></td>
    </tr>`
  }).join('')

  return `
    <div class="page-head">
      <div>
        <h1 class="page-title">Usuarios</h1>
        <p class="page-sub">Administradores del panel</p>
      </div>
      ${showCreateBtn ? '<button class="btn btn-primary"><span class="ms">person_add</span>Nuevo usuario</button>' : ''}
    </div>
    <div class="card">
      <table>
        <thead><tr>
          <th>Nombre</th><th>Email</th><th>Roles</th><th>Estado</th><th>Último acceso</th><th>Acciones</th>
        </tr></thead>
        <tbody>${body}</tbody>
      </table>
    </div>`
}

function dialogForm({ title, name, email, password, role, primary }) {
  return `<div class="overlay"><div class="dialog">
    <h2>${title}</h2>
    <div class="field"><label>Nombre</label><input value="${name}" /></div>
    <div class="field"><label>Email</label><input value="${email}" /></div>
    ${password !== null ? `<div class="field"><label>Contraseña</label><input type="password" value="${password}" /></div>` : ''}
    <div class="field"><label>Rol</label><select><option ${role === 'admin' ? 'selected' : ''}>admin</option><option ${role === 'editor' ? 'selected' : ''}>editor</option></select></div>
    <div class="dialog-actions">
      <button class="btn btn-outline">Cancelar</button>
      <button class="btn btn-primary">${primary}</button>
    </div>
  </div></div>`
}

const scenes = {
  'crear-1.png': shell({
    content: usersTable({ showCreateBtn: true }),
  }),
  'crear-2.png': shell({
    content: usersTable(),
    overlay: dialogForm({
      title: 'Nuevo usuario',
      name: '',
      email: '',
      password: '',
      role: 'editor',
      primary: 'Guardar',
    }),
  }),
  'crear-3.png': shell({
    content: usersTable(),
    overlay: dialogForm({
      title: 'Nuevo usuario',
      name: 'María Pérez',
      email: 'maria@modelarcve.com',
      password: '••••••••',
      role: 'editor',
      primary: 'Guardar',
    }),
  }),
  'crear-4.png': shell({
    content: usersTable({ withNew: true, highlight: 1 }),
    overlay: `<div class="toast">Usuario creado correctamente</div>`,
  }),
  'editar-1.png': shell({
    content: usersTable({ highlight: 0 }),
  }),
  'editar-2.png': shell({
    content: usersTable({ highlight: 0 }),
    overlay: dialogForm({
      title: 'Editar usuario',
      name: 'Admin Modelarc',
      email: 'admin@modelarc.com',
      password: null,
      role: 'admin',
      primary: 'Guardar',
    }),
  }),
  'editar-3.png': shell({
    content: usersTable({ highlight: 0 }),
    overlay: dialogForm({
      title: 'Editar usuario',
      name: 'Admin Modelarc',
      email: 'admin@modelarc.com',
      password: null,
      role: 'admin',
      primary: 'Guardar',
    }) + `<div class="toast">Cambios guardados</div>`,
  }),
  'bloquear-1.png': shell({
    content: usersTable({
      withNew: true,
      highlight: 1,
    }).replace(
      'María Pérez',
      'Colaborador Demo',
    ).replace(
      'maria@modelarcve.com',
      'colab@modelarcve.com',
    ),
  }),
  'bloquear-2.png': shell({
    content: usersTable({ withNew: true }),
    overlay: `<div class="overlay"><div class="dialog">
      <h2>Bloquear usuario</h2>
      <p style="margin:0 0 18px;color:#6b6b6b;line-height:1.5">
        ¿Bloquear a <strong>Colaborador Demo</strong>?<br/>
        No podrá iniciar sesión hasta que lo reactives.
      </p>
      <div class="dialog-actions">
        <button class="btn btn-outline">Cancelar</button>
        <button class="btn btn-danger">Bloquear</button>
      </div>
    </div></div>`,
  }),
  'bloquear-3.png': shell({
    content: usersTable({ blocked: true, highlight: 0 }),
    overlay: `<div class="toast">Usuario bloqueado</div>`,
  }),
}

const browser = await chromium.launch()
const page = await browser.newPage({ viewport: { width: 1280, height: 800 } })

for (const [file, html] of Object.entries(scenes)) {
  await page.setContent(html, { waitUntil: 'networkidle' })
  await page.waitForTimeout(200)
  const target = path.join(outDir, file)
  await page.screenshot({ path: target, type: 'png' })
  console.log('wrote', target)
}

await browser.close()
console.log('Done')
