import {defineStore, acceptHMRUpdate} from 'pinia';
//import axios from 'axios';
import {api} from '@/boot/axios.js'; // алиас @ сработал после правки jsconfig.json !

export const useRoomsStore = defineStore('rooms', {
  state: () => ({
    rooms: [],
    currentRoomId: null,
    pagination: {
      currentPage: 1,
      lastPage: 1,
      perPage: 10, // Значение по умолчанию, можно настроить
    },
  }),

  // Геттеры
  getters: {
    currentRoom: (state) => state.rooms?.filter(t => t.id === state.currentRoomId) || [],
  },

  // Действия
  actions: {
    // Логин
    async loadRooms(project_id, page = 1, perPage = 10) {
      try {
        const response = await api.get(`/v1/project/${project_id}/rooms`, {
          params: { page, per_page: perPage },
        });
        this.rooms = response.data.data; // Данные проектов
        this.pagination = {
          currentPage: response.data.meta.current_page,
          lastPage: response.data.meta.last_page,
          perPage: response.data.meta.per_page,
        };
        return response.data;
      } catch (error) {
        throw error.response?.data || { message: 'Load rooms failed' };
      }
    },
    async editRoom(projectId, projectData) {
      try {
        const response = await api.patch(`/v1/project/${projectId}/rooms/${projectData.id}`, projectData);
        this.rooms = this.rooms.map(room =>
          room.id === projectData.id ? { ...room, ...response.data } : room
        );
        return response;
      } catch (error) {
        throw error.response?.data || { message: 'Failed to update room' };
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

  },
});

// Поддержка HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useRoomsStore, import.meta.hot));
}
