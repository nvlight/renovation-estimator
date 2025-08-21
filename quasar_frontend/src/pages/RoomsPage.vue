<template>
  <q-page class="q-pa-md">
    <q-breadcrumbs>
      <q-breadcrumbs-el label="Проекты" to="/projects"/>
      <q-breadcrumbs-el :label="`Проект ${projectId}`"/>
      <q-breadcrumbs-el label="Комнаты"/>
    </q-breadcrumbs>

    <div class="row items-center q-mb-sm q-mt-sm">
      <h4 class="q-my-none ">Комнаты проекта {{ projectId }}</h4>
      <q-btn
        color="primary"
        icon="add"
        label="добавить комнату"
        class="q-ml-auto"
        @click="showAddItemDialog = true"
      />
    </div>

    <!-- Если загрузка прошла и есть комнаты - показываю -->
    <div v-if="!itemsLoading">
      <div v-if="roomsStore.rooms.length">
        <q-list bordered separator class="rounded-borders shadow-2">
          <q-item
            v-for="room in roomsStore.rooms"
            :key="room.id"
            clickable
            v-ripple
            class="q-my-sm"
          >
            <q-item-section avatar>
              <q-icon name="folder" color="primary" size="md"/>
            </q-item-section>
            <q-item-section>
              <!--            @click="goToProjectRooms(project.id)"-->
              <q-item-label
                class="text-bold"
              >{{ room.id }}
              </q-item-label>
              <q-item-label class="text-bold">{{ room.name }}</q-item-label>
              <q-item-label caption>{{ room.description || 'No description' }}</q-item-label>
              <q-item-label caption>Height: {{ room.height }}</q-item-label>
              <q-item-label caption>
                Updated: {{ formatDate(room.updated_at) }}
              </q-item-label>
            </q-item-section>

            <q-item-section side>
              <q-btn flat icon="visibility" color="grey-7" @click="openViewRoomDialog(room)"/>
              <q-btn flat icon="edit" color="primary" @click="openEditItemDialog(room)"/>
              <q-btn flat icon="delete" color="negative" @click="confirmDeleteItem(room.name, room.id)"/>
            </q-item-section>
          </q-item>
        </q-list>

        <!-- Пагинация -->
        <div class="q-mt-md flex justify-center">
          <q-pagination
            v-model="currentPage"
            :max="roomsStore.pagination.lastPage"
            :direction-links="true"
            :boundary-links="true"
            color="primary"
            @update:model-value="loadRooms"
          />
        </div>
      </div>
      <div v-else>
        <div>Пока нет комнат</div>
      </div>
    </div>
    <!-- если загружается либо показывают спиннер, либо полученную ошибку -->
    <div v-else class="text-center q-mt-md">
      <div v-if="loadErrorMessage">
        <div class="text-negative q-mb-md">
          Ошибка, попробуйте повторить позднее
        </div>
      </div>
      <div v-else class="row items-center justify-center no-wrap" style="min-width: max-content;">
        <q-spinner
          size="1em"
          class="q-mr-xs"
        />
        <div>Загрузка ...</div>
      </div>
    </div>

    <!-- Модальные окна -->

    <!-- Модальное окно для добавления проекта -->
    <q-dialog v-model="showAddItemDialog">
      <q-card style="width: 500px; max-width: 90vw;">
        <q-card-section>
          <div class="text-h6">Add New Room</div>
        </q-card-section>
        <q-card-section>
          <q-form @submit="addItem" ref="itemForm">
            <q-input
              v-model="newItem.name"
              label="Room Name *"
              :rules="[val => !!val || 'Room name is required']"
              outlined
              class="q-mb-md"
            />
            <q-input
              v-model="newItem.description"
              label="Description"
              type="textarea"
              outlined
              class="q-mb-md"
            />
            <q-input
              v-model="newItem.height"
              label="Height"
              outlined
              class="q-mb-md"
            />
            <div v-if="createItemErrorMessage" class="text-negative q-mb-md">
              {{ createItemErrorMessage }}
            </div>

            <q-card-actions align="right">
              <q-btn
                label="Cancel"
                color="negative"
                flat
                @click="showAddItemDialog = false"
              />

              <q-btn
                type="submit"
                color="primary"
                :disable="!newItem.name || isItemStoring"
                :loading="false"
                unelevated
                class="q-px-sm"
              >
                <template v-slot:default>
                  <div class="row items-center justify-center no-wrap" style="min-width: max-content;">
                    <q-spinner
                      v-if="isItemStoring"
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
    <q-dialog v-model="showEditItemDialog">
      <q-card style="width: 500px; max-width: 90vw;">
        <q-card-section>
          <div class="text-h6">Edit Room</div>
        </q-card-section>
        <q-card-section>
          <q-form @submit="editItem" ref="editItemForm">
            <q-input
              v-model="editItemData.name"
              label="Room Name *"
              :rules="[val => !!val || 'Room name is required']"
              outlined
              class="q-mb-md"
            />
            <q-input
              v-model="editItemData.description"
              label="Description"
              type="textarea"
              outlined
              class="q-mb-md"
            />
            <q-input
              v-model="editItemData.height"
              label="Height"
              outlined
              class="q-mb-md"
            />

            <div v-if="updateItemErrorMessage" class="text-negative q-mb-md">
              {{ updateItemErrorMessage }}
            </div>

            <q-card-actions align="right">
              <q-btn label="Cancel" color="negative" flat @click="showEditItemDialog = false"/>

              <q-btn
                type="submit"
                color="primary"
                :disable="!editItemData.name || isItemUpdating"
                :loading="false"
                unelevated
                class="q-px-sm"
              >
                <template v-slot:default>
                  <div class="row items-center justify-center no-wrap" style="min-width: max-content;">
                    <q-spinner
                      v-if="isItemUpdating"
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
    <q-dialog v-model="showViewItemDialog">
      <q-card style="width: 500px; max-width: 90vw;">
        <q-card-section>
          <div class="text-h6">View Item</div>
        </q-card-section>
        <q-card-section>
          <q-item-label class="q-mb-md">
            <strong>Id:</strong> {{ viewItemData.id }}
          </q-item-label>
          <q-item-label class="q-mb-md">
            <strong>Name:</strong> {{ viewItemData.name }}
          </q-item-label>
          <q-item-label class="q-mb-md">
            <strong>Description:</strong> {{ viewItemData.description || 'No description' }}
          </q-item-label>
          <q-item-label class="q-mb-md">
            <strong>Height:</strong> {{ viewItemData.height || 'No height' }}
          </q-item-label>
          <q-item-label>
            <strong>Updated:</strong> {{ formatDate(viewItemData.updated_at) }}
          </q-item-label>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn label="Close" color="primary" flat @click="showViewItemDialog = false"/>
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Модальное окно для подтверждения удаления -->
    <q-dialog v-model="showDeleteItemConfirmDialog">
      <q-card>
        <q-card-section>
          <div class="text-h6">Confirm Deletion</div>
        </q-card-section>
        <q-card-section>
          Are you sure you want to delete the project "{{ deleteItemName }}"?
        </q-card-section>

        <q-card-section>
          <div v-if="deleteItemErrorMessage" class="text-negative ">
            Ошибка, попробуйте повторить позднее
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn label="Cancel" color="negative" flat @click="showDeleteItemConfirmDialog = false"/>

          <q-btn
            type="submit"
            color="primary"
            :disable="isItemDeleting"
            :loading="false"
            unelevated
            class="q-px-sm"
            @click="deleteItem"
          >
            <template v-slot:default>
              <div class="row items-center justify-center no-wrap" style="min-width: max-content;">
                <q-spinner
                  v-if="isItemDeleting"
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
import {useRoute} from 'vue-router'
import {onMounted, ref, watch} from "vue";
import {useAuthStore} from '@/stores/auth';
import {useRoomsStore} from "@/stores/rooms.js";
import {date, Notify} from "quasar";

