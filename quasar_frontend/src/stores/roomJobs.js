import {defineStore, acceptHMRUpdate} from 'pinia';
//import axios from 'axios';
import {api} from '@/boot/axios.js'; // алиас @ сработал после правки jsconfig.json !

export const useRoomJobsStore = defineStore('roomJobs', {
  state: () => ({
    roomJobs: [],
  }),

  // Геттеры
  getters: {
    roomJobsSum: () => 250000,
  },

  // Действия
  actions: {
    // Логин
    async loadRoomJobs(room_id) {
      try {
        const response = await api.get(`/v1/room/${room_id}/roomJob`);
        this.roomJobs = response.data.data; // Данные проектов
        return response.data;
      } catch (error) {
        throw error.response?.data || { message: 'Load room jobs failed' };
      }
    },
    async addRoom(roomData) {
      try {
        const addRoom = await api.post(`/v1/project/${roomData.project_id}/rooms`, roomData);
        if (addRoom) {
          this.rooms.unshift(addRoom.data);
        }
        return addRoom;
      } catch (error) {
        throw error.response || {message: 'add room failed'};
      }
    },
    async deleteRoomJob(room_id, jobId) {
      try {
        const deleted = await api.delete(`/v1/room/${room_id}/roomJob/${jobId}`);
        if (deleted) {
          this.roomJobs = this.roomJobs.filter(pr => pr.id !== jobId);
        }
        return true;
      } catch (error) {
        console.log(error.response?.data || {message: 'delete room job failed'})
        return false;
      }
    },
  },
});

// Поддержка HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useRoomJobsStore, import.meta.hot));
}
