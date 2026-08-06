/**
 * Screenshots for Manuales → Proyectos + Recorridos 360°.
 * Run: node scripts/capture-manuals-proyectos-recorridos.mjs
 */
import { chromium } from 'playwright'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '../apps/admin/public/manuals')

const css = `
  @import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0');
  *{box-sizing:border-box}body{margin:0;font-family:system-ui,-apple-system,'Segoe UI',sans-serif;background:#F5F5F5;color:#2c2c2c}
  .layout{display:flex;min-height:100vh}.drawer{width:260px;background:#1A1A1A;color:#fff;flex-shrink:0;padding:18px 0}
  .brand{padding:8px 20px 22px;font-size:15px;letter-spacing:.12em;text-transform:uppercase;color:#C4A47C;font-weight:600}
  .sec{padding:10px 20px 6px;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.35)}
  .item{display:flex;align-items:center;gap:12px;padding:10px 20px;color:rgba(255,255,255,.72);font-size:14px}
  .item.active{background:rgba(196,164,124,.18);color:#C4A47C;border-right:3px solid #C4A47C}
  .ms{font-family:'Material Symbols Outlined';font-size:20px}
  .main{flex:1;display:flex;flex-direction:column;min-width:0}
  .header{height:72px;background:#fff;border-bottom:1px solid #e8e8e8;display:flex;align-items:center;justify-content:space-between;padding:0 24px}
  .header-title{font-size:18px;font-weight:600}.avatar{width:40px;height:40px;border-radius:50%;background:#C4A47C;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700}
  .page{padding:24px}.page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;gap:16px}
  .page-title{margin:0;font-size:28px;font-weight:400}.page-sub{margin:4px 0 0;color:#6b6b6b;font-size:14px}
  .card{background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
  .btn{border:0;border-radius:6px;padding:10px 16px;font-size:14px;display:inline-flex;align-items:center;gap:8px}
  .btn-primary{background:#C4A47C;color:#1a1a1a;font-weight:600}.btn-outline{background:#fff;border:1px solid #ddd;color:#333}
  .btn-danger{background:#fff;border:1px solid #e57373;color:#c62828}.btn-ok{background:#fff;border:1px solid #66bb6a;color:#2e7d32}
  .btn-sm{padding:6px 10px;font-size:13px}
  .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
  .proj{border:1px solid #e8e8e8;border-radius:10px;overflow:hidden;background:#fff}
  .proj-img{height:140px;background:linear-gradient(135deg,#d7c4a8,#b08f66);display:flex;align-items:flex-end;padding:10px}
  .badge{display:inline-block;border-radius:4px;padding:2px 8px;font-size:11px;color:#fff;background:#43a047}
  .badge-draft{background:#fb8c00}.badge-arch{background:#9e9e9e}
  .proj-body{padding:12px}.proj-title{margin:0 0 6px;font-size:16px;font-weight:600}.proj-meta{margin:0;color:#6b6b6b;font-size:13px}
  .proj-actions{display:flex;gap:8px;padding:0 12px 12px;flex-wrap:wrap}
  .overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;z-index:20}
  .dialog{width:480px;background:#fff;border-radius:10px;padding:22px 24px;box-shadow:0 20px 50px rgba(0,0,0,.25)}
  .dialog h2{margin:0 0 8px;font-size:20px}.hint{margin:0 0 14px;color:#6b6b6b;font-size:14px;line-height:1.45}
  .field{margin-bottom:12px}.field label{display:block;font-size:12px;color:#6b6b6b;margin-bottom:6px}
  .field input,.field select,.field textarea{width:100%;border:1px solid #ccc;border-radius:6px;padding:10px 12px;font-size:14px}
  .dialog-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:8px}
  .toast{position:fixed;right:24px;top:88px;background:#1a1a1a;color:#fff;padding:12px 16px;border-radius:8px;font-size:14px;z-index:30;border-left:4px solid #C4A47C}
  .form-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:16px}
  .thumbs{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
  .thumb{aspect-ratio:1;border-radius:8px;border:2px solid #e8e8e8;background:#ddd;position:relative}
  .thumb.cover{border-color:#C4A47C}.thumb span{position:absolute;left:6px;bottom:6px;background:#C4A47C;color:#111;font-size:10px;padding:2px 6px;border-radius:4px;font-weight:700}
  .section-title{margin:0 0 10px;font-size:16px;font-weight:600}
  .editor{display:grid;grid-template-columns:220px 1fr 280px;gap:12px;min-height:560px}
  .panel{background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:12px}
  .viewer{background:radial-gradient(circle at 40% 40%,#6b8cae,#2a3a4a 70%);border-radius:10px;min-height:520px;display:flex;align-items:center;justify-content:center;color:#fff;position:relative;overflow:hidden}
  .viewer::after{content:'Visor 360°';position:absolute;top:14px;left:14px;background:rgba(0,0,0,.35);padding:6px 10px;border-radius:6px;font-size:12px}
  .hotspot{position:absolute;width:18px;height:18px;border-radius:50%;background:#C4A47C;border:2px solid #fff;box-shadow:0 0 0 4px rgba(196,164,124,.35)}
  .scene-item{display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;border:1px solid #eee;margin-bottom:8px}
  .scene-item.active{border-color:#C4A47C;background:#fff8ef}
  .star{color:#C4A47C}
`