const route = useRoute()
const projectId = route.params.projectId;

const loadErrorMessage = ref(null);

const authStore = useAuthStore();
const roomsStore = useRoomsStore();

const currentPage = ref(parseInt(localStorage.getItem('roomsPage')) || 1);
const itemsLoading = ref(false);

const createItemErrorMessage = ref('');
const updateItemErrorMessage = ref('');
const deleteItemErrorMessage = ref('');
const isItemStoring = ref(false);
const isItemUpdating = ref(false);
const isItemDeleting = ref(false);

const showDeleteItemConfirmDialog = ref(false);
const deleteItemId = ref(null);
const deleteItemName = ref('');

const showAddItemDialog = ref(false);
const newItem = ref({
  project_id: projectId,
  name: '',
  description: '',
  height: '',
});
const itemForm = ref(null);

const editItemForm = ref(null);

const showEditItemDialog = ref(false);
const showViewItemDialog = ref(false);
const viewItemData = ref({
  id: null,
  name: '',
  description: '',
  height: '',
  updated_at: '',
});

const editItemData = ref({
  id: null,
  name: '',
  description: '',
  height: '',
});

// Загрузка проектов при смене страницы
async function loadRooms(page) {
  currentPage.value = page;

  try {
    itemsLoading.value = true;
    await roomsStore.loadRooms(projectId, currentPage.value);
  } catch (error) {
    loadErrorMessage.value = error.message || 'Failed to load projects';
  } finally {
    itemsLoading.value = false;
  }
}

