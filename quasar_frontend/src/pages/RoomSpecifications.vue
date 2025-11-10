<template>
  <q-page class="q-pa-md">
    <q-breadcrumbs>
      <q-breadcrumbs-el label="Проекты" to="/projects"/>
      <q-breadcrumbs-el :label="`Проект ${projectId}`"/>
      <q-breadcrumbs-el label="Комнаты"/>
      <q-breadcrumbs-el :label="`Комната ${roomId}`"/>
    </q-breadcrumbs>

    <div class="row items-center q-mb-sm q-mt-sm">
      <h5 class="q-my-none ">Спецификации проекта {{ projectId }} и комнаты {{ roomId }} </h5>
      <pre v-if="roomInfo">
        {{ roomInfo }}
      </pre>
    </div>

  </q-page>
</template>

<script setup>
import {useRoute} from "vue-router"
import {onMounted, ref} from "vue";
import {api} from "@/boot/axios.js";

//const router = userRouter;
const route = useRoute();

const projectId = ref(null);
const roomId = ref(null);
const roomInfo = ref(null);

const getRoomInfo = async(project_id, room_id) => {
  try {
    const response = await api.get(`/v1/project/${project_id}/rooms/${room_id}`);
    const data = response.data;
    roomInfo.value = data;
    return data;
  } catch (error) {
    throw error.response?.data || { message: 'Load room failed' };
  }
};

onMounted(() => {
  projectId.value = route.params.projectId;
  roomId.value = route.params.roomId;
  getRoomInfo(projectId.value, roomId.value);
});

</script>

<style scoped>

</style>
