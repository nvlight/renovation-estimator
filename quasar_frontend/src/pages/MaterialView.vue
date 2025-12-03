<template>
  <q-page class="q-ma-md">
    <q-breadcrumbs>
      <q-breadcrumbs-el label="Строительные материалы" :to="{name: 'Materials'}"/>
      <q-breadcrumbs-el :label="`Материал ${materialId}`" />
    </q-breadcrumbs>

    <h5>{{ materialTitle }} (id={{ materialId }})</h5>

    <q-table
      title=""
      :rows="rows"
      :columns="columns"
      row-key="name"
      :pagination="{ rowsPerPage: 20 }"
    >
    </q-table>

    <material-image
      :materialId="materialId"
      :images="material?.images"
    />

  </q-page>
</template>

<script setup>
import {useRoute} from 'vue-router';
import {computed, onMounted, ref} from "vue";
import {useMaterialsStore} from "@/stores/materials.js";
import MaterialImage from "@/pages/MaterialImage.vue";

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

const materialId = Number(route.params.material_id);
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

const materialTitle = computed(() => {
  return rows.value.filter(i => i.key === 'title')[0]?.value;
})

const documentTitle = computed(() => {
  return `Просмотр материала (id=${materialId})`;
});

onMounted(() => {
  loadMaterial(materialId);
  document.title = documentTitle.value;
});

</script>

<style scoped>

</style>
