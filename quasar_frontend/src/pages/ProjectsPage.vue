<template>
  <q-page class="q-pa-md">
    <div class="row items-center q-mb-md">
      <h4 class="q-my-none">Проекты</h4>
      <q-btn
        color="primary"
        icon="add"
        label="Add Project"
        class="q-ml-auto"
        @click="showAddProjectDialog = true"
      />
    </div>

    <!-- Показ проектов, если они есть  -->
    <div v-if="!loadErrorMessage && projectsStore.projects.length">
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
            <q-item-label
              class="text-bold"
              @click="goToProjectRooms(project.id)"
            >{{ project.id }}</q-item-label>
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
      <!-- Если ошибок нет, показываю что проекты загружаются -->
      <div v-if="!loadErrorMessage" >
        <div class="row items-center justify-center no-wrap" style="min-width: max-content;">
          <q-spinner
            size="1em"
            class="q-mr-xs"
          />
          <div>loading ...</div>
        </div>

      </div>
      <!-- Иначе показываю шаблонную ошибку -->
      <div v-else class="text-negative q-mb-md">
        Ошибка, попробуйте повторить позднее
      </div>
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
            <div v-if="createProjectErrorMessage" class="text-negative q-mb-md">
              Ошибка, попробуйте повторить позднее
            </div>

            <q-card-actions align="right">
              <q-btn
                label="Cancel"
                color="negative"
                flat
                @click="showAddProjectDialog = false"
              />

              <q-btn
                type="submit"
                color="primary"
                :disable="!newProject.name || isProjectStoring"
                :loading="false"
                unelevated
                class="q-px-sm"
              >
                <template v-slot:default>
                  <div class="row items-center justify-center no-wrap" style="min-width: max-content;">
                    <q-spinner
                      v-if="isProjectStoring"
                      color="white"
                      size="0.8em"
                      class="q-mr-xs"
                    />
                    <span>Save</span>
                  </div>
                </template>
              </q-btn>

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

            <div v-if="updateProjectErrorMessage" class="text-negative q-mb-md">
              Ошибка, попробуйте повторить позднее
            </div>

            <q-card-actions align="right">
              <q-btn label="Cancel" color="negative" flat @click="showEditProjectDialog = false"/>

              <q-btn
                type="submit"
                color="primary"
                :disable="!editProjectData.name || isProjectUpdating"
                :loading="false"
                unelevated
                class="q-px-sm"
              >
                <template v-slot:default>
                  <div class="row items-center justify-center no-wrap" style="min-width: max-content;">
                    <q-spinner
                      v-if="isProjectUpdating"
                      color="white"
                      size="0.8em"
                      class="q-mr-xs"
                    />
                    <span>Update</span>
                  </div>
                </template>
              </q-btn>

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
            <strong>id:</strong> {{ viewProjectData.id }}
          </q-item-label>
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

        <q-card-section>
          <div v-if="deleteProjectErrorMessage" class="text-negative q-mb-md">
            Ошибка, попробуйте повторить позднее
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn label="Cancel" color="negative" flat @click="showDeleteConfirmDialog = false"/>
          <!--          <q-btn label="Delete" color="primary" @click="deleteProject"/>-->

          <q-btn
            type="submit"
            color="primary"
            :disable="isProjectDeleting"
            :loading="false"
            unelevated
            class="q-px-sm"
            @click="deleteProject"
          >
            <template v-slot:default>
              <div class="row items-center justify-center no-wrap" style="min-width: max-content;">
                <q-spinner
                  v-if="isProjectDeleting"
                  color="white"
                  size="0.8em"
                  class="q-mr-xs"
                />
                <span>Delete</span>
              </div>
            </template>
          </q-btn>
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import {useAuthStore} from '@/stores/auth';
import {useProjectsStore} from '@/stores/projects';
import {ref, watch, onMounted} from "vue";
import {date} from 'quasar'; // Импортируем утилиту для форматирования даты
import {Notify} from 'quasar';
import { useRouter } from 'vue-router'

const authStore = useAuthStore();
const projectsStore = useProjectsStore();
const loadErrorMessage = ref(null);
const router = useRouter();

const goToProjectRooms = (projectId) => {
  router.push({name: 'ProjectRooms', params: { projectId }});
}

