export type ManualStep = {
  title: string
  body: string
  image?: string | null
}

export type ManualGuide = {
  slug: string
  title: string
  summary: string
  steps: ManualStep[]
}

export type ManualCategory = {
  id: string
  title: string
  summary: string
  icon: string
  guides: ManualGuide[]
}

/** Bump when regenerating screenshots so browsers skip stale cached PNGs. */
const IMG_V = 'v=4'

function img(path: string): string {
  return `${path}?${IMG_V}`
}

export const manuals: ManualCategory[] = [
  {
    id: 'proyectos',
    title: 'Proyectos',
    summary: 'Crea proyectos, sube la galería, publica en la web y arma comparaciones antes/después.',
    icon: 'apartment',
    guides: [
      {
        slug: 'crear-proyecto',
        title: 'Crear un proyecto',
        summary: 'Alta del proyecto con título y categoría. Las fotos se agregan después de guardar.',
        steps: [
          {
            title: 'Abrir Proyectos',
            body: 'En Gestión, entra a Proyectos. Verás el listado con búsqueda, filtros y acciones de publicar o archivar.',
            image: img('/manuals/proyectos/crear-1.png'),
          },
          {
            title: 'Nuevo proyecto',
            body: 'Haz clic en Nuevo proyecto. Completa al menos el título y la categoría. Las fotografías se cargan una vez creado.',
            image: img('/manuals/proyectos/crear-2.png'),
          },
          {
            title: 'Crear proyecto',
            body: 'Pulsa Crear proyecto. Quedarás en la edición, donde ya puedes subir la galería y configurar Antes y después.',
            image: img('/manuals/proyectos/crear-3.png'),
          },
        ],
      },
      {
        slug: 'galeria-fotos',
        title: 'Subir y ordenar la galería',
        summary: 'Carga fotos, marca la destacada, controla cuáles van a la web y guarda la galería.',
        steps: [
          {
            title: 'Abrir la edición',
            body: 'Desde el listado, Editar el proyecto. La sección Fotografías del proyecto está arriba del formulario.',
            image: img('/manuals/proyectos/galeria-1.png'),
          },
          {
            title: 'Subir fotos',
            body: 'Usa Subir fotos o arrastra imágenes. Puedes marcar una como destacada, reordenar y activar Web en cada miniatura.',
            image: img('/manuals/proyectos/galeria-2.png'),
          },
          {
            title: 'Guardar galería',
            body: 'Pulsa Guardar galería para persistir orden, portada y visibilidad. Es independiente de Guardar cambios del formulario.',
            image: img('/manuals/proyectos/galeria-3.png'),
          },
        ],
      },
      {
        slug: 'publicar-archivar',
        title: 'Publicar o archivar',
        summary: 'Controla si el proyecto aparece en el sitio público.',
        steps: [
          {
            title: 'Localizar el proyecto',
            body: 'En el listado de Proyectos identifica la tarjeta. El estado puede ser Borrador, Publicado o Archivado.',
            image: img('/manuals/proyectos/publicar-1.png'),
          },
          {
            title: 'Publicar',
            body: 'Si está en borrador, haz clic en Publicar y confirma. El proyecto queda visible en la web.',
            image: img('/manuals/proyectos/publicar-2.png'),
          },
          {
            title: 'Archivar',
            body: 'Si ya está publicado, Archivar lo oculta del sitio sin eliminarlo. Puedes volver a publicarlo después.',
            image: img('/manuals/proyectos/publicar-3.png'),
          },
        ],
      },
      {
        slug: 'antes-despues',
        title: 'Antes y después',
        summary: 'Crea comparaciones visuales para mostrar el cambio del proyecto.',
        steps: [
          {
            title: 'Ir a Antes y después',
            body: 'En la edición del proyecto, baja a la sección Antes y después y pulsa Nueva comparación.',
            image: img('/manuals/proyectos/ba-1.png'),
          },
          {
            title: 'Cargar imágenes',
            body: 'Sube la imagen de Antes y al menos Diseño o Después. Marca Destacada en la web si debe verse en el sitio público.',
            image: img('/manuals/proyectos/ba-2.png'),
          },
          {
            title: 'Guardar la comparación',
            body: 'Confirma con Guardar. Las no destacadas quedan Solo admin y no aparecen en la web.',
            image: img('/manuals/proyectos/ba-3.png'),
          },
        ],
      },
    ],
  },
  {
    id: 'recorridos',
    title: 'Recorridos 360°',
    summary: 'Crea tours virtuales, agrega escenas panorámicas y puntos interactivos en el Visor 360°.',
    icon: 'panorama_photosphere',
    guides: [
      {
        slug: 'crear-recorrido',
        title: 'Crear un recorrido',
        summary: 'Vincula un tour a un proyecto existente y abre el editor.',
        steps: [
          {
            title: 'Abrir Recorridos 360°',
            body: 'En Gestión, entra a Recorridos 360°. Verás las tarjetas de cada tour con su proyecto y cantidad de escenas.',
            image: img('/manuals/recorridos/crear-1.png'),
          },
          {
            title: 'Nuevo tour',
            body: 'Haz clic en Nuevo tour. Completa Nombre y selecciona un Proyecto (no archivado). La descripción es opcional.',
            image: img('/manuals/recorridos/crear-2.png'),
          },
          {
            title: 'Crear y entrar al editor',
            body: 'Pulsa Crear. Se abre el Editor de recorrido. Aún no hay escenas: el siguiente paso es agregar un panorama.',
            image: img('/manuals/recorridos/crear-3.png'),
          },
        ],
      },
      {
        slug: 'agregar-escena',
        title: 'Agregar una escena',
        summary: 'Sube un panorama 360° para que el Visor tenga una escena navegable.',
        steps: [
          {
            title: 'Nueva escena',
            body: 'En el editor, pulsa Escena (o el acceso a Nueva escena). Escribe un nombre identificable.',
            image: img('/manuals/recorridos/escena-1.png'),
          },
          {
            title: 'Subir panorama',
            body: 'Usa Subir panorama para cargar la imagen equirectangular. Luego pulsa Crear.',
            image: img('/manuals/recorridos/escena-2.png'),
          },
          {
            title: 'Seleccionar la escena',
            body: 'La escena aparece en el panel Escenas. Selecciónala para verla en el Visor 360° del centro.',
            image: img('/manuals/recorridos/escena-3.png'),
          },
        ],
      },
      {
        slug: 'escena-inicial',
        title: 'Definir la escena inicial',
        summary: 'Elige con qué panorama arranca el recorrido al abrirse.',
        steps: [
          {
            title: 'Abrir el panel Escenas',
            body: 'Con al menos dos escenas, localiza la estrella junto a cada una en el listado izquierdo.',
            image: img('/manuals/recorridos/inicial-1.png'),
          },
          {
            title: 'Usar como escena inicial',
            body: 'Haz clic en la estrella de la escena deseada. Quedará marcada como Escena inicial del tour.',
            image: img('/manuals/recorridos/inicial-2.png'),
          },
        ],
      },
      {
        slug: 'puntos-interactivos',
        title: 'Agregar puntos interactivos',
        summary: 'Coloca hotspots en el Visor 360° para ir a otra escena, mostrar info, media o un enlace.',
        steps: [
          {
            title: 'Orientar el visor',
            body: 'Selecciona una escena y mueve el Visor 360° hasta el punto donde quieres el hotspot (yaw/pitch actuales se usan al crear).',
            image: img('/manuals/recorridos/hotspot-1.png'),
          },
          {
            title: 'Agregar punto interactivo',
            body: 'Pulsa Agregar punto interactivo. Elige el tipo (Escena, Info, Media o Enlace), título y, si aplica, escena destino o URL.',
            image: img('/manuals/recorridos/hotspot-2.png'),
          },
          {
            title: 'Guardar el punto',
            body: 'Confirma con Guardar punto interactivo. Aparecerá en la lista de la escena y se podrá editar o eliminar después.',
            image: img('/manuals/recorridos/hotspot-3.png'),
          },
        ],
      },
    ],
  },
  {
    id: 'usuarios',
    title: 'Usuarios',
    summary:
      'Invita colaboradores por email, gestiona roles y controla el acceso al panel de administración.',
    icon: 'group',
    guides: [
      {
        slug: 'crear-usuario',
        title: 'Invitar usuario',
        summary:
          'Crea una cuenta pendiente con nombre, email y rol. La persona activa su acceso desde el correo.',
        steps: [
          {
            title: 'Abrir Usuarios',
            body: 'En el menú lateral, sección Administración, entra a Usuarios. Verás el listado con búsqueda y filtro por estado (pendiente, activo o bloqueado).',
            image: img('/manuals/usuarios/crear-1.png'),
          },
          {
            title: 'Abrir el formulario de invitación',
            body: 'Haz clic en Invitar usuario. No se pide contraseña: el invitado la creará al activar su cuenta.',
            image: img('/manuals/usuarios/crear-2.png'),
          },
          {
            title: 'Completar nombre, email y rol',
            body: 'Escribe el nombre, el email de acceso y el rol (admin o editor). Ese email recibirá la bienvenida con el enlace de activación.',
            image: img('/manuals/usuarios/crear-3.png'),
          },
          {
            title: 'Enviar invitación',
            body: 'Confirma con Enviar invitación. El usuario aparece como pending. Cuando active su cuenta desde el email, pasará a active y podrá iniciar sesión.',
            image: img('/manuals/usuarios/crear-4.png'),
          },
        ],
      },
      {
        slug: 'editar-usuario',
        title: 'Editar usuario',
        summary: 'Actualiza nombre, email o rol de una cuenta existente.',
        steps: [
          {
            title: 'Localizar la cuenta',
            body: 'En Usuarios, busca por nombre o email y abre la fila que quieres modificar.',
            image: img('/manuals/usuarios/editar-1.png'),
          },
          {
            title: 'Abrir Editar',
            body: 'Haz clic en Editar. Se muestra el formulario con los datos actuales (sin contraseña).',
            image: img('/manuals/usuarios/editar-2.png'),
          },
          {
            title: 'Guardar cambios',
            body: 'Ajusta nombre, email o rol y pulsa Guardar. Los cambios se aplican de inmediato en el panel.',
            image: img('/manuals/usuarios/editar-3.png'),
          },
        ],
      },
      {
        slug: 'restablecer-contrasena',
        title: 'Restablecer contraseña',
        summary:
          'Envía un email con un enlace de un solo uso para que el usuario defina una nueva contraseña.',
        steps: [
          {
            title: 'Abrir la cuenta activa',
            body: 'Solo cuentas active pueden restablecer contraseña. En Usuarios, haz clic en Editar sobre esa fila.',
            image: img('/manuals/usuarios/reset-1.png'),
          },
          {
            title: 'Enviar restablecimiento',
            body: 'Dentro del diálogo, pulsa Restablecer contraseña. Se envía un correo con un enlace válido por 7 días y de un solo uso.',
            image: img('/manuals/usuarios/reset-2.png'),
          },
          {
            title: 'El usuario actualiza su acceso',
            body: 'Al abrir el enlace, el usuario confirma la nueva contraseña y queda redirigido al login de admin.modelarcve.com.',
            image: img('/manuals/usuarios/reset-3.png'),
          },
        ],
      },
      {
        slug: 'bloquear-usuario',
        title: 'Bloquear o reactivar',
        summary:
          'Suspende el acceso sin borrar la cuenta, o reactívala cuando corresponda.',
        steps: [
          {
            title: 'Elegir la cuenta',
            body: 'En Usuarios identifica la fila. No bloquees tu propia sesión si eres el único administrador activo.',
            image: img('/manuals/usuarios/bloquear-1.png'),
          },
          {
            title: 'Confirmar el bloqueo',
            body: 'Haz clic en Bloquear y confirma. La cuenta pasa a blocked y pierde el acceso al panel de inmediato.',
            image: img('/manuals/usuarios/bloquear-2.png'),
          },
          {
            title: 'Reactivar cuando haga falta',
            body: 'En una cuenta bloqueada verás Activar. Al reactivarla vuelve a active y puede iniciar sesión de nuevo. Si está pending, usa Reenviar para volver a mandar el email de activación.',
            image: img('/manuals/usuarios/bloquear-3.png'),
          },
        ],
      },
    ],
  },
]

export function getManualCategory(id: string): ManualCategory | undefined {
  return manuals.find((c) => c.id === id)
}

export function getManualGuide(categoryId: string, slug: string): ManualGuide | undefined {
  return getManualCategory(categoryId)?.guides.find((g) => g.slug === slug)
}
