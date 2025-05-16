<template>
  <q-page class="q-pa-md">
    <h1>Register</h1>
    <q-form @submit="onRegister">
      <q-input v-model="form.name" label="Name" />
      <q-input v-model="form.email" label="Email" type="email" />
      <q-input v-model="form.password" label="Password" type="password" />
      <q-input v-model="form.password_confirmation" label="Confirm Password" type="password" />
      <p v-if="error">{{ error }}</p>
      <q-btn class="q-mt-sm"  type="submit" label="Register" color="primary" />
    </q-form>
  </q-page>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import {useRouter} from "vue-router";

const authStore = useAuthStore();
const router = useRouter();

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});
const error = ref(null);

const onRegister = async () => {
  try {
    await authStore.register(form.value);
    error.value = null;
    await router.push('/dashboard');
  } catch (err) {
    error.value = err.message;
  }
};

authStore.initializeAuth();
</script>
