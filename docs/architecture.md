# Arquitectura Modelarc

## Flujos

### Publicación de proyecto
1. Admin crea proyecto (draft)
2. Sube medios → job ProcessProjectImage
3. Publica → visible en `/proyectos/{slug}`

### Tour 360°
1. Admin crea tour vinculado a proyecto
2. Sube panoramas → job ProcessPanorama
3. Crea escenas y hotspots en el editor visual
4. Publica tour → embebido en ficha de proyecto y `/recorridos-360/{slug}`

### Lead
1. Visitante envía formulario contacto/presupuesto
2. Se guarda en `leads` (status: new)
3. Visible en dashboard → Solicitudes

## Dominios de código (API)

```
app/
├── Models/
├── Services/     # lógica de negocio
├── Jobs/         # colas de media
├── Http/
│   ├── Controllers/Api/Website/
│   ├── Controllers/Api/Admin/
│   ├── Requests/
│   └── Resources/
```

Controllers públicos viven en `Website` porque `Public` es palabra reservada en PHP.