function shell(active, title, content, overlay = '') {
  const items = [
    ['Principal', [['Dashboard', false]]],
    ['Gestión', [
      ['Proyectos', active === 'proyectos'],
      ['Recorridos 360°', active === 'recorridos'],
      ['Galería de medios', false],
      ['Servicios', false],
      ['Testimonios', false],
      ['Solicitudes', false],
    ]],
    ['Administración', [['Usuarios', false], ['Manuales', false], ['Configuración', false]]],
  ]
  const menu = items
    .map(
      ([sec, list]) =>
        `<div class="sec">${sec}</div>` +
        list
          .map(([label, on]) => `<div class="item${on ? ' active' : ''}"><span class="ms">circle</span>${label}</div>`)
          .join(''),
    )
    .join('')

  return `<!doctype html><html><head><meta charset="utf-8"><style>${css}</style></head><body>
  <div class="layout">
    <aside class="drawer"><div class="brand">Modelarc Admin</div>${menu}</aside>
    <div class="main">
      <header class="header"><div class="header-title">${title}</div><div class="avatar">A</div></header>
      <div class="page">${content}</div>
    </div>
  </div>${overlay}</body></html>`
}

function projectsList() {
  return `
  <div class="page-head">
    <div><h1 class="page-title">Proyectos</h1><p class="page-sub">Gestiona el portafolio publicado en la web</p></div>
    <button class="btn btn-primary"><span class="ms">add</span>Nuevo proyecto</button>
  </div>
  <div class="grid">
    <article class="proj">
      <div class="proj-img"><span class="badge">Publicado</span></div>
      <div class="proj-body"><h3 class="proj-title">Residencia Los Olivos</h3><p class="proj-meta">Residencial · Caracas</p></div>
      <div class="proj-actions">
        <button class="btn btn-outline btn-sm">Editar</button>
        <button class="btn btn-outline btn-sm">Archivar</button>
      </div>
    </article>
    <article class="proj">
      <div class="proj-img" style="background:linear-gradient(135deg,#c9d6e3,#7a8fa3)"><span class="badge badge-draft">Borrador</span></div>
      <div class="proj-body"><h3 class="proj-title">Oficinas Norte</h3><p class="proj-meta">Comercial · Valencia</p></div>
      <div class="proj-actions">
        <button class="btn btn-outline btn-sm">Editar</button>
        <button class="btn btn-ok btn-sm">Publicar</button>
      </div>
    </article>
    <article class="proj">
      <div class="proj-img" style="background:linear-gradient(135deg,#e8d5c4,#9a7b5f)"><span class="badge badge-arch">Archivado</span></div>
      <div class="proj-body"><h3 class="proj-title">Remodelación Centro</h3><p class="proj-meta">Remodelación · Maracaibo</p></div>
      <div class="proj-actions">
        <button class="btn btn-outline btn-sm">Editar</button>
        <button class="btn btn-ok btn-sm">Publicar</button>
      </div>
    </article>
  </div>`
}

