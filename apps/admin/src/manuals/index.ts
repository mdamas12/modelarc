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
    summary: 'Cómo administrar cuentas del panel: crear, editar y bloquear usuarios.',
    icon: 'group',
    guides: [
      {
        slug: 'crear-usuario',
        title: 'Crear usuario',
        summary: 'Alta de un nuevo administrador o colaborador del panel.',
        steps: [
          {
            title: 'Abrir el módulo Usuarios',
            body: 'En el menú lateral, dentro de Administración, haz clic en Usuarios. Verás el listado de cuentas del panel.',
            image: '/manuals/usuarios/crear-1.png',
          },
          {
            title: 'Iniciar el alta',
            body: 'Haz clic en el botón Nuevo usuario (o Crear). Se abrirá el formulario de registro.',
            image: '/manuals/usuarios/crear-2.png',
          },
          {
            title: 'Completar los datos',
            body: 'Ingresa nombre, email, contraseña y rol. Revisa que el email sea válido; será el acceso al panel.',
            image: '/manuals/usuarios/crear-3.png',
          },
          {
            title: 'Guardar',
            body: 'Confirma con Guardar. El usuario aparecerá en la tabla y podrá iniciar sesión con su email y contraseña.',
            image: '/manuals/usuarios/crear-4.png',
          },
        ],
      },
      {
        slug: 'editar-usuario',
        title: 'Editar usuario',
        summary: 'Actualizar nombre, email, rol u otros datos de una cuenta existente.',
        steps: [
          {
            title: 'Abrir Usuarios',
            body: 'Ve a Administración → Usuarios y localiza la cuenta que quieres modificar.',
            image: '/manuals/usuarios/editar-1.png',
          },
          {
            title: 'Abrir la edición',
            body: 'En la fila del usuario, haz clic en Editar. Se mostrará el formulario con los datos actuales.',
            image: '/manuals/usuarios/editar-2.png',
          },
          {
            title: 'Modificar y guardar',
            body: 'Cambia los campos necesarios (nombre, rol, etc.) y confirma con Guardar. Los cambios se aplican de inmediato.',
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
            body: 'Haz clic en Bloquear (o cambia el estado a inactivo). Confirma la acción en el diálogo.',
            image: '/manuals/usuarios/bloquear-2.png',
          },
          {
            title: 'Verificar el estado',
            body: 'La cuenta quedará marcada como bloqueada o inactiva y no podrá iniciar sesión hasta que la reactives.',
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
