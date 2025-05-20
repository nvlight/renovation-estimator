<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-mb-md">
      <h3 class="q-my-none">Projects</h3>
      <q-btn
        color="primary"
        icon="add"
        label="Add Project"
        class="q-ml-auto"
        @click="showAddProjectDialog = true"
      />
    </div>

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
            <q-btn flat icon="visibility" color="grey-7" @click="openViewProjectDialog(project)"/>
            <q-btn flat icon="edit" color="primary" @click="openEditProjectDialog(project)"/>
            <q-btn flat icon="delete" color="negative" @click="confirmDeleteProject(project.id, project.name)"/>
          </q-item-section>
        </q-item>
      </q-list>

      <!-- Пагинация -->
      <div class="q-mt-md flex justify-center">
        <q-pagination
          v-model="currentPage"
          :max="projectsStore.pagination.lastPage"
          :direction-links="true"
          :boundary-links="true"
          color="primary"
          @update:model-value="loadProjects"
        />
      </div>
    </div>
    <div v-else class="text-center q-mt-md">
      <q-spinner color="primary" size="3em"/>
      <div>Loading projects...</div>
    </div>

    <!-- Модальное окно для добавления проекта -->
    <q-dialog v-model="showAddProjectDialog">
      <q-card style="width: 500px; max-width: 90vw;">
        <q-card-section>
          <div class="text-h6">Add New Project</div>
        </q-card-section>
        <q-card-section>
          <q-form @submit="addProject" ref="projectForm">
            <q-input
              v-model="newProject.name"
              label="Project Name *"
              :rules="[val => !!val || 'Project name is required']"
              outlined
              class="q-mb-md"
            />
            <q-input
              v-model="newProject.description"
              label="Description"
              type="textarea"
              outlined
              class="q-mb-md"
            />
            <q-input
              v-model="newProject.place_name"
              label="Place Name"
              outlined
              class="q-mb-md"
            />
            <q-card-actions align="right">
              <q-btn label="Cancel" color="negative" flat @click="showAddProjectDialog = false"/>
              <q-btn label="Save" type="submit" color="primary" :disable="!newProject.name"/>
            </q-card-actions>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Модальное окно для редактирования проекта -->
    <q-dialog v-model="showEditProjectDialog">
      <q-card style="width: 500px; max-width: 90vw;">
        <q-card-section>
          <div class="text-h6">Edit Project</div>
        </q-card-section>
        <q-card-section>
          <q-form @submit="editProject" ref="editProjectForm">
            <q-input
              v-model="editProjectData.name"
              label="Project Name *"
              :rules="[val => !!val || 'Project name is required']"
              outlined
              class="q-mb-md"
            />
            <q-input
              v-model="editProjectData.description"
              label="Description"
              type="textarea"
              outlined
              class="q-mb-md"
            />
            <q-input
              v-model="editProjectData.place_name"
              label="Place Name"
              outlined
              class="q-mb-md"
            />
            <q-card-actions align="right">
              <q-btn label="Cancel" color="negative" flat @click="showEditProjectDialog = false"/>
              <q-btn label="Save" type="submit" color="primary" :disable="!editProjectData.name"/>
            </q-card-actions>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Модальное окно для просмотра проекта -->
    <q-dialog v-model="showViewProjectDialog">
      <q-card style="width: 500px; max-width: 90vw;">
        <q-card-section>
          <div class="text-h6">View Project</div>
        </q-card-section>
        <q-card-section>
          <q-item-label class="q-mb-md">
            <strong>Name:</strong> {{ viewProjectData.name }}
          </q-item-label>
          <q-item-label class="q-mb-md">
            <strong>Description:</strong> {{ viewProjectData.description || 'No description' }}
          </q-item-label>
          <q-item-label class="q-mb-md">
            <strong>Place Name:</strong> {{ viewProjectData.place_name || 'Not specified' }}
          </q-item-label>
          <q-item-label>
            <strong>Created:</strong> {{ formatDate(viewProjectData.created) }}
          </q-item-label>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn label="Close" color="primary" flat @click="showViewProjectDialog = false"/>
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Модальное окно для подтверждения удаления -->
    <q-dialog v-model="showDeleteConfirmDialog">
      <q-card>
        <q-card-section>
          <div class="text-h6">Confirm Deletion</div>
        </q-card-section>
        <q-card-section>
          Are you sure you want to delete the project "{{ deleteProjectName }}"?
        </q-card-section>
        <q-card-actions align="right">
          <q-btn label="Cancel" color="negative" flat @click="showDeleteConfirmDialog = false"/>
          <q-btn label="Delete" color="primary" @click="deleteProject"/>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import {useAuthStore} from '@/stores/auth';
