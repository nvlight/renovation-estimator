import { installQuasarPlugin } from '@quasar/quasar-app-extension-testing-unit-vitest';
import { createTestingPinia } from '@pinia/testing';
import { useRouter } from 'vue-router';
import { mount, flushPromises } from '@vue/test-utils';
import RegisterPage from '@/pages/RegisterPage.vue';
import { useAuthStore } from '@/stores/auth';
import { QInput, QForm, QBtn } from 'quasar';
import { expect, vi, describe, beforeEach, afterEach, it } from 'vitest';

installQuasarPlugin();

vi.mock('vue-router', () => ({
  useRouter: vi.fn(() => ({
    push: vi.fn(),
  })),
}));

describe('RegisterPage', () => {
  let wrapper;
  let pinia;
  let routerPush;

  beforeEach(() => {
    pinia = createTestingPinia({ createSpy: vi.fn });
    routerPush = vi.fn();
    vi.mocked(useRouter).mockReturnValue({ push: routerPush });

    wrapper = mount(RegisterPage, {
      global: {
        plugins: [pinia],
        components: { QInput, QForm, QBtn },
      },
    });
  });

  afterEach(() => {
    wrapper.unmount();
  });

  it('renders the form correctly', () => {
    const inputs = wrapper.findAllComponents(QInput);
    expect(inputs).toHaveLength(4);
    expect(inputs[0].props().label).toBe('Name');
    expect(inputs[1].props().label).toBe('Email');
    expect(inputs[2].props().label).toBe('Password');
    expect(inputs[3].props().label).toBe('Confirm Password');
    expect(wrapper.findComponent(QBtn).props().label).toBe('Register');
  });

  it('calls register and redirects on successful submission', async () => {
    const authStore = useAuthStore(pinia);
    authStore.register.mockResolvedValue({});

    const inputs = wrapper.findAllComponents(QInput);
    await inputs[0].vm.$emit('update:modelValue', 'John Doe');
    await inputs[1].vm.$emit('update:modelValue', 'john@example.com');
    await inputs[2].vm.$emit('update:modelValue', 'password');
    await inputs[3].vm.$emit('update:modelValue', 'password');

    await wrapper.findComponent(QForm).trigger('submit');
    await flushPromises();
    //await wrapper.vm.$nextTick();

    expect(authStore.register).toHaveBeenCalledWith({
      name: 'John Doe',
      email: 'john@example.com',
      password: 'password',
      password_confirmation: 'password',
    });
    expect(routerPush).toHaveBeenCalledWith('/dashboard');
  });

  it('displays error message on registration failure', async () => {
    const authStore = useAuthStore(pinia);
    authStore.register.mockRejectedValue({ message: 'Registration failed' });

    await wrapper.findComponent(QForm).trigger('submit');
    await flushPromises();
    //await wrapper.vm.$nextTick();

    const error = wrapper.find('p');
    expect(error.exists()).toBe(true);
    expect(error.text()).toBe('Registration failed');
  });

  it('clears error on successful registration after failure', async () => {
    const authStore = useAuthStore(pinia);

    // Сначала провоцируем ошибку
    authStore.register.mockRejectedValueOnce({ message: 'Registration failed' });
    await wrapper.findComponent(QForm).trigger('submit');
    await flushPromises();
    //await wrapper.vm.$nextTick();
    expect(wrapper.find('p').text()).toBe('Registration failed');

    // Затем успешная регистрация
    authStore.register.mockResolvedValue({});
    await wrapper.findComponent(QForm).trigger('submit');
    await flushPromises();
    //await wrapper.vm.$nextTick();
    expect(wrapper.find('p').exists()).toBe(false);
    expect(routerPush).toHaveBeenCalledWith('/dashboard');
  });
});
