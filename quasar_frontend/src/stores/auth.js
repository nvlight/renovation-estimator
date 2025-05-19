import { defineStore, acceptHMRUpdate } from 'pinia';
//import axios from 'axios';
import { api } from '@/boot/axios.js'; // алиас @ сработал после правки jsconfig.json !

export const useAuthStore = defineStore('auth', {
  // Состояние: пользователь и токен
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('auth_token') || null, // Сохраняем токен в localStorage
  }),

  // Геттеры
  getters: {
    isAuthenticated: (state) => !!state.token, // Проверяем, аутентифицирован ли пользователь
    currentUser: (state) => state.user, // Текущий пользователь
  },

  // Действия
  actions: {
    // Регистрация
    async register(credentials) {
      try {
        const response = await api.post('/register', credentials);
        const { user, token } = response.data;

        this.user = user;
        this.token = token;
        localStorage.setItem('auth_token', token); // Сохраняем токен
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`; // Устанавливаем заголовок для будущих запросов

        return response.data; // Возвращаем данные для обработки в компоненте
      } catch (error) {
        throw error.response?.data || { message: 'Registration failed' };
      }
    },

    // Логин
    async login(credentials) {
      try {
        //const response = await axios.post('/api/login', credentials);
        const response = await api.post('/login', credentials);

        const { user, token } = response.data;

        this.user = user;
        this.token = token;
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        return response.data;
      } catch (error) {
        throw error.response?.data || { message: 'Login failed' };
      }
    },

    // Логаут
    async logout() {
      try {
        await api.post('/logout');
        this.user = null;
        this.token = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        delete api.defaults.headers.common['Authorization'];

        return { message: 'Logged out' };
      } catch (error) {
        throw error.response?.data || { message: 'Logout failed' };
      }
    },

    // Инициализация при загрузке (проверка сохранённого токена)
    initializeAuth() {
      if (this.token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      }
    },
  },
});

// Поддержка HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useAuthStore, import.meta.hot));
}