function projectForm({ gallery = false, ba = false } = {}) {
  return `
  <div class="page-head">
    <div>
      <button class="btn btn-outline btn-sm" style="margin-bottom:8px">← Volver</button>
      <h1 class="page-title">Editar proyecto</h1>
      <p class="page-sub">Residencia Los Olivos</p>
    </div>
    <button class="btn btn-primary">Guardar cambios</button>
  </div>
  <div class="card" style="margin-bottom:14px">
    <h3 class="section-title">Fotografías del proyecto</h3>
    ${
      gallery
        ? `<div class="thumbs">
            <div class="thumb cover"><span>Principal</span></div>
            <div class="thumb"></div><div class="thumb"></div><div class="thumb" style="border-style:dashed;display:flex;align-items:center;justify-content:center;color:#6b6b6b;font-size:12px">+ Subir</div>
          </div>
          <div style="margin-top:12px;display:flex;gap:8px">
            <button class="btn btn-outline btn-sm">Subir fotos</button>
            <button class="btn btn-primary btn-sm">Guardar galería</button>
          </div>`
        : `<p class="hint" style="margin:0">Sube fotos después de crear el proyecto. Usa Subir fotos o arrastra imágenes aquí.</p>
           <button class="btn btn-outline btn-sm" style="margin-top:10px">Subir fotos</button>`
    }
  </div>
  <div class="card" style="margin-bottom:14px">
    <h3 class="section-title">Información del proyecto</h3>
    <div class="form-grid">
      <div>
        <div class="field"><label>Título *</label><input value="Residencia Los Olivos" /></div>
        <div class="field"><label>Categoría *</label><select><option>Residencial</option></select></div>
        <div class="field"><label>Ubicación</label><input value="Caracas" /></div>
      </div>
      <div>
        <div class="field"><label>Proyecto destacado</label><input type="checkbox" checked /> Sí</div>
        <div class="field"><label>Tiene tour virtual</label><input type="checkbox" /> No</div>
      </div>
    </div>
  </div>
  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
      <h3 class="section-title" style="margin:0">Antes y después</h3>
      <button class="btn btn-primary btn-sm">Nueva comparación</button>
    </div>
    ${
      ba
        ? `<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="thumb" style="aspect-ratio:16/10;background:#cfcfcf"><span>Antes</span></div>
            <div class="thumb" style="aspect-ratio:16/10;background:#b8c9a8"><span>Después</span></div>
          </div>
          <div style="margin-top:10px"><span class="badge">Destacada en la web</span></div>`
        : `<p class="hint" style="margin:0">Aún no hay comparaciones. Crea una con Nueva comparación.</p>`
    }
  </div>`
}

function toursList() {
  return `
  <div class="page-head">
    <div><h1 class="page-title">Recorridos 360°</h1><p class="page-sub">Tours virtuales vinculados a proyectos</p></div>
    <button class="btn btn-primary"><span class="ms">add</span>Nuevo tour</button>
  </div>
  <div class="grid">
    <article class="proj">
      <div class="proj-img" style="background:linear-gradient(135deg,#5b7c99,#2c3e50)"><span class="badge">Publicado</span></div>
      <div class="proj-body"><h3 class="proj-title">Tour Residencia Los Olivos</h3><p class="proj-meta">Proyecto: Residencia Los Olivos · 4 escenas</p></div>
      <div class="proj-actions"><button class="btn btn-outline btn-sm">Ver detalle</button><button class="btn btn-danger btn-sm">Eliminar</button></div>
    </article>
    <article class="proj">
      <div class="proj-img" style="background:linear-gradient(135deg,#7a8fa3,#3d4f5f)"><span class="badge badge-draft">Borrador</span></div>
      <div class="proj-body"><h3 class="proj-title">Tour Oficinas Norte</h3><p class="proj-meta">Proyecto: Oficinas Norte · 1 escena</p></div>
      <div class="proj-actions"><button class="btn btn-outline btn-sm">Ver detalle</button><button class="btn btn-danger btn-sm">Eliminar</button></div>
    </article>
  </div>`
}

