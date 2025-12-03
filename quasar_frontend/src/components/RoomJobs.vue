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
<!--  <div><pre>{{ roomJobsStore.roomJobs }}</pre></div>-->
</template>

<script setup>
import {computed, onMounted, ref} from "vue";
import {useRoomJobsStore} from "@/stores/roomJobs.js";

const props = defineProps({
  roomId: {
    type: Number,
    required: true,
  }
});

const roomJobsStore = useRoomJobsStore();
const roomId = ref(props.roomId);

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

const formatMoreInfo = (moreInfo) => {
  if (!moreInfo || !Array.isArray(moreInfo)) return '[]';

  return '[' + moreInfo.map(obj => {
    if (typeof obj === 'object' && obj !== null) {
      const parts = [];

      // Добавляем поля в нужном порядке с переименованием и проверкой на существование
      if (obj.name !== undefined && obj.name !== null && obj.name !== '') {
        parts.push(`${obj.name}`);
      }
      if (obj.price !== undefined && obj.price !== null) {
        parts.push(`цена: ${obj.price}`);
      }
      if (obj.amount !== undefined && obj.amount !== null) {
        parts.push(`количество: ${obj.amount}`);
      }
      if (obj.sum !== undefined && obj.sum !== null) {
        parts.push(`сумма: ${obj.sum}`);
      }

      // Если нет ни одного поля, возвращаем пустую строку
      return parts.length > 0 ? parts.join(', ') : '';
    }
    return String(obj);
  }).filter(str => str !== '').join('; ') + ']'; // Фильтруем пустые строки
};

const rows = computed(() =>  {
  return roomJobsStore.roomJobs.map(item => ({
    id: item.id,
    room_id: item.room_id,
    title: item.title,
    sum: item.sum,
    moreInfo: formatMoreInfo(item.more_info),
  }));
});

const deleteJob = async (row) => {
  await roomJobsStore.deleteRoomJob(roomId.value, row.id)
}

onMounted(async () => {
  await roomJobsStore.loadRoomJobs(roomId.value);
});
</script>

<style scoped>

</style>
