<template>
  <q-page class="q-ma-md">
    <h5>MaterialView</h5>
    <q-table
      title=""
      :rows="rows"
      :columns="columns"
      row-key="name"
      :pagination="{ rowsPerPage: 20 }"
    >
    </q-table>

    <div v-if="material.images"
         class="flex q-mt-md"
         style="gap: 10px;"
    >
      <div v-for="(item,i) in material.images" :key="i">
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
            @click="deleteMaterialImage(item.id)"
          />
          <q-btn
            flat
            round
            size="md"
            color="positive"
            icon="chevron_right"
            @click="deleteMaterialImage(item.id)"
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

    <div><pre>{{ material?.images }}</pre></div>
  </q-page>
</template>

<script setup>
import {useRoute} from 'vue-router';
import {onMounted, ref} from "vue";
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

const deleteMaterialImage = async (material_image_id) => {
  const del = await api.delete(`/v1/material_image/${material_image_id}`);
  if (del?.status === 200){

    material.value.images = material.value.images.filter(item => item.id !== material_image_id);

    Notify.create({
      type: 'positive',
      message: 'Картинка удалена!',
    });
  }else{
    Notify.create({
      type: 'negative',
      message: 'Ошибка при удалении!',
    });
  }
}

onMounted(() => {
  loadMaterial(materialId);
});
</script>

<style scoped>

</style>