function tourEditor({ scenes = 0, hotspot = false, dialog = null } = {}) {
  const sceneList =
    scenes === 0
      ? `<p class="hint">Sin escenas. Agrega una para comenzar.</p>`
      : Array.from({ length: scenes })
          .map(
            (_, i) => `<div class="scene-item${i === 0 ? ' active' : ''}">
              <span class="star">${i === 0 ? '★' : '☆'}</span>
              <span>Escena ${i + 1}${i === 0 ? ' · Inicial' : ''}</span>
            </div>`,
          )
          .join('')

  return `
  <div class="page-head">
    <div>
      <button class="btn btn-outline btn-sm" style="margin-bottom:8px">← Volver</button>
      <h1 class="page-title">Editor de recorrido</h1>
      <p class="page-sub">Tour Residencia Los Olivos</p>
    </div>
    <div style="display:flex;gap:8px">
      <button class="btn btn-outline">Escena</button>
      <button class="btn btn-primary">Agregar punto interactivo</button>
    </div>
  </div>
  <div class="editor">
    <aside class="panel"><h3 class="section-title">Escenas</h3>${sceneList}</aside>
    <div class="viewer">${hotspot ? '<div class="hotspot" style="left:58%;top:42%"></div>' : '<div style="opacity:.7;font-size:14px">Orienta el panorama y agrega puntos</div>'}</div>
    <aside class="panel">
      <h3 class="section-title">${hotspot ? 'Editar punto interactivo' : 'Configurar punto interactivo'}</h3>
      ${
        hotspot
          ? `<div class="field"><label>Tipo</label><select><option selected>Escena</option><option>Info</option><option>Media</option><option>Enlace</option></select></div>
             <div class="field"><label>Título</label><input value="Ir a cocina" /></div>
             <div class="field"><label>Escena destino</label><select><option>Escena 2</option></select></div>
             <button class="btn btn-primary" style="width:100%;justify-content:center">Guardar punto interactivo</button>`
          : `<p class="hint">Selecciona una escena y pulsa Agregar punto interactivo.</p>`
      }
    </aside>
  </div>`
}

