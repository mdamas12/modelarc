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
    summary: 'Cómo invitar, editar y bloquear cuentas del panel.',
    icon: 'group',
    guides: [
      {
        slug: 'crear-usuario',
        title: 'Invitar usuario',
        summary: 'Alta por invitación: nombre, email y rol. El usuario activa su cuenta desde el email.',
        steps: [
          {
            title: 'Abrir el módulo Usuarios',
            body: 'En el menú lateral, dentro de Administración, haz clic en Usuarios. Verás el listado de cuentas del panel.',
            image: '/manuals/usuarios/crear-1.png',
          },
          {
            title: 'Iniciar la invitación',
            body: 'Haz clic en Invitar usuario. Se abrirá el formulario sin campo de contraseña.',
            image: '/manuals/usuarios/crear-2.png',
          },
          {
            title: 'Completar los datos',
            body: 'Ingresa nombre, email y rol. El sistema enviará un correo de bienvenida para que la persona active su cuenta.',
            image: '/manuals/usuarios/crear-3.png',
          },
          {
            title: 'Enviar invitación',
            body: 'Confirma con Enviar invitación. El usuario quedará en estado pendiente hasta que active su cuenta desde el email.',
            image: '/manuals/usuarios/crear-4.png',
          },
        ],
      },
      {
        slug: 'editar-usuario',
        title: 'Editar usuario',
        summary: 'Actualizar nombre, email, rol o enviar un restablecimiento de contraseña.',
        steps: [
          {
            title: 'Abrir Usuarios',
            body: 'Ve a Administración → Usuarios y localiza la cuenta que quieres modificar.',
            image: '/manuals/usuarios/editar-1.png',
          },
          {
            title: 'Abrir la edición',
            body: 'En la fila del usuario, haz clic en Editar. Puedes cambiar nombre, email y rol, o usar Restablecer contraseña.',
            image: '/manuals/usuarios/editar-2.png',
          },
          {
            title: 'Modificar y guardar',
            body: 'Guarda los cambios. Si enviaste un restablecimiento, el usuario recibirá un email con un enlace de un solo uso.',
            image: '/manuals/usuarios/editar-3.png',
          },
        ],
      },
      {
        slug: 'bloquear-usuario',
        title: 'Bloquear usuario',
        summary: 'Desactivar el acceso de una cuenta sin eliminarla del sistema.',
        steps: [
          {
            title: 'Localizar la cuenta',
            body: 'En Administración → Usuarios, busca el usuario que deseas bloquear. Revisa que no sea tu propia sesión activa si eres el único admin.',
            image: '/manuals/usuarios/bloquear-1.png',
          },
          {
            title: 'Bloquear acceso',
            body: 'Haz clic en Bloquear y confirma la acción en el diálogo.',
            image: '/manuals/usuarios/bloquear-2.png',
          },
          {
            title: 'Verificar el estado',
            body: 'La cuenta quedará bloqueada y no podrá iniciar sesión hasta que la reactives.',
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
