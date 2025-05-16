import { installQuasarPlugin } from '@quasar/quasar-app-extension-testing-unit-vitest';
import { createTestingPinia } from '@pinia/testing';
import { useRouter } from 'vue-router';
import { mount, flushPromises } from '@vue/test-utils';
import LoginPage from '@/pages/LoginPage.vue';
import { useAuthStore } from '@/stores/auth';
import { QInput, QForm, QBtn } from 'quasar';

installQuasarPlugin();

vi.mock('vue-router', () => ({
  useRouter: vi.fn(() => ({
    push: vi.fn(),
  })),
}));

describe('LoginPage', () => {
  let wrapper;
  let pinia;
  let routerPush;

  beforeEach(() => {
    pinia = createTestingPinia({ createSpy: vi.fn });
    routerPush = vi.fn();
    vi.mocked(useRouter).mockImplementation(() => ({ push: routerPush }));

    wrapper = mount(LoginPage, {
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
    const loginPageWrapper = wrapper.findComponent(LoginPage);
    const inputs = loginPageWrapper.findAllComponents(QInput);
    expect(inputs).toHaveLength(2);
    expect(inputs[0].props().label).toBe('Email');
    expect(inputs[1].props().label).toBe('Password');
    expect(wrapper.findComponent(QBtn).props().label).toBe('Login');
  });

  // Тест на успешную отправку формы
  it('calls login and redirects on successful submission', async () => {
    const authStore = useAuthStore(pinia);
    authStore.login = vi.fn().mockResolvedValue({}); // Мокаем успешный login

    // Находим поля ввода
    const inputs = wrapper.findAllComponents(QInput);

    // Эмулируем ввод данных в поля
    await inputs[0].vm.$emit('update:modelValue', 'john@example.com');
    await inputs[1].vm.$emit('update:modelValue', 'password');

    // Ждем обновления реактивных данных
    await wrapper.vm.$nextTick();

    // Проверяем, что данные формы обновились
    expect(wrapper.vm.form.email).toBe('john@example.com');
    expect(wrapper.vm.form.password).toBe('password');

    // Находим форму и отправляем её
    const form = wrapper.findComponent(QForm);
    await form.trigger('submit');
    //await form.trigger('click');
    //await form.vm.submit();
    await flushPromises(); // эта штука решает, нужно оказывается дождаться асинхронных действий от sumbit

    // вызов напрямую метода сработал! гениально!
    //await wrapper.vm.onLogin(); // прямой вызов метода! это нежелательно, лучше дождаться отправки формы

    // Ждем завершения асинхронных операций (эта штука и подставляла все время!)
    //await wrapper.vm.$nextTick();

    // Проверяем вызов authStore.login с правильными данными
    expect(authStore.login).toHaveBeenCalledWith({
      email: 'john@example.com',
      password: 'password',
    });

    // Проверяем перенаправление на /dashboard
    expect(routerPush).toHaveBeenCalledWith('/dashboard');
  });

  it('displays error message on login failure', async () => {
    const authStore = useAuthStore(pinia);
    authStore.login.mockRejectedValue({ message: 'Login failed' });

    await wrapper.findComponent(QForm).trigger('submit');
    await flushPromises();
    //await wrapper.vm.$nextTick();

    const error = wrapper.find('p');
    expect(error.exists()).toBe(true);
    expect(error.text()).toBe('Login failed');
  });
});
