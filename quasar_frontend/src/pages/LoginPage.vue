<template>
  <q-page class="q-pa-md">
    <h1>Login</h1>
    <q-form @submit="onLogin">
      <q-input v-model="form.email" label="Email" type="email" />
      <q-input v-model="form.password" label="Password" type="password" />
      <p v-if="error">{{ error }}</p>
      <q-btn class="q-mt-sm" type="submit" label="Login" color="primary" />
    </q-form>
  </q-page>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from "@/stores/auth.js";
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const form = ref({
  email: '',
  password: '',
});
const error = ref(null);

const onLogin = async () => {
  try {
    await authStore.login(form.value);
    error.value = null;
    // Перенаправление после логина, например:
    await router.push('/dashboard');
  } catch (err) {
    error.value = err.message;
  }
};

// Инициализация токена при загрузке
authStore.initializeAuth();
</script>
