/**
 * Screenshots for Manuales → Usuarios (invite flow).
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
  body { margin:0; font-family: system-ui,-apple-system,'Segoe UI',sans-serif; background:#F5F5F5; color:#2c2c2c; }
  .layout { display:flex; min-height:100vh; }
  .drawer { width:260px; background:#1A1A1A; color:#fff; flex-shrink:0; padding:18px 0 12px; }
  .brand { padding:8px 20px 22px; font-size:15px; letter-spacing:0.12em; text-transform:uppercase; color:#C4A47C; font-weight:600; }
  .sec { padding:10px 20px 6px; font-size:11px; letter-spacing:0.14em; text-transform:uppercase; color:rgba(255,255,255,0.35); }
  .item { display:flex; align-items:center; gap:12px; padding:10px 20px; color:rgba(255,255,255,0.72); font-size:14px; }
  .item.active { background:rgba(196,164,124,0.18); color:#C4A47C; border-right:3px solid #C4A47C; }
  .ms { font-family:'Material Symbols Outlined'; font-size:20px; }
  .main { flex:1; display:flex; flex-direction:column; min-width:0; }
  .header { height:72px; background:#fff; border-bottom:1px solid #e8e8e8; display:flex; align-items:center; justify-content:space-between; padding:0 24px; }
  .header-title { font-size:18px; font-weight:600; }
  .header-user { display:flex; align-items:center; gap:12px; }
  .avatar { width:40px; height:40px; border-radius:50%; background:#C4A47C; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; }
  .page { padding:24px; }
  .page-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px; gap:16px; }
  .page-title { margin:0; font-size:28px; font-weight:400; }
  .page-sub { margin:4px 0 0; color:#6b6b6b; font-size:14px; }
  .filters { display:flex; gap:12px; margin-bottom:14px; }
  .filter { flex:1; max-width:280px; border:1px solid #ccc; border-radius:6px; padding:10px 12px; background:#fff; color:#6b6b6b; font-size:14px; }
  .card { background:#fff; border:1px solid #e8e8e8; border-radius:10px; padding:16px; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
  .btn { border:0; border-radius:6px; padding:10px 16px; font-size:14px; cursor:default; display:inline-flex; align-items:center; gap:8px; }
  .btn-primary { background:#C4A47C; color:#1a1a1a; font-weight:600; }
  .btn-outline { background:#fff; border:1px solid #ddd; color:#333; }
  .btn-danger { background:#fff; border:1px solid #e57373; color:#c62828; }
  .btn-ok { background:#fff; border:1px solid #66bb6a; color:#2e7d32; }
  .btn-sm { padding:6px 10px; font-size:13px; }
  table { width:100%; border-collapse:collapse; font-size:14px; }
  th { text-align:left; color:#6b6b6b; font-weight:600; padding:10px 8px; border-bottom:1px solid #eee; }
  td { padding:12px 8px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
  .chip { display:inline-block; background:#C4A47C; color:#fff; border-radius:999px; padding:2px 10px; font-size:12px; }
  .badge { display:inline-block; border-radius:4px; padding:2px 8px; font-size:12px; color:#fff; }
  .badge-ok { background:#43a047; }
  .badge-pending { background:#fb8c00; }
  .badge-off { background:#9e9e9e; }
  .row-hl { background:#fff8ef; outline:2px solid #C4A47C; outline-offset:-2px; }
  .actions { display:flex; gap:8px; flex-wrap:wrap; }
  .overlay { position:fixed; inset:0; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:20; }
  .dialog { width:460px; background:#fff; border-radius:10px; padding:22px 24px 20px; box-shadow:0 20px 50px rgba(0,0,0,0.25); }
  .dialog h2 { margin:0 0 8px; font-size:20px; font-weight:600; }
  .dialog .hint { margin:0 0 16px; color:#6b6b6b; font-size:14px; line-height:1.45; }
  .field { margin-bottom:14px; }
  .field label { display:block; font-size:12px; color:#6b6b6b; margin-bottom:6px; }
  .field input, .field select { width:100%; border:1px solid #ccc; border-radius:6px; padding:10px 12px; font-size:14px; background:#fff; }
  .dialog-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:8px; }
  .toast { position:fixed; right:24px; top:88px; background:#1a1a1a; color:#fff; padding:12px 16px; border-radius:8px; font-size:14px; z-index:30; border-left:4px solid #C4A47C; max-width:320px; }
  .login-wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(160deg,#111 0%,#1a1a1a 45%,#2a2520 100%); }
  .login-card { width:420px; background:#fff; border-radius:12px; padding:36px; box-shadow:0 20px 60px rgba(0,0,0,0.35); }
  .login-brand { text-align:center; color:#C4A47C; letter-spacing:0.14em; text-transform:uppercase; font-weight:700; margin-bottom:8px; }
  .login-sub { text-align:center; color:#6b6b6b; margin:0 0 20px; }
`

function shell({ title = 'Usuarios', content, overlay = '' }) {
  const menu = `
    <div class="sec">Principal</div>
    <div class="item"><span class="ms">dashboard</span>Dashboard</div>
    <div class="sec">Gestión</div>
    <div class="item"><span class="ms">apartment</span>Proyectos</div>
    <div class="item"><span class="ms">panorama_photosphere</span>Recorridos 360°</div>
    <div class="item"><span class="ms">photo_library</span>Galería de medios</div>
    <div class="item"><span class="ms">handyman</span>Servicios</div>
    <div class="item"><span class="ms">format_quote</span>Testimonios</div>
    <div class="item"><span class="ms">mail_outline</span>Solicitudes</div>
    <div class="sec">Administración</div>
    <div class="item active"><span class="ms">group</span>Usuarios</div>
    <div class="item"><span class="ms">menu_book</span>Manuales</div>
    <div class="item"><span class="ms">settings</span>Configuración</div>`

  return `<!doctype html><html><head><meta charset="utf-8"><style>${css}</style></head><body>
  <div class="layout">
    <aside class="drawer"><div class="brand">Modelarc Admin</div>${menu}</aside>
    <div class="main">
      <header class="header">
        <div class="header-title">${title}</div>
        <div class="header-user">
          <div><div style="font-weight:600;font-size:14px">Admin Modelarc</div><div style="font-size:12px;color:#6b6b6b">Administrador</div></div>
          <div class="avatar">A</div>
        </div>
      </header>
      <div class="page">${content}</div>
    </div>
  </div>${overlay}</body></html>`
}

function badge(status) {
  if (status === 'active') return '<span class="badge badge-ok">active</span>'
  if (status === 'pending') return '<span class="badge badge-pending">pending</span>'
  return '<span class="badge badge-off">blocked</span>'
}

function actionBtns(status) {
  if (status === 'pending') {
    return `<div class="actions">
      <button class="btn btn-outline btn-sm">Editar</button>
      <button class="btn btn-outline btn-sm" style="border-color:#C4A47C;color:#8a6d45">Reenviar</button>
    </div>`
  }
  if (status === 'blocked') {
    return `<div class="actions">
      <button class="btn btn-outline btn-sm">Editar</button>
      <button class="btn btn-ok btn-sm">Activar</button>
    </div>`
  }
  return `<div class="actions">
    <button class="btn btn-outline btn-sm">Editar</button>
    <button class="btn btn-danger btn-sm">Bloquear</button>
  </div>`
}

function usersPage({ rows, highlight = null, showInvite = true } = {}) {
  const body = rows
    .map((r, i) => {
      const hl = highlight === i ? ' row-hl' : ''
      return `<tr class="${hl}">
        <td>${r.name}</td><td>${r.email}</td>
        <td><span class="chip">${r.role}</span></td>
        <td>${badge(r.status)}</td>
        <td>${r.last}</td>
        <td>${actionBtns(r.status)}</td>
      </tr>`
    })
    .join('')

  return `
    <div class="page-head">
      <div>
        <h1 class="page-title">Usuarios</h1>
        <p class="page-sub">Invita colaboradores y gestiona el acceso al panel</p>
      </div>
      ${showInvite ? '<button class="btn btn-primary"><span class="ms">person_add</span>Invitar usuario</button>' : ''}
    </div>
    <div class="filters">
      <div class="filter">Buscar por nombre o email</div>
      <div class="filter" style="max-width:180px">Estado</div>
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

function inviteDialog({ filled = false } = {}) {
  const name = filled ? 'María Pérez' : ''
  const email = filled ? 'maria@modelarcve.com' : ''
  return `<div class="overlay"><div class="dialog">
    <h2>Invitar usuario</h2>
    <p class="hint">Se enviará un email de bienvenida para que active su cuenta y cree su contraseña.</p>
    <div class="field"><label>Nombre *</label><input value="${name}" /></div>
    <div class="field"><label>Email *</label><input value="${email}" /></div>
    <div class="field"><label>Rol *</label><select><option>admin</option><option selected>editor</option></select></div>
    <div class="dialog-actions">
      <button class="btn btn-outline">Cancelar</button>
      <button class="btn btn-primary">Enviar invitación</button>
    </div>
  </div></div>`
}

function editDialog({ withReset = false, toast = false } = {}) {
  return `<div class="overlay"><div class="dialog">
    <h2>Editar usuario</h2>
    <div class="field"><label>Nombre *</label><input value="Admin Modelarc" /></div>
    <div class="field"><label>Email *</label><input value="admin@modelarc.com" /></div>
    <div class="field"><label>Rol *</label><select><option selected>admin</option><option>editor</option></select></div>
    ${
      withReset
        ? `<button class="btn btn-outline" style="width:100%;justify-content:center;margin:4px 0 12px;border-color:#C4A47C;color:#8a6d45">
            <span class="ms">lock_reset</span>Restablecer contraseña
          </button>`
        : ''
    }
    <div class="dialog-actions">
      <button class="btn btn-outline">Cancelar</button>
      <button class="btn btn-primary">Guardar</button>
    </div>
  </div></div>${toast ? '<div class="toast">Usuario actualizado.</div>' : ''}`
}

const baseRows = [
  { name: 'Admin Modelarc', email: 'admin@modelarc.com', role: 'admin', status: 'active', last: '2026-08-06 09:20' },
]

const withPending = [
  ...baseRows,
  { name: 'María Pérez', email: 'maria@modelarcve.com', role: 'editor', status: 'pending', last: '—' },
]

const withBlocked = [
  { name: 'Colaborador Demo', email: 'colab@modelarcve.com', role: 'editor', status: 'blocked', last: '2026-08-05 16:10' },
  ...baseRows,
]

const scenes = {
  'crear-1.png': shell({ content: usersPage({ rows: baseRows }) }),
  'crear-2.png': shell({
    content: usersPage({ rows: baseRows }),
    overlay: inviteDialog({ filled: false }),
  }),
  'crear-3.png': shell({
    content: usersPage({ rows: baseRows }),
    overlay: inviteDialog({ filled: true }),
  }),
  'crear-4.png': shell({
    content: usersPage({ rows: withPending, highlight: 1 }),
    overlay: `<div class="toast">Invitación enviada. El usuario debe activar su cuenta desde el email.</div>`,
  }),
  'editar-1.png': shell({ content: usersPage({ rows: withPending, highlight: 0 }) }),
  'editar-2.png': shell({
    content: usersPage({ rows: withPending, highlight: 0 }),
    overlay: editDialog({ withReset: true }),
  }),
  'editar-3.png': shell({
    content: usersPage({ rows: withPending, highlight: 0 }),
    overlay: editDialog({ withReset: true, toast: true }),
  }),
  'reset-1.png': shell({ content: usersPage({ rows: baseRows, highlight: 0 }) }),
  'reset-2.png': shell({
    content: usersPage({ rows: baseRows, highlight: 0 }),
    overlay: `${editDialog({ withReset: true })}<div class="toast">Email de restablecimiento enviado.</div>`,
  }),
  'reset-3.png': `<!doctype html><html><head><meta charset="utf-8"><style>${css}</style></head><body>
    <div class="login-wrap">
      <div class="login-card">
        <div class="login-brand">Modelarc</div>
        <p class="login-sub">Restablecer contraseña</p>
        <div class="field"><label>Nombre</label><input value="Admin Modelarc" readonly /></div>
        <div class="field"><label>Email</label><input value="admin@modelarc.com" readonly /></div>
        <div class="field"><label>Contraseña *</label><input type="password" value="••••••••" /></div>
        <div class="field"><label>Confirmar contraseña *</label><input type="password" value="••••••••" /></div>
        <button class="btn btn-primary" style="width:100%;justify-content:center">Actualizar contraseña</button>
      </div>
    </div>
  </body></html>`,
  'bloquear-1.png': shell({ content: usersPage({ rows: withPending, highlight: 0 }) }),
  'bloquear-2.png': shell({
    content: usersPage({ rows: withPending }),
    overlay: `<div class="overlay"><div class="dialog">
      <h2>Bloquear usuario</h2>
      <p class="hint">¿Bloquear a <strong>Admin Modelarc</strong>?<br/>No podrá iniciar sesión hasta que lo reactives.</p>
      <div class="dialog-actions">
        <button class="btn btn-outline">Cancelar</button>
        <button class="btn btn-danger">Bloquear</button>
      </div>
    </div></div>`,
  }),
  'bloquear-3.png': shell({
    content: usersPage({ rows: withBlocked, highlight: 0 }),
    overlay: `<div class="toast">Usuario bloqueado. Usa Activar para reactivarlo.</div>`,
  }),
}

const browser = await chromium.launch()
const page = await browser.newPage({ viewport: { width: 1280, height: 820 } })

for (const [file, html] of Object.entries(scenes)) {
  await page.setContent(html, { waitUntil: 'networkidle' })
  await page.waitForTimeout(150)
  await page.screenshot({ path: path.join(outDir, file), type: 'png' })
  console.log('wrote', file)
}

await browser.close()
console.log('Done')
