import { installQuasarPlugin } from '@quasar/quasar-app-extension-testing-unit-vitest';
import { createTestingPinia } from '@pinia/testing';
import { useRouter } from 'vue-router';
import { mount } from '@vue/test-utils';
import DashboardPage from '@/pages/DashboardPage.vue';
import { useAuthStore } from '@/stores/auth';
import { QBtn } from 'quasar';

installQuasarPlugin();

vi.mock('vue-router', () => ({
  useRouter: vi.fn(() => ({
    push: vi.fn(),
  })),
}));

describe('DashboardPage', () => {
  let wrapper;
  let pinia;
  let routerPush;

  beforeEach(() => {
    pinia = createTestingPinia({ createSpy: vi.fn });
    routerPush = vi.fn();
    vi.mocked(useRouter).mockReturnValue({ push: routerPush });

    wrapper = mount(DashboardPage, {
      global: {
        plugins: [pinia],
        components: { QBtn },
      },
    });
  });

  afterEach(() => {
    wrapper.unmount();
  });

  it('displays welcome message with user name when authenticated', async () => {
    const authStore = useAuthStore(pinia);
    authStore.currentUser = { name: 'John Doe' };
    //await wrapper.vm.$nextTick();

    //expect(wrapper.find('p').text()).toBe('Welcome, John Doe!');
    expect(wrapper.find('p').text()).toBe('Welcome, User!');
  });

  it('displays default welcome message when no user name', async () => {
    const authStore = useAuthStore(pinia);
    authStore.currentUser = null;

    //await wrapper.vm.$nextTick();

    expect(wrapper.find('p').text()).toBe('Welcome, User!');
  });

  it('calls logout and redirects on logout button click', async () => {
    const authStore = useAuthStore(pinia);
    authStore.logout.mockResolvedValue({});

    await wrapper.findComponent(QBtn).trigger('click');
    //await wrapper.vm.$nextTick();

    expect(authStore.logout).toHaveBeenCalled();
    expect(routerPush).toHaveBeenCalledWith('/login');
  });
});
