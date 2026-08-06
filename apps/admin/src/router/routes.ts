import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'login',
    component: () => import('@/pages/LoginPage.vue'),
    meta: { requiresAuth: false, title: 'Login' },
  },
  {
    path: '/',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('@/pages/DashboardPage.vue'),
        meta: { title: 'Dashboard' },
      },
      {
        path: 'proyectos',
        name: 'projects',
        component: () => import('@/pages/ProjectsPage.vue'),
        meta: { title: 'Proyectos' },
      },
      {
        path: 'proyectos/nuevo',
        name: 'project-create',
        component: () => import('@/pages/ProjectFormPage.vue'),
        meta: { title: 'Nuevo proyecto' },
      },
      {
        path: 'proyectos/:id',
        name: 'project-edit',
        component: () => import('@/pages/ProjectFormPage.vue'),
        meta: { title: 'Editar proyecto' },
      },
      {
        path: 'proyectos/:id/medios',
        redirect: (to) => `/proyectos/${to.params.id}`,
      },
      {
        path: 'recorridos',
        name: 'tours',
        component: () => import('@/pages/ToursPage.vue'),
        meta: { title: 'Recorridos 360°' },
      },
      {
        path: 'recorridos/:id/editor',
        name: 'tour-editor',
        component: () => import('@/pages/TourEditorPage.vue'),
        meta: { title: 'Editor de recorrido' },
      },
      {
        path: 'medios',
        name: 'media',
        component: () => import('@/pages/MediaPage.vue'),
        meta: { title: 'Galería de medios' },
      },
      {
        path: 'servicios',
        name: 'services',
        component: () => import('@/pages/ServicesPage.vue'),
        meta: { title: 'Servicios' },
      },
      {
        path: 'testimonios',
        name: 'testimonials',
        component: () => import('@/pages/TestimonialsPage.vue'),
        meta: { title: 'Testimonios' },
      },
      {
        path: 'solicitudes',
        name: 'leads',
        component: () => import('@/pages/LeadsPage.vue'),
        meta: { title: 'Solicitudes' },
      },
      {
        path: 'usuarios',
        name: 'users',
        component: () => import('@/pages/UsersPage.vue'),
        meta: { title: 'Usuarios' },
      },
      {
        path: 'manuales',
        name: 'manuals',
        component: () => import('@/pages/manuals/ManualsIndexPage.vue'),
        meta: { title: 'Manuales' },
      },
      {
        path: 'manuales/:category',
        name: 'manual-category',
        component: () => import('@/pages/manuals/ManualCategoryPage.vue'),
        meta: { title: 'Manuales' },
      },
      {
        path: 'manuales/:category/:slug',
        name: 'manual-guide',
        component: () => import('@/pages/manuals/ManualGuidePage.vue'),
        meta: { title: 'Manuales' },
      },
      {
        path: 'configuracion',
        name: 'settings',
        component: () => import('@/pages/SettingsPage.vue'),
        meta: { title: 'Configuración' },
      },
    ],
  },
  {
    path: '/:catchAll(.*)*',
    component: () => import('@/pages/ErrorNotFound.vue'),
  },
];

export default routes;
