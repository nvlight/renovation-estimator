import { installQuasarPlugin } from '@quasar/quasar-app-extension-testing-unit-vitest';
import { createTestingPinia } from '@pinia/testing';
import { useRouter } from 'vue-router';
import { flushPromises, mount } from '@vue/test-utils';
import ProjectsPage from '@/pages/ProjectsPage.vue';
import { useProjectsStore } from '@/stores/projects';
import { useAuthStore } from '@/stores/auth';
import { QBtn, QDialog, QPagination, QForm, QInput } from 'quasar';
import { vi, describe, beforeEach, afterEach, it, expect } from 'vitest';

installQuasarPlugin();

vi.mock('vue-router', () => ({
  useRouter: vi.fn(() => ({
    push: vi.fn(),
  })),
}));

describe('ProjectsPage', () => {
  let wrapper = null;
  let pinia;
  let routerPush;

  beforeEach(() => {
    try {
      pinia = createTestingPinia();
      routerPush = vi.fn();
      vi.mocked(useRouter).mockReturnValue({ push: routerPush });

      wrapper = mount(ProjectsPage, {
        global: {
          plugins: [pinia],
          components: { QBtn, QDialog, QPagination, QForm, QInput },
        },
      });
    } catch (error) {
      console.error('Error in beforeEach:', error);
      throw error;
    }
  });

  afterEach(() => {
    if (wrapper && typeof wrapper.unmount === 'function') {
      wrapper.unmount();
      wrapper = null;
    }
    vi.clearAllMocks();
  });

  it('displays loading spinner when projects are not loaded and no error', async () => {
    const projectsStore = useProjectsStore(pinia);
    projectsStore.projects = [];
    expect(wrapper.find('.q-spinner').exists()).toBe(true);
    expect(wrapper.text()).toContain('loading ...');
  });

  it('displays error message when loadErrorMessage is set', async () => {
    wrapper.vm.loadErrorMessage = 'Failed to load projects';
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('Ошибка, попробуйте повторить позднее');
    expect(wrapper.find('.q-spinner').exists()).toBe(false);
  });

  it('renders project list when projects are loaded', async () => {
    const projectsStore = useProjectsStore(pinia);
    projectsStore.projects = [
      { id: 1, name: 'Project 1', description: 'Desc 1', created: '2025-06-01T12:00:00Z' },
      { id: 2, name: 'Project 2', description: '', created: '2025-06-02T12:00:00Z' },
    ];
    projectsStore.pagination = { currentPage: 1, lastPage: 2, perPage: 10 };
    await wrapper.vm.$nextTick();

    expect(wrapper.findAll('.q-item').length).toBe(2);
    expect(wrapper.text()).toContain('Project 1');
    expect(wrapper.text()).toContain('Project 2');
    expect(wrapper.text()).toContain('Desc 1');
    expect(wrapper.text()).toContain('No description');
  });

  it('opens add project dialog when Add Project button is clicked', async () => {
    const addButton = wrapper.findComponent(QBtn);
    expect(addButton.text()).toContain('Add Project');
    await addButton.trigger('click');
    expect(wrapper.vm.showAddProjectDialog).toBe(true);
  });

  it('calls addProject when submitting add project form', async () => {
    const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {});
    const projectsStore = useProjectsStore(pinia);
    projectsStore.addProject.mockResolvedValue({
      success: true,
      data: { id: 3, name: 'New Project', description: '', place_name: '' },
    });

    wrapper.vm.showAddProjectDialog = true;
    await wrapper.vm.$nextTick();

    // Находим диалог добавления и форму внутри него
    const addDialog = wrapper.findAllComponents(QDialog).find(dialog => dialog.props().modelValue === wrapper.vm.showAddProjectDialog);
    expect(addDialog.exists()).toBe(true);

    const form = addDialog.findComponent(QForm);
    expect(form.exists()).toBe(true);

    // Устанавливаем значение
    wrapper.vm.newProject.name = 'New Project';
    const nameInput = form.findComponent(QInput);
    expect(nameInput.exists()).toBe(true);
    await nameInput.setValue('New Project');
    await nameInput.trigger('input');
    await wrapper.vm.$nextTick();

    // Проверяем валидацию формы
    const formComponent = wrapper.vm.$refs.projectForm;
    const isValid = await formComponent.validate();
    expect(isValid).toBe(true);

    // Отправляем форму
    await form.trigger('submit');
    await wrapper.vm.$nextTick();
    await flushPromises();

    console.log('addProject mock result:', projectsStore.addProject.mock.results);

    expect(projectsStore.addProject).toHaveBeenCalledWith({
      name: 'New Project',
      description: '',
      place_name: '',
    });
    expect(wrapper.vm.showAddProjectDialog).toBe(false);
    //expect(wrapper.vm.createProjectErrorMessage).toBe('');

    consoleSpy.mockRestore();
  });

  it('opens edit project dialog when edit button is clicked', async () => {
    const projectsStore = useProjectsStore(pinia);
    projectsStore.projects = [
      { id: 1, name: 'Project 1', description: 'Desc 1', created: '2025-06-01T12:00:00Z' },
    ];
    await wrapper.vm.$nextTick();

    const editButton = wrapper.findAllComponents(QBtn).find(btn => btn.props().icon === 'edit');
    await editButton.trigger('click');

    expect(wrapper.vm.showEditProjectDialog).toBe(true);
    expect(wrapper.vm.editProjectData).toMatchObject({
      id: 1,
      name: 'Project 1',
      description: 'Desc 1',
    });
  });

  it('calls editProject when submitting edit project form', async () => {
    const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {});
    const projectsStore = useProjectsStore(pinia);
    projectsStore.editProject.mockResolvedValue({
      success: true,
      data: { id: 1, name: 'Updated Project', description: 'Updated Desc', place_name: '' },
    });

    wrapper.vm.showEditProjectDialog = true;
    wrapper.vm.editProjectData = {
      id: 1,
      name: 'Updated Project',
      description: 'Updated Desc',
      place_name: '',
    };
    await wrapper.vm.$nextTick();

    // Находим диалог редактирования и форму внутри него
    const editDialog = wrapper.findAllComponents(QDialog).find(dialog => dialog.props().modelValue === wrapper.vm.showEditProjectDialog);
    const form = editDialog.findComponent(QForm);

    expect(form.exists()).toBe(true);
    await form.trigger('submit');
    await wrapper.vm.$nextTick();
    await flushPromises();

    console.log('editProject mock result:', projectsStore.editProject.mock.results);

    expect(projectsStore.editProject).toHaveBeenCalledWith(1, {
      name: 'Updated Project',
      description: 'Updated Desc',
      place_name: '',
    });
    expect(wrapper.vm.showEditProjectDialog).toBe(false);
    //expect(wrapper.vm.updateProjectErrorMessage).toBe('');

    consoleSpy.mockRestore();
  });

  it('opens delete confirmation dialog when delete button is clicked', async () => {
    const projectsStore = useProjectsStore(pinia);
    projectsStore.projects = [
      { id: 1, name: 'Project 1', description: 'Desc 1', created: '2025-06-01T12:00:00Z' },
    ];
    await wrapper.vm.$nextTick();

    const deleteButton = wrapper.findAllComponents(QBtn).find(btn => btn.props().icon === 'delete');
    await deleteButton.trigger('click');

    expect(wrapper.vm.showDeleteConfirmDialog).toBe(true);
    expect(wrapper.vm.deleteProjectName).toBe('Project 1');
  });

  it('calls deleteProject when confirming deletion', async () => {
    const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {});
    const projectsStore = useProjectsStore(pinia);
    projectsStore.deleteProject.mockResolvedValue({
      success: true,
    });

    wrapper.vm.showDeleteConfirmDialog = true;
    wrapper.vm.deleteProjectId = 1;
    await wrapper.vm.$nextTick();

    const deleteButton = wrapper.findAllComponents(QBtn).find(btn => btn.text().toLowerCase().includes('delete'));
    await deleteButton.trigger('click');
    await wrapper.vm.$nextTick();
    await flushPromises();

    console.log('deleteProject mock result:', projectsStore.deleteProject.mock.results);

    expect(projectsStore.deleteProject).toHaveBeenCalledWith(1);
    expect(wrapper.vm.showDeleteConfirmDialog).toBe(false);
    //expect(wrapper.vm.deleteProjectErrorMessage).toBe('');

    consoleSpy.mockRestore();
  });

  it('initializes auth and loads projects on mount', () => {
    const authStore = useAuthStore(pinia);
    const projectsStore = useProjectsStore(pinia);

    expect(authStore.initializeAuth).toHaveBeenCalled();
    expect(projectsStore.loadProjects).toHaveBeenCalledWith(1);
  });
});