const scenes = {
  'proyectos/crear-1.png': shell('proyectos', 'Proyectos', projectsList()),
  'proyectos/crear-2.png': shell(
    'proyectos',
    'Nuevo proyecto',
    `<div class="page-head"><div><h1 class="page-title">Nuevo proyecto</h1><p class="page-sub">Las fotos se agregan después de guardar</p></div></div>
     <div class="card">
       <div class="field"><label>Título *</label><input value="Residencia Los Olivos" /></div>
       <div class="field"><label>Categoría *</label><select><option selected>Residencial</option><option>Comercial</option></select></div>
       <div class="field"><label>Ubicación</label><input value="Caracas" /></div>
       <div class="dialog-actions"><button class="btn btn-outline">Cancelar</button><button class="btn btn-primary">Crear proyecto</button></div>
     </div>`,
  ),
  'proyectos/crear-3.png': shell('proyectos', 'Editar proyecto', projectForm(), `<div class="toast">Proyecto creado. Ahora puedes subir fotos.</div>`),
  'proyectos/galeria-1.png': shell('proyectos', 'Editar proyecto', projectForm()),
  'proyectos/galeria-2.png': shell('proyectos', 'Editar proyecto', projectForm({ gallery: true })),
  'proyectos/galeria-3.png': shell(
    'proyectos',
    'Editar proyecto',
    projectForm({ gallery: true }),
    `<div class="toast">Galería guardada.</div>`,
  ),
  'proyectos/publicar-1.png': shell('proyectos', 'Proyectos', projectsList()),
  'proyectos/publicar-2.png': shell(
    'proyectos',
    'Proyectos',
    projectsList(),
    `<div class="overlay"><div class="dialog"><h2>Publicar proyecto</h2><p class="hint">¿Publicar <strong>Oficinas Norte</strong> en el sitio web?</p><div class="dialog-actions"><button class="btn btn-outline">Cancelar</button><button class="btn btn-primary">Publicar</button></div></div></div>`,
  ),
  'proyectos/publicar-3.png': shell(
    'proyectos',
    'Proyectos',
    projectsList(),
    `<div class="overlay"><div class="dialog"><h2>Archivar proyecto</h2><p class="hint">¿Archivar <strong>Residencia Los Olivos</strong>? Dejará de mostrarse en la web.</p><div class="dialog-actions"><button class="btn btn-outline">Cancelar</button><button class="btn btn-danger">Archivar</button></div></div></div>`,
  ),
  'proyectos/ba-1.png': shell('proyectos', 'Editar proyecto', projectForm({ gallery: true })),
  'proyectos/ba-2.png': shell(
    'proyectos',
    'Editar proyecto',
    projectForm({ gallery: true }),
    `<div class="overlay"><div class="dialog" style="width:520px"><h2>Nueva comparación</h2>
      <div class="field"><label>Antes *</label><div class="thumb" style="aspect-ratio:16/9;background:#cfcfcf"></div></div>
      <div class="field"><label>Después *</label><div class="thumb" style="aspect-ratio:16/9;background:#b8c9a8"></div></div>
      <div class="field"><label><input type="checkbox" checked /> Destacada en la web</label></div>
      <div class="dialog-actions"><button class="btn btn-outline">Cancelar</button><button class="btn btn-primary">Guardar</button></div>
    </div></div>`,
  ),
  'proyectos/ba-3.png': shell('proyectos', 'Editar proyecto', projectForm({ gallery: true, ba: true }), `<div class="toast">Comparación guardada.</div>`),

  'recorridos/crear-1.png': shell('recorridos', 'Recorridos 360°', toursList()),
  'recorridos/crear-2.png': shell(
    'recorridos',
    'Recorridos 360°',
    toursList(),
    `<div class="overlay"><div class="dialog"><h2>Nuevo recorrido</h2>
      <div class="field"><label>Nombre *</label><input value="Tour Residencia Los Olivos" /></div>
      <div class="field"><label>Proyecto *</label><select><option selected>Residencia Los Olivos</option><option>Oficinas Norte</option></select></div>
      <div class="field"><label>Descripción</label><textarea rows="3">Recorrido virtual de la vivienda.</textarea></div>
      <div class="dialog-actions"><button class="btn btn-outline">Cancelar</button><button class="btn btn-primary">Crear</button></div>
    </div></div>`,
  ),
  'recorridos/crear-3.png': shell('recorridos', 'Editor de recorrido', tourEditor({ scenes: 0 })),
  'recorridos/escena-1.png': shell(
    'recorridos',
    'Editor de recorrido',
    tourEditor({ scenes: 0 }),
    `<div class="overlay"><div class="dialog"><h2>Nueva escena</h2>
      <div class="field"><label>Nombre *</label><input value="Sala principal" /></div>
      <div class="field"><label>Panorama</label><button class="btn btn-outline" style="width:100%;justify-content:center">Subir panorama</button></div>
      <div class="dialog-actions"><button class="btn btn-outline">Cancelar</button><button class="btn btn-primary">Crear</button></div>
    </div></div>`,
  ),
  'recorridos/escena-2.png': shell(
    'recorridos',
    'Editor de recorrido',
    tourEditor({ scenes: 0 }),
    `<div class="overlay"><div class="dialog"><h2>Nueva escena</h2>
      <div class="field"><label>Nombre *</label><input value="Sala principal" /></div>
      <div class="field"><label>Panorama</label><div class="thumb" style="aspect-ratio:21/9;background:linear-gradient(90deg,#5b7c99,#2c3e50)"><span>panorama.jpg</span></div>
      <button class="btn btn-outline btn-sm" style="margin-top:8px">Cambiar imagen</button></div>
      <div class="dialog-actions"><button class="btn btn-outline">Cancelar</button><button class="btn btn-primary">Crear</button></div>
    </div></div>`,
  ),
  'recorridos/escena-3.png': shell('recorridos', 'Editor de recorrido', tourEditor({ scenes: 2 })),
  'recorridos/inicial-1.png': shell('recorridos', 'Editor de recorrido', tourEditor({ scenes: 3 })),
  'recorridos/inicial-2.png': shell(
    'recorridos',
    'Editor de recorrido',
    tourEditor({ scenes: 3 }),
    `<div class="toast">Escena 1 marcada como Escena inicial.</div>`,
  ),
  'recorridos/hotspot-1.png': shell('recorridos', 'Editor de recorrido', tourEditor({ scenes: 2 })),
  'recorridos/hotspot-2.png': shell('recorridos', 'Editor de recorrido', tourEditor({ scenes: 2, hotspot: true })),
  'recorridos/hotspot-3.png': shell(
    'recorridos',
    'Editor de recorrido',
    tourEditor({ scenes: 2, hotspot: true }),
    `<div class="toast">Punto interactivo guardado.</div>`,
  ),
}

const browser = await chromium.launch()
const page = await browser.newPage({ viewport: { width: 1280, height: 820 } })

for (const [rel, html] of Object.entries(scenes)) {
  const out = path.join(root, rel)
  fs.mkdirSync(path.dirname(out), { recursive: true })
  await page.setContent(html, { waitUntil: 'networkidle' })
  await page.waitForTimeout(120)
  await page.screenshot({ path: out, type: 'png' })
  console.log('wrote', rel)
}

await browser.close()
console.log('Done')
