import {defineStore, acceptHMRUpdate} from 'pinia';
//import axios from 'axios';
import {api} from '@/boot/axios.js'; // алиас @ сработал после правки jsconfig.json !

export const useRoomMaterialsStore = defineStore('roomMaterials', {
  state: () => ({
    items: [],
  }),

  // Геттеры
  getters: {
    roomMaterialsSum() {
      return this.items.reduce((acc, item) => acc + (item.sum || 0), 0);
    },
  },

  actions: {
    async loadItems(roomId) {
      try {
        const response = await api.get(`/v1/room/${roomId}/roomMaterial`);
        this.items = response.data.data;
        return response.data;
      } catch (error) {
        console.log(error.response?.data || {message: 'load room_materials failed'})
        return false;
      }
    },
    async showItem(item_id) {
      try {
        const response = await api.get(`/v1/room_material/${item_id}`);
        return response.data;
      } catch (error) {
        console.log(error.response?.data || {message: 'load material failed'})
        throw error;
      }
    },
    async addItem(roomId, data) {
      try {
        const addItem = await api.post(`/v1/room/${roomId}/roomMaterial`, data);
        if (addItem) {
          this.items.push(addItem.data.data);
        }
        return addItem;
      } catch (error) {
        console.log(error.response?.data || {message: 'store room material failed'})
        return false;
      }
    },
    async deleteItem(roomId, item_id) {
      try {
        const deleted = await api.delete(`/v1/room/${roomId}/roomMaterial/${item_id}`);
        if (deleted) {
          this.items = this.items.filter(pr => pr.id !== item_id);
        }
        return true;
      } catch (error) {
        console.log(error.response?.data || {message: 'delete room material failed'})
        throw error;
      }
    },
  },
});

// Поддержка HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useRoomMaterialsStore, import.meta.hot));
}