// Открытие диалога просмотра
function openViewRoomDialog(room) {
  viewItemData.value = {
    id: room.id,
    name: room.name,
    description: room.description || '',
    height: room.height || '',
    updated_at: room.updated_at,
  };
  showViewItemDialog.value = true;
}

// Открытие диалога редактирования
function openEditItemDialog(room) {
  editItemData.value = {
    id: room.id,
    name: room.name,
    description: room.description || '',
    height: room.height || '',
  };
  showEditItemDialog.value = true;
}

// Добавление комнаты
async function addItem() {
  try {
    createItemErrorMessage.value = '';
    isItemStoring.value = true;
    const response = await roomsStore.addRoom(newItem.value);
    //console.log('addRoom response:', JSON.stringify(response, null, 2));
    if (response) {
      showAddItemDialog.value = false; // Закрываем диалог
      newItem.value = {project_id: projectId.value, name: '', description: '', height: ''}; // Сбрасываем форму
      itemForm.value.resetValidation(); // Сбрасываем валидацию

      Notify.create({
        type: 'positive',
        message: 'Room stored successfully!',
      });

      await roomsStore.loadRooms(projectId, currentPage.value); // Перезагружаем текущую страницу
    }
  } catch (error) {
    createItemErrorMessage.value = error.data?.message || 'Ошибка, попробуйте повторить позднее';
    console.log('addRoom error:', createItemErrorMessage.value);
  } finally {
    isItemStoring.value = false;
  }
}

// Редактирование комнаты
async function editItem() {
  try {
    isItemUpdating.value = true;
    const response = await roomsStore.editRoom(projectId, editItemData.value);

    if (response) {
      showEditItemDialog.value = false;
      editItemData.value = {id: null, name: '', description: '', height: ''};
      editItemForm.value.resetValidation();

      Notify.create({
        type: 'positive',
        message: 'Room updated successfully!',
      });
    }
  } catch (error) {
    console.log(error);
    updateItemErrorMessage.value = error?.message || 'Failed to update room';
    console.log('updateRoom error:', updateItemErrorMessage.value);
  } finally {
    isItemUpdating.value = false;
  }
}

function confirmDeleteItem(projectName, roomId) {
  deleteItemId.value = roomId;
  deleteItemName.value = projectName;
  showDeleteItemConfirmDialog.value = true;
}

// Удаление проекта
async function deleteItem() {
  try {
    isItemDeleting.value = true;
    const response = await roomsStore.deleteRoom(projectId, deleteItemId.value);
    if (response) {
      showDeleteItemConfirmDialog.value = false;
      deleteItemId.value = null;
      deleteItemName.value = '';

      // если на текущей странице не осталось элементов
      if (!roomsStore.rooms.length && currentPage.value > 1) {
        currentPage.value -= 1; // Переходим на предыдущую страницу
      }

      Notify.create({
        type: 'positive',
        message: 'Room deleted successfully!',
      });

      await roomsStore.loadRooms(projectId, currentPage.value); // Перезагружаем текущую страницу
    }
  } catch (error) {
    console.log(error)
    deleteItemErrorMessage.value = error.response?.data?.message || 'Failed to delete room';
    //console.log('deleteProject error:', deleteProjectErrorMessage.value);
  } finally {
    isItemDeleting.value = false;
  }
}

const formatDate = (timestamp) => {
  return date.formatDate(timestamp, 'DD MMMM YYYY, HH:mm'); // Формат, например, "20 May 2025, 12:11"
};

// Сохранение текущей страницы в localStorage при изменении
watch(currentPage, (newPage) => {
  localStorage.setItem('roomsPage', newPage);
});

// скрытие текста ошибки при повторном открытии формы добавления комнаты.
watch(showAddItemDialog, () => {
  if (showAddItemDialog.value) {
    createItemErrorMessage.value = '';
  }
});

// скрытие текста ошибки при повторном открытии формы редактирования комнаты.
watch(showEditItemDialog, () => {
  if (showEditItemDialog.value) {
    updateItemErrorMessage.value = '';
  }
});

// скрытие текста ошибки при повторном открытии формы удаления комнаты.
watch(showDeleteItemConfirmDialog, () => {
  if (showDeleteItemConfirmDialog.value) {
    deleteItemErrorMessage.value = '';
  }
});

onMounted(() => {
  authStore.initializeAuth(); // Инициализируем токен при загрузке
  loadRooms(currentPage.value);
})
</script>