import {useProjectsStore} from '@/stores/projects';
import {ref, watch} from "vue";
import {date} from 'quasar'; // Импортируем утилиту для форматирования даты
import {Notify} from 'quasar';

const authStore = useAuthStore();
const projectsStore = useProjectsStore();
const errorMessage = ref(null);

const showAddProjectDialog = ref(false);
const newProject = ref({
  name: '',
  description: '',
  place_name: '',
});
const projectForm = ref(null);

const showEditProjectDialog = ref(false);
const editProjectData = ref({
  id: null,
  name: '',
  description: '',
  place_name: '',
});
const editProjectForm = ref(null);

const showViewProjectDialog = ref(false);
const viewProjectData = ref({
  id: null,
  name: '',
  description: '',
  place_name: '',
  created: '',
});

const showDeleteConfirmDialog = ref(false);
const deleteProjectId = ref(null);
const deleteProjectName = ref('');

// Инициализация текущей страницы из localStorage
const currentPage = ref(parseInt(localStorage.getItem('projectsPage')) || 1);

// Сохранение текущей страницы в localStorage при изменении
watch(currentPage, (newPage) => {
  localStorage.setItem('projectsPage', newPage);
});


// Форматирование даты
const formatDate = (timestamp) => {
  return date.formatDate(timestamp, 'DD MMMM YYYY, HH:mm'); // Формат, например, "20 May 2025, 12:11"
};

// Инициализируем токен при загрузке
authStore.initializeAuth();

async function loadData() {
  try {
    await projectsStore.loadProjects(currentPage.value);
  } catch (error) {
    errorMessage.value = error.message || 'Failed to load projects';
  }
}

loadData();

// Загрузка проектов при смене страницы
async function loadProjects(page) {
  currentPage.value = page;
  try {
    await projectsStore.loadProjects(page);
  } catch (error) {
    errorMessage.value = error.message || 'Failed to load projects';
    Notify.create({
      type: 'negative',
      message: errorMessage.value,
    });
  }
}

// Открытие диалога редактирования
function openEditProjectDialog(project) {
  editProjectData.value = {
    id: project.id,
    name: project.name,
    description: project.description || '',
    place_name: project.place_name || '',
  };
  showEditProjectDialog.value = true;
}

// Добавление проекта
async function addProject() {
  try {
    const response = await projectsStore.addProject(newProject.value);
    if (response) {
      showAddProjectDialog.value = false; // Закрываем диалог
      newProject.value = {name: '', description: '', place_name: ''}; // Сбрасываем форму
      projectForm.value.resetValidation(); // Сбрасываем валидацию

      await projectsStore.loadProjects(currentPage.value); // Перезагружаем текущую страницу
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to add project';
  }
}

// Открытие диалога просмотра
function openViewProjectDialog(project) {
  viewProjectData.value = {
    id: project.id,
    name: project.name,
    description: project.description || '',
    place_name: project.place_name || '',
    created: project.created,
  };
  showViewProjectDialog.value = true;
}

// Редактирование проекта
async function editProject() {
  try {
    await projectsStore.editProject(editProjectData.value.id, {
      name: editProjectData.value.name,
      description: editProjectData.value.description,
      place_name: editProjectData.value.place_name,
    });
    showEditProjectDialog.value = false;
    editProjectData.value = {id: null, name: '', description: '', place_name: ''};
    editProjectForm.value.resetValidation();
    Notify.create({
      type: 'positive',
      message: 'Project updated successfully!',
    });
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to update project';
    Notify.create({
      type: 'negative',
      message: errorMessage.value,
    });
  }
}

// Подтверждение удаления
function confirmDeleteProject(projectId, projectName) {
  deleteProjectId.value = projectId;
  deleteProjectName.value = projectName;
  showDeleteConfirmDialog.value = true;
}

// Удаление проекта
async function deleteProject() {
  try {
    await projectsStore.deleteProject(deleteProjectId.value);
    showDeleteConfirmDialog.value = false;
    deleteProjectId.value = null;
    deleteProjectName.value = '';

    // если на текущей странице не осталось элементов
    if (!projectsStore.projects.length && currentPage.value > 1) {
      currentPage.value -= 1; // Переходим на предыдущую страницу
    }

    await projectsStore.loadProjects(currentPage.value); // Перезагружаем текущую страницу
    Notify.create({
      type: 'positive',
      message: 'Project deleted successfully!',
    });
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed to delete project';
    Notify.create({
      type: 'negative',
      message: errorMessage.value,
    });
  }
}
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
