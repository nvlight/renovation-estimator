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
          @click="deleteJob(props.row)"
        />
      </q-td>
    </template>
  </q-table>
</template>

<script setup>
import {onMounted, ref} from "vue";
import {useRoomJobsStore} from "@/stores/roomJobs.js";

const props = defineProps({
  roomId: {
    type: Number,
    required: true,
  }
});

const roomJobsStore = useRoomJobsStore();
const roomId = ref(props.roomId);
const roomJobs = ref([]);

const columns = [
  {
    name: 'id',
    required: true,
    label: 'id',
    align: 'left',
    field: row => row.id,
    format: val => `${val}`,
    sortable: true
  },
  // {name: 'roomId', align: 'center', label: 'roomId', field: 'roomId', sortable: false},
  {name: 'title', label: 'Название', field: 'title', sortable: true, align: 'left',},
  {name: 'sum', label: 'Сумма', field: 'sum', sortable: true},

  {name: 'moreInfo', label: 'Дополнительно', field: 'moreInfo', align: 'left',},
  {name: 'actions', label: 'Действия', field: 'actions'},
]

const rows = ref([]);

const fillRoomJobs = () => {
  rows.value = roomJobs.value.data.map(item => ({
    id: item.id,
    roomId: item.room_id,
    title: item.title,
    sum: item.sum,
    moreInfo: item.more_info,
  }));
};

const deleteJob = async (row) => {
  const success = await roomJobsStore.deleteRoomJob(roomId.value, row.id)
  if (success){
    rows.value = rows.value.filter(r => r.id !== row.id)
  }
}

onMounted(async () => {
  roomJobs.value = await roomJobsStore.loadRoomJobs(roomId.value);
  fillRoomJobs();
});
</script>

<style scoped>

</style>
