<template>
  <q-page class="q-pa-md">
    <h1>Dashboard</h1>
    <p>Welcome, {{ currentUser?.name || 'User' }}!</p>
    <q-btn label="Logout" color="negative" @click="onLogout" />
  </q-page>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

// Проверяем текущего пользователя
const currentUser = authStore.currentUser;

// Инициализируем токен при загрузке
authStore.initializeAuth();

// Метод для логаута
const onLogout = async () => {
  try {
    await authStore.logout();
    await router.push('/login');
  } catch (err) {
    console.error('Logout failed:', err.message);
  }
};
</script>
