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
    async deleteRoom(projectId, roomId) {
      try {
        const deleted = await api.delete(`/v1/project/${projectId}/rooms/${roomId}`);
        if (deleted) {
          this.rooms = this.rooms.filter(pr => pr.id !== roomId);
        }
        return true;
      } catch (error) {
        throw error.response?.data || {message: 'delete room failed'};
      }
    },
  },
});

// Поддержка HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useRoomJobsStore, import.meta.hot));
}
