import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: () => import('@/layouts/PublicLayout.vue'),
    children: [
      { path: '', name: 'home', component: () => import('@/pages/HomePage.vue') },
      { path: 'nosotros', name: 'about', component: () => import('@/pages/AboutPage.vue') },
      { path: 'servicios', name: 'services', component: () => import('@/pages/ServicesPage.vue') },
      { path: 'proyectos', name: 'projects', component: () => import('@/pages/ProjectsPage.vue') },
      {
        path: 'proyectos/:slug',
        name: 'project-detail',
        component: () => import('@/pages/ProjectDetailPage.vue'),
      },
      {
        path: 'recorridos-360',
        name: 'tours',
        component: () => import('@/pages/VirtualTourPage.vue'),
      },
      {
        path: 'recorridos-360/:slug',
        name: 'tour-detail',
        component: () => import('@/pages/VirtualTourDetailPage.vue'),
      },
      { path: 'blog', name: 'blog', component: () => import('@/pages/BlogPage.vue') },
      { path: 'contacto', name: 'contact', component: () => import('@/pages/ContactPage.vue') },
      {
        path: 'testimonios/:token',
        name: 'testimonial-invite',
        component: () => import('@/pages/TestimonialInvitePage.vue'),
      },
    ],
  },

  {
    path: '/:catchAll(.*)*',
    component: () => import('@/pages/ErrorNotFound.vue'),
  },
];

export default routes;
