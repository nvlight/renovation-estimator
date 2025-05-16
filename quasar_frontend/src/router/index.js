import { defineRouter } from '#q-app/wrappers'
import { createRouter, createMemoryHistory, createWebHistory, createWebHashHistory } from 'vue-router'
import routes from './routes'
import {useAuthStore} from "@/stores/auth.js";

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default defineRouter(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : (process.env.VUE_ROUTER_MODE === 'history' ? createWebHistory : createWebHashHistory)

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.VUE_ROUTER_BASE)
  })

  // Навигационный guard
  Router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    authStore.initializeAuth(); // Устанавливаем токен при каждом переходе
    const isAuthenticated = authStore.isAuthenticated;

    // Если путь '/' и пользователь аутентифицирован, перенаправляем на /dashboard
    if (to.path === '/' && isAuthenticated) {
      next('/dashboard');
    }
    // Если требуется авторизация, но пользователь не вошёл
    else if (to.meta.requiresAuth && !isAuthenticated) {
      next('/login');
    }
    // Если страница только для гостей, но пользователь уже вошёл
    else if (to.meta.requiresGuest && isAuthenticated) {
      next('/dashboard');
    }
    // В остальных случаях продолжаем навигацию
    else {
      next();
    }
  });

  return Router
})
