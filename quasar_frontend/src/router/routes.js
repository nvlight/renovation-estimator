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
        meta: { requiresGuest: true }, // Только для не аутентифицированных
      },
      {
        path: 'register',
        name: 'Register',
        component: () => import('@/pages/RegisterPage.vue'),
        meta: { requiresGuest: true }, // Только для не аутентифицированных
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/pages/DashboardPage.vue'),
        meta: { requiresAuth: true }, // Только для аутентифицированных
      },
      {
        path: 'projects',
        name: 'Projects',
        component: () => import('@/pages/ProjectsPage.vue'),
        meta: { requiresAuth: true }, // Только для аутентифицированных
      },
      {
        path: 'projects/:projectId/rooms',
        name: 'ProjectRooms',
        component: () => import('@/pages/RoomsPage.vue'),
        meta: { requiresAuth: true },
      },{
        path: 'projects/:projectId/rooms/:roomId/specifications',
        name: 'RoomSpecifications',
        component: () => import('@/pages/RoomSpecifications.vue'),
        meta: { requiresAuth: true },
      },
      {
        path: 'test-css',
        name: 'TestCss',
        component: () => import('@/pages/TestCssPage.vue'),
        meta: { requiresAuth: false }, // Только для аутентифицированных
      },
      {
        path: 'test-walls-builder',
        name: 'TestWallsBuilder',
        component: () => import('@/pages/TestWallsBuilder.vue'),
        meta: { requiresAuth: false }, // Только для аутентифицированных
      },

    ],
  },

  // Обработка 404
  {
    path: '/:catchAll(.*)*',
    component: () => import('@/pages/ErrorNotFound.vue'),
  },
];

export default routes;
