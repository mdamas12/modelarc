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

export const manuals: ManualCategory[] = [
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
            image: '/manuals/usuarios/crear-1.png',
          },
          {
            title: 'Abrir el formulario de invitación',
            body: 'Haz clic en Invitar usuario. No se pide contraseña: el invitado la creará al activar su cuenta.',
            image: '/manuals/usuarios/crear-2.png',
          },
          {
            title: 'Completar nombre, email y rol',
            body: 'Escribe el nombre, el email de acceso y el rol (admin o editor). Ese email recibirá la bienvenida con el enlace de activación.',
            image: '/manuals/usuarios/crear-3.png',
          },
          {
            title: 'Enviar invitación',
            body: 'Confirma con Enviar invitación. El usuario aparece como pending. Cuando active su cuenta desde el email, pasará a active y podrá iniciar sesión.',
            image: '/manuals/usuarios/crear-4.png',
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
            image: '/manuals/usuarios/editar-1.png',
          },
          {
            title: 'Abrir Editar',
            body: 'Haz clic en Editar. Se muestra el formulario con los datos actuales (sin contraseña).',
            image: '/manuals/usuarios/editar-2.png',
          },
          {
            title: 'Guardar cambios',
            body: 'Ajusta nombre, email o rol y pulsa Guardar. Los cambios se aplican de inmediato en el panel.',
            image: '/manuals/usuarios/editar-3.png',
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
            image: '/manuals/usuarios/reset-1.png',
          },
          {
            title: 'Enviar restablecimiento',
            body: 'Dentro del diálogo, pulsa Restablecer contraseña. Se envía un correo con un enlace válido por 7 días y de un solo uso.',
            image: '/manuals/usuarios/reset-2.png',
          },
          {
            title: 'El usuario actualiza su acceso',
            body: 'Al abrir el enlace, el usuario confirma la nueva contraseña y queda redirigido al login de admin.modelarcve.com.',
            image: '/manuals/usuarios/reset-3.png',
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
            image: '/manuals/usuarios/bloquear-1.png',
          },
          {
            title: 'Confirmar el bloqueo',
            body: 'Haz clic en Bloquear y confirma. La cuenta pasa a blocked y pierde el acceso al panel de inmediato.',
            image: '/manuals/usuarios/bloquear-2.png',
          },
          {
            title: 'Reactivar cuando haga falta',
            body: 'En una cuenta bloqueada verás Activar. Al reactivarla vuelve a active y puede iniciar sesión de nuevo. Si está pending, usa Reenviar para volver a mandar el email de activación.',
            image: '/manuals/usuarios/bloquear-3.png',
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
