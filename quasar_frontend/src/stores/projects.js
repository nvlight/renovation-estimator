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
        const response = await api.get('/v1/projects');

        const {data} = response.data;
        this.projects = data;

        return data;
      } catch (error) {
        throw error.response?.data || {message: 'load projects failed'};
      }
    },
    async editProject(projectId, projectData) {
      try {
        const response = await api.patch(`/v1/projects/${projectId}`, projectData);
        this.projects = this.projects.map(project =>
          project.id === projectId ? { ...project, ...response.data } : project
        );
        return response;
      } catch (error) {
        throw error.response?.data || { message: 'Failed to update project' };
      }
    },
    async deleteProject(projectId) {
      try {
        const deleted = await api.delete('/v1/projects/' + projectId);
        if (deleted) {
          this.projects = this.projects.filter(pr => pr.id !== projectId);
        }
        return true;
      } catch (error) {
        throw error.response?.data || {message: 'load projects failed'};
      }
    },
    async addProject(projectData) {
      try {
        const addProject = await api.post('/v1/projects/', projectData);
        if (addProject) {
          this.projects.unshift(addProject.data);
        }
        return addProject;
      } catch (error) {
        throw error.response || {message: 'add projects failed'};
      }
    },

  },
});

// Поддержка HMR (Hot Module Replacement)
if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useProjectsStore, import.meta.hot));
}
