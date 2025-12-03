<template>
  <div>
    <h5>Картинки </h5>

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

    <!-- upload images -->
    <div class="q-ma-md ">
      <q-uploader
        ref="uploaderRef"
        :url="uploaderUrl"
        :headers="uploadHeaders"
        max-files="1"
        accept="image/*"
        field-name="name"
        :form-fields="formFields"
        :auto-upload="false"
        :max-file-size="20000000"
        @added="onFileAdded"
        @uploaded="onFileUploaded"
        label="Выберите изображение (макс. 1200x800 px)"
        square
        flat
        bordered
        style="max-width: 300px"
      >
      </q-uploader>
    </div>
  </div>
</template>

<script setup>
import {computed, onMounted, ref, watch} from "vue";
import {Notify} from "quasar";
import {api} from "@/boot/axios.js";

const props = defineProps({
  materialId: {
    type: Number,
    required: true,
  },
  images: {
    type: Array,
    default: () => [],
  },
});

const localImages = ref([]);

// Код ниже отвечает за сохранения картинки
// Реактивные данные
const uploaderRef = ref(null)

const sortedImages = computed(() => {
  if (!localImages.value || !Array.isArray(localImages.value)) {
    return [];
  }
  return [...localImages.value].sort((a, b) => a.sort - b.sort);
});

const deleteMaterialImage = async (material_image_id) => {
  if (!confirm('Действительно удалить?')) return;

  const del = await api.delete(`/v1/material_image/${material_image_id}`);
  if (del?.status === 200) {

    localImages.value = localImages.value.filter(item => item.id !== material_image_id);

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

    localImages.value = move.data.images;
  }
}

const uploaderUrl = computed(() => {
  return `${api.defaults.baseURL}/v1/material_image/`;
});
const uploadHeaders = [
  { name: 'Authorization', value: `Bearer ${localStorage.getItem('auth_token')}` },
  { name: 'Accept', value: 'application/json' }
];
const formFields = computed(() => [
  { name: 'material_id', value: props.materialId }
]);

const checkImageDimensions = (file) => {
  const reader = new FileReader();

  reader.onload = (e) => {
    const img = new Image()
    img.onload = () => {
      if (img.naturalWidth > 1200 || img.naturalHeight > 925) {
        Notify.create({
          type: 'negative',
          message: 'Изображение слишком большое (макс. 1200x925 px)'
        })

        uploaderRef.value.removeFile(file)
      }
    }
    img.src = e.target.result
  }
  reader.readAsDataURL(file)
}

const onFileUploaded = (info) => {
  if (info.xhr.status === 201) {
    const result = JSON.parse(info.xhr.response);
    if (result.success){
      localImages.value = result.images;
      Notify.create({
        type: 'positive',
        message: 'Файл успешно загружен'
      })
    }
  }
}

const onFileAdded = (files) => {
  if (files.length === 0) return

  const file = files[0];

  checkImageDimensions(file)
}

// Каждый раз при изменении props.images
watch(
  () => props.images,
  (newVal) => {
    localImages.value = [...newVal]
  },
  {immediate: true} // важное! заполнит localImages сразу
);

const documentTitle = computed(() => {
  return 'Строительные материалы';
});

onMounted(() => {
  document.title = documentTitle.value;
});

</script>

<style scoped>
</style>