const createProjectErrorMessage = ref('');
const updateProjectErrorMessage = ref('');
const deleteProjectErrorMessage = ref('');
const isProjectStoring = ref(false);
const isProjectUpdating = ref(false);
const isProjectDeleting = ref(false);

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

// Форматирование даты
const formatDate = (timestamp) => {
  return date.formatDate(timestamp, 'DD MMMM YYYY, HH:mm'); // Формат, например, "20 May 2025, 12:11"
};

// Загрузка проектов при смене страницы
async function loadProjects(page) {
  currentPage.value = page;
  try {
    await projectsStore.loadProjects(page);
  } catch (error) {
    loadErrorMessage.value = error.message || 'Failed to load projects';
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
    isProjectStoring.value = true;
    const response = await projectsStore.addProject(newProject.value);
    console.log('addProject response:', JSON.stringify(response, null, 2));
    if (response) {
      showAddProjectDialog.value = false; // Закрываем диалог
      newProject.value = {name: '', description: '', place_name: ''}; // Сбрасываем форму
      projectForm.value.resetValidation(); // Сбрасываем валидацию

      Notify.create({
        type: 'positive',
        message: 'Project stored successfully!',
      });

      await projectsStore.loadProjects(currentPage.value); // Перезагружаем текущую страницу
    }
  } catch (error) {
    createProjectErrorMessage.value = error.response?.data?.message || 'Failed to add project';
    console.log('addProject error:', createProjectErrorMessage.value);
  } finally {
    isProjectStoring.value = false;
  }
}

// Редактирование проекта
async function editProject() {
  try {
    isProjectUpdating.value = true;
    const response = await projectsStore.editProject(editProjectData.value.id, {
      name: editProjectData.value.name,
      description: editProjectData.value.description,
      place_name: editProjectData.value.place_name,
    });

    if (response) {
      showEditProjectDialog.value = false;
      editProjectData.value = {id: null, name: '', description: '', place_name: ''};
      editProjectForm.value.resetValidation();

      Notify.create({
        type: 'positive',
        message: 'Project updated successfully!',
      });
    }
  } catch (error) {
    updateProjectErrorMessage.value = error.response?.data?.message || 'Failed to update project';
    console.log('updateProject error:', updateProjectErrorMessage.value);
  } finally {
    isProjectUpdating.value = false;
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
    isProjectDeleting.value = true;
    const response = await projectsStore.deleteProject(deleteProjectId.value);
    if (response) {
      showDeleteConfirmDialog.value = false;
      deleteProjectId.value = null;
      deleteProjectName.value = '';

      // если на текущей странице не осталось элементов
      if (!projectsStore.projects.length && currentPage.value > 1) {
        currentPage.value -= 1; // Переходим на предыдущую страницу
      }

      Notify.create({
        type: 'positive',
        message: 'Project deleted successfully!',
      });

      await projectsStore.loadProjects(currentPage.value); // Перезагружаем текущую страницу
    }
  } catch (error) {
    deleteProjectErrorMessage.value = error.response?.data?.message || 'Failed to delete project';
    console.log('deleteProject error:', deleteProjectErrorMessage.value);
  } finally {
    isProjectDeleting.value = false;
  }
}

// Сохранение текущей страницы в localStorage при изменении
watch(currentPage, (newPage) => {
  localStorage.setItem('projectsPage', newPage);
});

// скрытие текста ошибки при повторном открытии формы добавления проекта.
watch(showAddProjectDialog, () => {
  if (showAddProjectDialog.value) {
    createProjectErrorMessage.value = '';
  }
});

// скрытие текста ошибки при повторном открытии формы редактирования проекта.
watch(showEditProjectDialog, () => {
  if (showEditProjectDialog.value) {
    updateProjectErrorMessage.value = '';
  }
});

// скрытие текста ошибки при повторном открытии формы удаления проекта.
watch(showDeleteConfirmDialog, () => {
  if (showDeleteConfirmDialog.value) {
    deleteProjectErrorMessage.value = '';
  }
});

onMounted(() => {
  authStore.initializeAuth(); // Инициализируем токен при загрузке
  loadProjects(currentPage.value); // Загрузка проектов по текущей странице при загрузке
})

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
