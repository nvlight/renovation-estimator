import {defineStore, acceptHMRUpdate} from 'pinia';
//import axios from 'axios';
import {api} from '@/boot/axios.js'; // алиас @ сработал после правки jsconfig.json !

export const useMaterialsStore = defineStore('materials', {
  state: () => ({
    items: [],
  }),

  // Геттеры
  getters: {
    // roomJobsSum() {
    //   return this.items.reduce((acc, item) => acc + (item.sum || 0), 0);
    // },
  },

  actions: {
    async loadItems() {
      try {
        const response = await api.get(`/v1/material`);
        this.items = response.data.data;
        return response.data;
      } catch (error) {
        console.log(error.response?.data || {message: 'load materials failed'})
        return false;
      }
    },
    async addItem(room_id, roomJobData) {
      try {
        const addRoom = await api.post(`/v1/room/${room_id}/roomJob`, roomJobData);
        if (addRoom) {
          this.items.push(addRoom.data);
        }
        return addRoom;
      } catch (error) {
        console.log(error.response?.data || {message: 'store room job failed'})
        return false;
      }
    },
    async deleteItem(item_id) {
      try {
        const deleted = await api.delete(`/v1/material/${item_id}`);
        if (deleted) {
          this.items = this.items.filter(pr => pr.id !== item_id);
        }
        return true;
      } catch (error) {
        console.log(error.response?.data || {message: 'delete material failed'})
        return false;
      }
    },
  },
});

// Поддержка HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useMaterialsStore, import.meta.hot));
}
