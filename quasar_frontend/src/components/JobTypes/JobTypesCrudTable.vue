<template>
  <q-table
    title=""
    :rows="rows"
    :columns="columns"
    row-key="name"
    :pagination="{ rowsPerPage: 10 }"
   />
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
    field: row => row.name,
    format: val => `${val}`,
    sortable: true
  },
  // {name: 'roomId', align: 'center', label: 'roomId', field: 'roomId', sortable: false},
  {name: 'title', label: 'Название', field: 'title', sortable: true, align: 'left',},
  {name: 'sum', label: 'Сумма', field: 'sum'},

  {name: 'moreInfo', label: 'Дополнительно', field: 'moreInfo', align: 'left',},
  {name: 'delete', label: 'delete', field: 'delete'},
]

const rows = ref([]);

const fillRoomJobs = () => {
  rows.value = roomJobs.value.data.map(item => ({
    name: item.id,
    //roomId: item.room_id,
    title: item.title,
    sum: item.sum,
    moreInfo: item.more_info,
  }));
};

onMounted(async  () => {
  roomJobs.value = await roomJobsStore.loadRoomJobs(roomId.value);
  fillRoomJobs();
});
</script>

<style scoped>

</style>
