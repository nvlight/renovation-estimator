import {defineStore, acceptHMRUpdate} from 'pinia';
//import axios from 'axios';
import {api} from '@/boot/axios.js'; // алиас @ сработал после правки jsconfig.json !

export const useProjectsStore = defineStore('projects', {
  state: () => ({
    projects: [],
    currentProjectId: null,
  }),

  // Геттеры
  getters: {
    currentProject: (state) => state.projects?.filter(t => t.id === state.currentProjectId) || [],
  },

  // Действия
  actions: {
    // Логин
    async loadProjects() {
      try {
        const response = await api.get('/v2/projects');

        const {data} = response.data;
        this.projects = data;

        return data;
      } catch (error) {
        throw error.response?.data || {message: 'load projects failed'};
      }
    },
    editProject(projectId) {
      console.log('edit: ', projectId);
    },
    deleteProject(projectId) {
      console.log('delete: ', projectId);
    },
  },
});

// Поддержка HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useProjectsStore, import.meta.hot));
}
