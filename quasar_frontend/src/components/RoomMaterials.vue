<template>
  <q-btn color="primary" label="Добавить новый" @click="toggleAddNew"/>
  <div v-if="addNew">

    <q-input
      v-model="searchQuery"
      placeholder="Поиск материалов..."
      outlined
      clearable
      class="q-mb-md q-mt-md"
      @clear="searchQuery = ''"
    >
      <template v-slot:prepend>
        <q-icon name="search"/>
      </template>
    </q-input>

    <div class="text-subtitle2">Всего {{ materialsStore.items.length }} позиций материалов</div>

    <div>
      <div>
<!--        <div>{{ selectedMaterial }} boo: {{ Number(Boolean(selectedMaterial.id))  }}</div>-->
        <q-input color="purple-12" disable v-model="selectedMaterial.title" label="Выбранный материал" class=""/>
        <div class="row" style="gap: 15px;">
          <q-input v-model="selectedMaterial.amount" label="количество"/>
          <q-input v-model="selectedMaterial.sum" label="сумма за единицу"/>
          <q-input class="custom-disabled-input" disable v-model="selectedMaterial.total" label="общая сумма"/>
          <q-btn
            :disable="!Boolean(selectedMaterial.material_id)"
            color="primary"
            label="Добавить материал"
            @click="addMaterial"/>
        </div>
      </div>
    </div>

    <!-- Список материалов -->
    <div v-if="filteredMaterials.length > 0" class="q-mt-md">
      <div class="" style="height: 133px; overflow-y: scroll">
        <q-list bordered separator>
          <q-item
            v-for="material in filteredMaterials"
            :key="material.id"
            clickable
            v-ripple
            @click="pickMaterial(material)"
          >
            <q-item-section>
              <q-item-label>{{ material.title }}</q-item-label>
              <q-item-label caption>
                id: {{ material.id }} • Цена: {{ material.price }}
              </q-item-label>
            </q-item-section>

          </q-item>
        </q-list>
      </div>
    </div>

    <!--    <pre>{{ materialsStore.items }}</pre>-->

  </div>

  <q-table
    class="q-mt-md"
    title=""
    :rows="rows"
    :columns="columns"
    row-key="name"
    :pagination="{ rowsPerPage: 10 }"
  >
    <!-- Ячейка с кнопкой удаления -->
    <template v-slot:body-cell-actions="props">
      <q-td :props="props">
        <q-btn
          flat
          round
          size="sm"
          color="negative"
          icon="delete"
          @click="deleteMaterial(roomId, props.row)"
        />
      </q-td>
    </template>
  </q-table>
  <!--  <div><pre>{{ roomJobsStore.roomJobs }}</pre></div>-->
</template>

<script setup>
import {computed, onMounted, ref, watch} from "vue";
import {useRoomMaterialsStore} from "@/stores/roomMaterials.js";
import {useMaterialsStore} from "@/stores/materials.js";

const props = defineProps({
  roomId: {
    type: Number,
    required: true,
  }
});

const addNew = ref(false);
const selectedMaterial = ref({
  material_id: null,
  title: '',
  amount: 0,
  sum: 0,
  total: 0,
});
const toggleAddNew = () => {
  addNew.value = !addNew.value;
  if (!addNew.value){
    selectedMaterial.value.material_id = null;
  }
}
const searchQuery = ref('');

const roomMaterialsStore = useRoomMaterialsStore();
const materialsStore = useMaterialsStore();
const roomId = ref(props.roomId);

const columns = [
  {name: 'id', align: 'left', label: 'id', field: 'id', sortable: true},
  //{name: 'roomId', align: 'center', label: 'roomId', field: 'room_id', sortable: true},
  //{name: 'materialId', align: 'center', label: 'materialId', field: 'material_id', sortable: true},
  // {name: 'room_title', align: 'left', label: 'room_title', field: 'room_title', sortable: true},
  {name: 'material_title', align: 'left', label: 'Материал', field: 'material_title', sortable: true},

  {name: 'amount', label: 'Количество', field: 'amount', sortable: true, align: 'right',},
  {name: 'sum', label: 'Сумма', field: 'sum', sortable: true, align: 'right',},

  {name: 'actions', label: 'Действия', field: 'actions'},
];

const rows = computed(() => {
  return roomMaterialsStore.items.map(item => ({
    id: item.id,
    room_id: item.room_id,
    material_id: item.material_id,
    room_title: item.room_title,
    material_title: item.material_title,
    amount: item.amount,
    sum: item.sum,
  }));
});

// Отфильтрованные материалы
const filteredMaterials = computed(() => {
  if (!searchQuery.value.trim()) {
    return materialsStore.items;
  }

  const query = searchQuery.value.toLowerCase();
  return materialsStore.items.filter(material =>
    //material.title.toLowerCase().includes(query) ||
    //material.product_code.toLowerCase().includes(query)
    material.title.toLowerCase().includes(query)
  );
});

const addMaterial = () => {
  console.log(selectedMaterial.value);
}

const pickMaterial = (material) => {
  console.log(material);
  selectedMaterial.value.material_id = material.id;
  selectedMaterial.value.room_id = roomId.value;
  selectedMaterial.value.title = material.title;
  selectedMaterial.value.amount = 1;
  selectedMaterial.value.sum = +material.price;
  selectedMaterial.value.total = Math.ceil(selectedMaterial.value.sum * selectedMaterial.value.amount);
}

watch(
  () => selectedMaterial.value?.amount,
  (newAmount) => {
    //console.log(newAmount);
    if (selectedMaterial.value && newAmount !== undefined) {
      const price = selectedMaterial.value.sum || 0;
      const total = newAmount * price;
      //console.log('Сумма:', total);
      // или обновление какого-то значения
      selectedMaterial.value.total = total;
    }
  },
  { deep: true }
);

const deleteMaterial = async (roomId, row) => {
  await roomMaterialsStore.deleteItem(roomId, row.id)
}

onMounted(async () => {
  await roomMaterialsStore.loadItems(roomId.value);
  await materialsStore.loadItems(roomId.value);
});
</script>

<style scoped>
.custom-disabled-input input {
  color: #9c27b0 !important; /* purple-500 */
  opacity: 0.8;
}

</style>
