const routes = [
  {
    path: '/',
    component: () => import('@/layouts/MainLayout.vue'),
    children: [
      {
        path: '',
        name: 'Index',
        component: () => import('@/pages/IndexPage.vue'),
      },
      {
        path: 'login',
        name: 'Login',
        component: () => import('@/pages/LoginPage.vue'),
        meta: { requiresGuest: true }, // Только для неаутентифицированных
      },
      {
        path: 'register',
        name: 'Register',
        component: () => import('@/pages/RegisterPage.vue'),
        meta: { requiresGuest: true }, // Только для неаутентифицированных
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/pages/DashboardPage.vue'),
        meta: { requiresAuth: true }, // Только для аутентифицированных
      },
      {
        path: 'test-css',
        name: 'TestCss',
        component: () => import('@/pages/TestCssPage.vue'),
        meta: { requiresAuth: false }, // Только для аутентифицированных
      },
    ],
  },

  // Обработка 404
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
];

export default routes;
