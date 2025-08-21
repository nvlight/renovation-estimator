import {installQuasarPlugin} from '@quasar/quasar-app-extension-testing-unit-vitest';
import {createTestingPinia} from '@pinia/testing';
import {useRouter, useRoute} from 'vue-router';
import {flushPromises, mount} from '@vue/test-utils';
import RoomsPage from '@/pages/RoomsPage.vue';
import {useRoomsStore} from '@/stores/rooms';
import {useAuthStore} from '@/stores/auth';
import {QBtn, QDialog, QPagination, QForm, QInput} from 'quasar';
import {vi, describe, beforeEach, afterEach, it, expect} from 'vitest';

installQuasarPlugin();

vi.mock('vue-router', () => ({
  // useRouter: vi.fn(() => ({
  //   push: vi.fn(),
  // })),
  useRoute: vi.fn(() => ({
    params: {projectId: '123'}, // Мокаем объект маршрута, можно добавить нужные свойства
    query: {},
    path: 'projects/:projectId/rooms', // Укажите путь, если он используется в компоненте
  })),
}));

describe('ProjectRoomsPage', () => {
  let wrapper = null;
  let pinia;
  //let routerPush;

  beforeEach(() => {
    try {
      pinia = createTestingPinia();

      //routerPush = vi.fn();
      //vi.mocked(useRouter).mockReturnValue({ push: routerPush });

      wrapper = mount(RoomsPage, {
        global: {
          plugins: [pinia],
          components: {QBtn, QDialog, QPagination, QForm, QInput},
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

  it('displays loading spinner when rooms are not loaded and no error', async () => {
    const roomsStore = useRoomsStore(pinia);
    roomsStore.rooms = [];

    // Устанавливаем itemsLoading в true
    wrapper.vm.itemsLoading = true;

    // Убедимся, что loadErrorMessage пустое
    wrapper.vm.loadErrorMessage = '';

    // Дожидаемся асинхронных операций
    await flushPromises();

    expect(wrapper.find('.q-spinner').exists()).toBe(true);
    expect(wrapper.text()).toContain('Загрузка ...');
  });

  it('displays error message when loadErrorMessage is set', async () => {
    const roomsStore = useRoomsStore(pinia);
    roomsStore.rooms = [];

    wrapper.vm.itemsLoading = true;
    wrapper.vm.loadErrorMessage = true;

    await wrapper.vm.$nextTick();
    //await flushPromises();

    expect(wrapper.text()).toContain('Ошибка, попробуйте повторить позднее');
    expect(wrapper.find('.q-spinner').exists()).toBe(false);
  });

  it('renders project list when projects are loaded', async () => {
    const roomsStore = useRoomsStore(pinia);
    roomsStore.rooms = [
      {
        'id': 53,
        'room_id': 78,
        'name': "Non aliquid hic.",
        'description': "Super cool description - yeah!",
        'height': "2.86",
        'updated_at': "2025-08-20T14:41:42.000000Z",
      }
    ];

    roomsStore.pagination = {currentPage: 1, lastPage: 2, perPage: 10};
    await wrapper.vm.$nextTick();

    expect(wrapper.findAll('.q-item').length).toBe(1);
    expect(wrapper.text()).toContain('Non aliquid hic.');
    expect(wrapper.text()).toContain('Super cool description - yeah!');
    expect(wrapper.text()).toContain('2.86');
  });

  it('opens add room dialog when Add Room button is clicked', async () => {
    const addButton = wrapper.findComponent(QBtn);
    expect(addButton.text()).toContain('добавить комнату');
    await addButton.trigger('click');
    expect(wrapper.vm.showAddItemDialog).toBe(true);
  });

  it('calls addRoom when submitting add room form', async () => {
    const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {
    });
    const roomsStore = useRoomsStore(pinia);
    roomsStore.addRoom.mockResolvedValue({
      success: true,
      data: {id: 3, name: 'New Room', description: 'SC desc', height: '3'},
    });

    wrapper.vm.showAddItemDialog = true;
    await wrapper.vm.$nextTick();

    // Находим диалог добавления и форму внутри него
    const addDialog = wrapper.findAllComponents(QDialog).find(dialog => dialog.props().modelValue === wrapper.vm.showAddItemDialog);
    expect(addDialog.exists()).toBe(true);

    const form = addDialog.findComponent(QForm);
    expect(form.exists()).toBe(true);

    // Устанавливаем значение
    wrapper.vm.newItem.name = 'New Room';
    wrapper.vm.newItem.height = '3';

    const nameInput = form.findComponent(QInput);
    expect(nameInput.exists()).toBe(true);
    await nameInput.setValue('New Room');
    await nameInput.trigger('input');
    //await nameInput.setValue('New Room');
    //await nameInput.trigger('input');
    await wrapper.vm.$nextTick();

    // Проверяем валидацию формы
    const formComponent = wrapper.vm.$refs.itemForm;
    const isValid = await formComponent.validate();
    expect(isValid).toBe(true);

    // Отправляем форму
    await form.trigger('submit');
    //await wrapper.vm.$nextTick();
    await flushPromises();

    console.log('addRoom mock result:', roomsStore.addRoom.mock.results);

    expect(roomsStore.addRoom).toHaveBeenCalledWith({
      name: 'New Room',
      description: '',
      height: '3',
      project_id: "123"
    });
    expect(wrapper.vm.showAddItemDialog).toBe(false);
    //expect(wrapper.vm.createProjectErrorMessage).toBe('');

    consoleSpy.mockRestore();
  });

  it('opens edit room dialog when edit button is clicked', async () => {
    const roomsStore = useRoomsStore(pinia);
    roomsStore.rooms = [
      {id: 5, name: 'New Room', description: '', height: '3', project_id: "123"},
    ];
    await wrapper.vm.$nextTick();
    //await flushPromises();

    const editButton = wrapper.findAllComponents(QBtn).find(btn => btn.props().icon === 'edit');
    await editButton.trigger('click');

    expect(wrapper.vm.showEditItemDialog).toBe(true);
    expect(wrapper.vm.editItemData).toMatchObject({
      id: 5,
      name: 'New Room',
      description: '',
      height: '3',
      //project_id: "123"
    });
  });

  it('calls editItem when submitting edit item form', async () => {
    const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {});
    const roomsStore = useRoomsStore(pinia);
    roomsStore.editRoom.mockResolvedValue({
      success: true,
      data: { id: 1, name: 'Updated Project', description: 'Updated Desc', place_name: '' },
    });

    wrapper.vm.showEditItemDialog = true;
    wrapper.vm.editItemData = {
      id: 1,
      name: 'Updated Room',
      description: 'Updated Desc',
      height: '3',
    };
    await wrapper.vm.$nextTick();

    // Находим диалог редактирования и форму внутри него
    const editDialog = wrapper.findAllComponents(QDialog).find(dialog => dialog.props().modelValue === wrapper.vm.showEditItemDialog);
    const form = editDialog.findComponent(QForm);

    expect(form.exists()).toBe(true);
    await form.trigger('submit');
    //await wrapper.vm.$nextTick();
    await flushPromises();

    console.log('editProject mock result:', roomsStore.editRoom.mock.results);

    expect(roomsStore.editRoom).toHaveBeenCalledWith("123", {
      name: 'Updated Room',
      description: 'Updated Desc',
      height: '3',
      id: 1,
    });
    expect(wrapper.vm.showEditItemDialog).toBe(false);
    //expect(wrapper.vm.updateProjectErrorMessage).toBe('');

    consoleSpy.mockRestore();
  });

  it('opens delete confirmation dialog when delete button is clicked', async () => {
    const roomsStore = useRoomsStore(pinia);
    roomsStore.rooms = [
      { id: 1, name: 'Room 1', description: 'Desc 1', created: '2025-06-01T12:00:00Z' },
    ];
    await wrapper.vm.$nextTick();

    const deleteButton = wrapper.findAllComponents(QBtn).find(btn => btn.props().icon === 'delete');
    await deleteButton.trigger('click');

    expect(wrapper.vm.showDeleteItemConfirmDialog).toBe(true);
    expect(wrapper.vm.deleteItemName).toBe('Room 1');
  });

  it('calls deleteItem when confirming deletion', async () => {
    const consoleSpy = vi.spyOn(console, 'log').mockImplementation(() => {});
    const roomsStore = useRoomsStore(pinia);
    roomsStore.deleteRoom.mockResolvedValue({
      success: true,
    });

    wrapper.vm.showDeleteItemConfirmDialog = true;
    wrapper.vm.deleteItemId = 1;
    await wrapper.vm.$nextTick();

    const deleteButton = wrapper.findAllComponents(QBtn).find(btn => btn.text().toLowerCase().includes('delete'));
    await deleteButton.trigger('click');
    await wrapper.vm.$nextTick();
    await flushPromises();

    console.log('deleteRoom mock result:', roomsStore.deleteRoom.mock.results);

    expect(roomsStore.deleteRoom).toHaveBeenCalledWith("123", 1);
    expect(wrapper.vm.showDeleteItemConfirmDialog).toBe(false);
    //expect(wrapper.vm.deleteProjectErrorMessage).toBe('');

    consoleSpy.mockRestore();
  });

  it('initializes auth and loads projects on mount', () => {
    const authStore = useAuthStore(pinia);
    const roomsStore = useRoomsStore(pinia);

    expect(authStore.initializeAuth).toHaveBeenCalled();
    expect(roomsStore.loadRooms).toHaveBeenCalledWith('123', 1);
  });
});
