<template>
  <q-page class="q-ma-md">
    <h5 class="text-subtitle11">Строительные материалы</h5>
    <div>
      <q-btn color="primary">Добавить материал</q-btn>
    </div>

    <div class="q-mt-md">
      <q-table
        title=""
        :rows="rows"
        :columns="columns"
        row-key="name"
        :pagination="{ rowsPerPage: 20 }"
      >
        <!-- Ячейка с кнопкой удаления -->
        <template v-slot:body-cell-actions="props">
          <q-td :props="props">
            <q-btn
              flat
              round
              size="md"
              color="negative"
              icon="delete"
              @click="deleteMaterial(props.row)"
            />
            <q-btn
              flat
              round
              size="md"
              color="blue"
              icon="visibility"
              @click="viewMaterial(props.row)"
            />
          </q-td>
        </template>
      </q-table>

    </div>

    <!--      <pre>{{ materials }}</pre>-->
  </q-page>
</template>

<script setup>
import {computed, onMounted} from "vue";
import {useMaterialsStore} from "@/stores/materials.js";
import {Notify} from "quasar";
import {useRouter} from "vue-router";

const router = useRouter();
const materialsStore = useMaterialsStore();

const columns = [
  { name: 'actions', label: 'actions', align: 'left', field: 'actions' },
  {
    name: 'id',
    required: true,
    label: 'id',
    align: 'left',
    field: row => row.id,
    format: val => `${val}`,
    sortable: true
  },
  { name: 'title', align: 'left', label: 'title', field: 'title', sortable: true },
  { name: 'price', label: 'price', field: 'price', sortable: true },
  { name: 'brand', label: 'brand', field: 'brand', sortable: true },
  { name: 'description', label: 'description', align: 'left', field: 'description' },
];

const rows = computed( () => {
  return materialsStore.items.map(item => {
    return {
      id: item.id,
      title: item.title,
      price: item.price,
      brand: item.brand,
      description: item.description,
    }
  });
});

const viewMaterial = (row) => {
  router.push({name: 'MaterialDetail', params: { material_id: row.id }});
}

const deleteMaterial = (row) => {
  if (!confirm('Действительно удалить строительный материал?')) {
    return;
  }

  const del = materialsStore.deleteItem(row.id);
  del.then( () => {
    Notify.create({
      type: 'positive',
      message: 'Строительный материал удален!',
    });
  }).catch( () => {
    Notify.create({
      type: 'negative',
      message: 'Ошибка при удалении!',
    });
  });
}

onMounted( () => {
  materialsStore.loadItems();
});
</script>

<style scoped>

</style>
