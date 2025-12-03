<template>
  <q-table
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
import {computed, onMounted, ref} from "vue";
import {useRoomMaterialsStore} from "@/stores/roomMaterials.js";

const props = defineProps({
  roomId: {
    type: Number,
    required: true,
  }
});

const roomMaterialsStore = useRoomMaterialsStore();
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

const rows = computed(() =>  {
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

const deleteMaterial = async (roomId, row) => {
  await roomMaterialsStore.deleteItem(roomId, row.id)
}

onMounted(async () => {
  await roomMaterialsStore.loadItems(roomId.value);
});
</script>

<style scoped>

</style>
