<template>
  <q-page class="q-pa-md">
    <h3>Projects</h3>
    <div v-if="errorMessage" class="text-negative q-mb-md">
      {{ errorMessage }}
    </div>
    <div v-else-if="projectsStore.projects && projectsStore.projects.length">
      <q-list bordered separator class="rounded-borders shadow-2">
        <q-item
          v-for="project in projectsStore.projects"
          :key="project.id"
          clickable
          v-ripple
          class="q-my-sm"
        >
          <q-item-section avatar>
            <q-icon name="folder" color="primary" size="md"/>
          </q-item-section>
          <q-item-section>
            <q-item-label class="text-bold">{{ project.name }}</q-item-label>
            <q-item-label caption>{{ project.description || 'No description' }}</q-item-label>
            <q-item-label caption>
              Created: {{ formatDate(project.created) }}
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-btn flat icon="edit" color="primary" @click="projectsStore.editProject(project.id)" />
            <q-btn flat icon="delete" color="negative" @click="projectsStore.deleteProject(project.id)" />
          </q-item-section>
        </q-item>
      </q-list>
    </div>
    <div v-else class="text-center q-mt-md">
      <q-spinner color="primary" size="3em"/>
      <div>Loading projects...</div>
    </div>
  </q-page>
</template>

<script setup>
import {useAuthStore} from '@/stores/auth';
import {useProjectsStore} from '@/stores/projects';
import {ref} from "vue";
import {date} from 'quasar'; // Импортируем утилиту для форматирования даты

const authStore = useAuthStore();
const projectsStore = useProjectsStore();
const errorMessage = ref(null);

// Форматирование даты
const formatDate = (timestamp) => {
  return date.formatDate(timestamp, 'DD MMMM YYYY, HH:mm'); // Формат, например, "20 May 2025, 12:11"
};

// Инициализируем токен при загрузке
authStore.initializeAuth();

async function loadData() {
  try {
    await projectsStore.loadProjects();
  } catch (error) {
    errorMessage.value = error.message || 'Failed to load projects';
  }
}

loadData();

</script>

<style scoped>
/* Дополнительные стили для улучшения внешнего вида */
.q-item {
  transition: background-color 0.3s;
}

.q-item:hover {
  background-color: #f5f5f5; /* Лёгкий фон при наведении */
}

.rounded-borders {
  border-radius: 8px;
}
</style>
