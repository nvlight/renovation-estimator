<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated>
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title> Renovation estimator </q-toolbar-title>

        <div>Quasar v{{ $q.version }}</div>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above bordered>
      <q-list>
        <q-item-label header> Навигация </q-item-label>

        <EssentialLink
          v-for="link in linksList"
          :key="link.title"
          :title="link.title"
          :icon="link.icon"
          :to="link.to"
          :active="route.meta.menuSection === link.section"
          @select="closeDrawerOnMobile"
        />

        <template v-if="authStore.isAuthenticated">
          <q-separator spaced />
          <q-item clickable :disable="logoutPending" data-testid="logout" @click="onLogout">
            <q-item-section avatar>
              <q-icon name="logout" />
            </q-item-section>
            <q-item-section>
              <q-item-label>Выйти</q-item-label>
            </q-item-section>
          </q-item>
        </template>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useQuasar } from 'quasar'
import { useRoute, useRouter } from 'vue-router'
import EssentialLink from '@/components/EssentialLink.vue'
import { useAuthStore } from '@/stores/auth'

const authenticatedLinks = [
  {
    title: 'Панель',
    icon: 'dashboard',
    to: { name: 'Dashboard' },
    section: 'dashboard',
  },
  {
    title: 'Проекты',
    icon: 'folder',
    to: { name: 'Projects' },
    section: 'projects',
  },
  {
    title: 'Строительные материалы',
    icon: 'inventory_2',
    to: { name: 'Materials' },
    section: 'materials',
  },
]

const guestLinks = [
  {
    title: 'Войти',
    icon: 'login',
    to: { name: 'Login' },
    section: 'login',
  },
  {
    title: 'Регистрация',
    icon: 'person_add',
    to: { name: 'Register' },
    section: 'register',
  },
]

const $q = useQuasar()
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const leftDrawerOpen = ref(false)
const logoutPending = ref(false)
const linksList = computed(() => (authStore.isAuthenticated ? authenticatedLinks : guestLinks))

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

function closeDrawerOnMobile() {
  if ($q.screen.lt.md) {
    leftDrawerOpen.value = false
  }
}

async function onLogout() {
  logoutPending.value = true

  try {
    await authStore.logout()
    closeDrawerOnMobile()
    await router.push({ name: 'Login' })
  } catch (error) {
    console.error('Logout failed:', error)
  } finally {
    logoutPending.value = false
  }
}
</script>
