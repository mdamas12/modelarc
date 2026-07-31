import { defineRouter } from '#q-app';
import {
  createMemoryHistory,
  createRouter,
  createWebHashHistory,
  createWebHistory,
} from 'vue-router';

import routes from './routes';
import { TOKEN_KEY } from '@/boot/axios';

export default defineRouter(function () {
  const createHistory = import.meta.env.QUASAR_SERVER
    ? createMemoryHistory
    : (import.meta.env.QUASAR_VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory);

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,
    history: createHistory(import.meta.env.QUASAR_VUE_ROUTER_BASE),
  });

  Router.beforeEach((to) => {
    const token = localStorage.getItem(TOKEN_KEY);
    const requiresAuth = to.matched.some((r) => r.meta.requiresAuth !== false && r.path !== '/login');

    if (to.path === '/login' || to.name === 'login') {
      if (token) return { path: '/' };
      return true;
    }

    if (requiresAuth && !token) {
      return { path: '/login', query: { redirect: to.fullPath } };
    }

    return true;
  });

  return Router;
});
