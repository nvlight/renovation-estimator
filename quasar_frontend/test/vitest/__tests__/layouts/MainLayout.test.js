import { installQuasarPlugin } from '@quasar/quasar-app-extension-testing-unit-vitest'
import { createTestingPinia } from '@pinia/testing'
import { flushPromises, mount } from '@vue/test-utils'
import { createMemoryHistory, createRouter } from 'vue-router'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import EssentialLink from '@/components/EssentialLink.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import routes from '@/router/routes'
import { useAuthStore } from '@/stores/auth'

installQuasarPlugin()

const EmptyPage = { template: '<div />' }
const testRoutes = [
  {
    path: '/',
    component: EmptyPage,
    children: [
      {
        path: 'login',
        name: 'Login',
        component: EmptyPage,
        meta: { menuSection: 'login' },
      },
      {
        path: 'register',
        name: 'Register',
        component: EmptyPage,
        meta: { menuSection: 'register' },
      },
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: EmptyPage,
        meta: { menuSection: 'dashboard' },
      },
      {
        path: 'projects',
        name: 'Projects',
        component: EmptyPage,
        meta: { menuSection: 'projects' },
      },
      {
        path: 'projects/:projectId/rooms',
        name: 'ProjectRooms',
        component: EmptyPage,
        meta: { menuSection: 'projects' },
      },
      {
        path: 'materials',
        name: 'Materials',
        component: EmptyPage,
        meta: { menuSection: 'materials' },
      },
      {
        path: 'material/:material_id',
        name: 'MaterialDetail',
        component: EmptyPage,
        meta: { menuSection: 'materials' },
      },
    ],
  },
]

describe('MainLayout', () => {
  let wrapper

  beforeEach(() => {
    localStorage.clear()
  })

  afterEach(() => {
    wrapper?.unmount()
  })

  async function mountLayout({ path, authenticated }) {
    const router = createRouter({
      history: createMemoryHistory(),
      routes: testRoutes,
    })
    const pinia = createTestingPinia({
      createSpy: vi.fn,
      initialState: {
        auth: {
          token: authenticated ? 'test-token' : null,
          user: authenticated ? { name: 'Test User' } : null,
        },
      },
    })

    await router.push(path)
    await router.isReady()

    wrapper = mount(MainLayout, {
      global: {
        plugins: [pinia, router],
      },
    })

    return { router, authStore: useAuthStore(pinia) }
  }

  function menuLinks() {
    return wrapper.findAllComponents(EssentialLink)
  }

  it('shows guest navigation without template links', async () => {
    await mountLayout({ path: '/login', authenticated: false })

    expect(menuLinks().map((link) => link.props('title'))).toEqual(['Войти', 'Регистрация'])
    expect(menuLinks().map((link) => link.props('to'))).toEqual([
      { name: 'Login' },
      { name: 'Register' },
    ])
    expect(wrapper.text()).toContain('Навигация')
    expect(wrapper.text()).not.toContain('Essential Links')
    expect(wrapper.text()).not.toContain('Quasar Awesome')
  })

  it('shows the project navigation and logout for an authenticated user', async () => {
    await mountLayout({ path: '/dashboard', authenticated: true })

    expect(menuLinks().map((link) => link.props('title'))).toEqual([
      'Панель',
      'Проекты',
      'Строительные материалы',
    ])
    expect(menuLinks().map((link) => link.props('to'))).toEqual([
      { name: 'Dashboard' },
      { name: 'Projects' },
      { name: 'Materials' },
    ])
    expect(wrapper.get('[data-testid="logout"]').text()).toContain('Выйти')
  })

  it.each([
    ['/projects/7/rooms', 'Проекты'],
    ['/material/12', 'Строительные материалы'],
  ])('keeps the parent menu section active on %s', async (path, title) => {
    await mountLayout({ path, authenticated: true })

    const activeLink = menuLinks().find((link) => link.props('active'))

    expect(activeLink.props('title')).toBe(title)
  })

  it.each([
    ['ProjectRooms', 'projects'],
    ['RoomSpecifications', 'projects'],
    ['MaterialDetail', 'materials'],
  ])('assigns the %s route to the %s menu section', (routeName, menuSection) => {
    const route = routes[0].children.find(({ name }) => name === routeName)

    expect(route.meta.menuSection).toBe(menuSection)
  })

  it('logs out and redirects to the login page', async () => {
    const { router, authStore } = await mountLayout({
      path: '/dashboard',
      authenticated: true,
    })
    authStore.logout.mockResolvedValue()

    await wrapper.get('[data-testid="logout"]').trigger('click')
    await flushPromises()

    expect(authStore.logout).toHaveBeenCalledOnce()
    expect(router.currentRoute.value.name).toBe('Login')
  })

  it('stays on the current page when logout fails', async () => {
    const consoleError = vi.spyOn(console, 'error').mockImplementation(() => {})
    const { router, authStore } = await mountLayout({
      path: '/dashboard',
      authenticated: true,
    })
    authStore.logout.mockRejectedValue(new Error('Network error'))

    await wrapper.get('[data-testid="logout"]').trigger('click')
    await flushPromises()

    expect(router.currentRoute.value.name).toBe('Dashboard')
    consoleError.mockRestore()
  })
})
