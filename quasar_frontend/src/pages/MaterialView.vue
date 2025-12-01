<template>
  <q-page class="q-ma-md">
    <q-breadcrumbs>
      <q-breadcrumbs-el label="Проекты" :to="{name: 'Materials'}"/>
      <q-breadcrumbs-el :label="`Проект ${materialId}`" />
    </q-breadcrumbs>

    <h5>Просмотр материала ({{ materialId }})</h5>

    <q-table
      title=""
      :rows="rows"
      :columns="columns"
      row-key="name"
      :pagination="{ rowsPerPage: 20 }"
    >
    </q-table>

    <div v-if="sortedImages"
         class="flex q-mt-md"
         style="gap: 10px;"
    >
      <div v-for="(item,i) in sortedImages" :key="i">
        <img
          :src="item.url"
          width="200px"
          height="100px"
          alt=""
        />
        <div class="flex justify-center">
          <q-btn
            flat
            round
            size="md"
            color="positive"
            icon="chevron_left"
            @click="moveMaterialImage(item.id,'left')"
          />
          <q-btn
            flat
            round
            size="md"
            color="positive"
            icon="chevron_right"
            @click="moveMaterialImage(item.id,'right')"
          />

          <q-btn
            flat
            round
            size="md"
            color="negative"
            icon="delete"
            @click="deleteMaterialImage(item.id)"
          />
        </div>
      </div>
    </div>

    <!--    <div><pre>{{ material?.images }}</pre></div>-->

    <!-- upload images -->
    <div class="q-ma-md ">
      <q-uploader
        ref="uploaderRef"
        max-files="1"
        accept="image/*"
        :auto-upload="false"
        :max-file-size="20000000"
        @added="checkImageDimensions"
        @removed="onFileRemoved"
        label="Выберите изображение (макс. 1200x800 px)"
        square
        flat
        bordered
        style="max-width: 300px"
      />

      <q-btn
        label="Отправить на сервер"
        @click="submitFile"
        color="primary"
        class="q-mt-md"
        :disable="!selectedFile"
      />
    </div>
  </q-page>
</template>

<script setup>
import {useRoute} from 'vue-router';
import {computed, onMounted, ref} from "vue";
import {useMaterialsStore} from "@/stores/materials.js";
import {api} from "@/boot/axios.js";
import {Notify} from "quasar";

const materialStore = useMaterialsStore();
const route = useRoute();

const columns = [
  {
    name: 'key',
    required: true,
    label: 'key',
    align: 'left',
    field: row => row.key,
    format: val => `${val}`,
    sortable: true
  },
  {
    name: 'value',
    required: true,
    label: 'value',
    align: 'left',
    field: row => row.value,
    format: val => `${val}`,
    sortable: true
  },
];

const rows = ref([]);

const materialId = route.params.material_id;
const material = ref({});

const loadMaterial = (materialId) => {
  materialStore.showItem(materialId).then(response => {
    material.value = response;

    rows.value = Object.entries(material.value).map(([key, value]) => {
      let displayValue = value;
      if (value !== null && typeof value === 'object') {
        // Используем Вариант C для примера
        displayValue = Object.entries(value)
          .map(([k, v]) => `${k}: ${v}`)
          .join(', ');
      }
      return {key, value: displayValue};
    });
  });
}

const sortedImages = computed(() => {
  if (!material.value.images || !Array.isArray(material.value.images)) {
    return [];
  }
  return [...material.value.images].sort((a, b) => a.sort - b.sort);
});

const deleteMaterialImage = async (material_image_id) => {
  if (!confirm('Действительно удалить?')) return;

  const del = await api.delete(`/v1/material_image/${material_image_id}`);
  if (del?.status === 200) {

    material.value.images = material.value.images.filter(item => item.id !== material_image_id);

    Notify.create({
      type: 'positive',
      message: 'Картинка удалена!',
    });
  } else {
    Notify.create({
      type: 'negative',
      message: 'Ошибка при удалении!',
    });
  }
}

const moveMaterialImage = async (material_image_id, direction) => {
  const api_dir = direction === 'left' ? 'left' : 'right';
  const move = await api.patch(`/v1/material_image/${material_image_id}/to_${api_dir}`);
  if (move?.status === 200 && move.data.success) {
    material.value.images = move.data.images;
  }
}

onMounted(() => {
  loadMaterial(materialId);
});


// Код ниже отвечает за сохранения картинки
// Реактивные данные
const uploaderRef = ref(null)
const selectedFile = ref(null)

// Методы
const checkImageDimensions = (files) => {
  const file = files[0]
  const img = new Image()

  img.onload = () => {
    if (img.width > 1200 || img.height > 800) {
      Notify.create({
        type: 'negative',
        message: 'Размер изображения не должен превышать 1200x800 px'
      })
      uploaderRef.value.removeFile(file)
    } else {
      selectedFile.value = file
    }
    URL.revokeObjectURL(img.src)
  }

  img.onerror = () => {
    Notify.create({
      type: 'negative',
      message: 'Файл не является изображением'
    })
    uploaderRef.value.removeFile(file)
    URL.revokeObjectURL(img.src)
  }

  img.src = URL.createObjectURL(file)
}

const onFileRemoved = () => {
  selectedFile.value = null
}

const submitFile = async () => {
  if (!selectedFile.value) {
    Notify.create({
      type: 'warning',
      message: 'Сначала выберите файл'
    })
    return
  }

  try {
    const formData = new FormData()
    formData.append('name', selectedFile.value);
    formData.append('material_id', materialId);

    const result = await api.post('/v1/material_image/', formData);
    if (result.status === 201){
      material.value.images = result.data.images;
    }

    Notify.create({
      type: 'positive',
      message: 'Файл успешно загружен'
    })

    // Очищаем после успешной загрузки
    uploaderRef.value.removeQueuedFiles()
    selectedFile.value = null

  } catch (error) {
    Notify.create({
      type: 'negative',
      message: 'Ошибка загрузки файла'
    })
    console.error('Upload error:', error)
  }
}

</script>

<style scoped>

</style>
