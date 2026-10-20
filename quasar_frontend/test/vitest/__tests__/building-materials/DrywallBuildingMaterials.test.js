import { createTestingPinia } from '@pinia/testing'
import { flushPromises, mount, shallowMount } from '@vue/test-utils'
import { installQuasarPlugin } from '@quasar/quasar-app-extension-testing-unit-vitest'
import { Notify } from 'quasar'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import DrywallBuildingMaterials from '@/components/BuildingMaterialCalcs/DrywallBuildingMaterials/DrywallBuildingMaterials.vue'
import DrywallBuildingMaterialsForCeil from '@/components/BuildingMaterialCalcs/DrywallBuildingMaterials/DrywallBuildingMaterialsForCeil.vue'
import DrywallBuildingMaterialsForWalls from '@/components/BuildingMaterialCalcs/DrywallBuildingMaterials/DrywallBuildingMaterialsForWalls.vue'
import { useMaterialsStore } from '@/stores/materials.js'
import { useRoomMaterialsStore } from '@/stores/roomMaterials.js'

installQuasarPlugin()

vi.mock('quasar', async (importOriginal) => {
  const original = await importOriginal()

  return {
    ...original,
    Notify: {
      create: vi.fn(),
    },
  }
})

const roomWalls = [
  { length: 400, angle: 0, is_real: 1 },
  { length: 300, angle: 90, is_real: 1 },
  { length: 500, angle: 180, is_real: 0 },
]

const baseProps = {
  roomId: 31,
  perimeter: 10,
  ceilSquare: 12,
  wallsSquare: 27,
  walls: roomWalls,
  height: 2.7,
  isCountingCeil: false,
  isCountingWalls: true,
}

describe('DrywallBuildingMaterials', () => {
  it('renders the wall calculator only when wall counting is enabled', async () => {
    const wrapper = shallowMount(DrywallBuildingMaterials, {
      props: baseProps,
    })

    const wallCalculator = wrapper.findComponent(DrywallBuildingMaterialsForWalls)

    expect(wallCalculator.exists()).toBe(true)
    expect(wallCalculator.props()).toMatchObject({
      roomId: 31,
      perimeter: 10,
      wallsSquare: 27,
      walls: roomWalls,
      height: 2.7,
    })
    expect(wallCalculator.props()).not.toHaveProperty('ceilSquare')
    expect(wallCalculator.props()).not.toHaveProperty('isCountingWalls')

    await wrapper.setProps({ isCountingWalls: false })

    expect(wrapper.findComponent(DrywallBuildingMaterialsForWalls).exists()).toBe(false)
  })
})

describe('DrywallBuildingMaterialsForWalls', () => {
  let wrapper
  let pinia
  let materialsStore
  let roomMaterialsStore

  beforeEach(() => {
    vi.clearAllMocks()
    pinia = createTestingPinia({ createSpy: vi.fn })
    materialsStore = useMaterialsStore(pinia)
    roomMaterialsStore = useRoomMaterialsStore(pinia)

    materialsStore.items = [
      { id: 1, title: 'Лист гипсокартона', price: 500 },
      { id: 3, title: 'Профиль потолочный', price: 100 },
      { id: 4, title: 'Профиль направляющий', price: 100 },
      { id: 5, title: 'Подвес прямой', price: 10 },
      { id: 11, title: 'Саморез по дереву, 200 шт', price: 200 },
      { id: 6, title: 'Саморез по металлу, 1000 шт', price: 1000 },
      { id: 14, title: 'Саморез-семечка, 1000 шт', price: 1000 },
      { id: 15, title: 'Дюбель-гвоздь 4 см, 200 шт', price: 200 },
      { id: 16, title: 'Дюбель-гвоздь 6 см, 100 шт', price: 100 },
    ]
    materialsStore.loaded = true

    wrapper = mount(DrywallBuildingMaterialsForWalls, {
      props: {
        roomId: 31,
        perimeter: 10,
        wallsSquare: 27,
        walls: roomWalls,
        height: 2.7,
      },
      global: {
        plugins: [pinia],
      },
    })
  })

  it('calculates every configured wall material and its package-aware sum', () => {
    const materialInputs = wrapper.findAllComponents({ name: 'QInput' })

    expect(wrapper.text()).toContain('Шаги материалов')
    expect(wrapper.text()).not.toContain('Количество материалов')
    expect(wrapper.text()).not.toContain('Скотч малярный')
    expect(materialInputs).toHaveLength(9)
    expect(
      materialInputs.every((input) => input.find('input').attributes('type') === 'number'),
    ).toBe(true)
    expect(materialInputs.every((input) => input.find('input').attributes('step') === '0.01')).toBe(
      true,
    )
    expect(wrapper.vm.materialsInputs.profileMountedStep.value).toBe(0.6)
    expect(wrapper.vm.wallProfileLines).toBe(10)
    expect(wrapper.vm.rows.map((row) => row.id)).toEqual([1, 3, 4, 5, 6, 11, 14, 15, 16])
    expect(wrapper.vm.rows.map((row) => row.amount)).toEqual([9, 9, 9, 60, 129, 375, 290, 248, 70])
    expect(wrapper.vm.rows.map((row) => row.sum)).toEqual([
      4500, 900, 900, 600, 129, 375, 290, 248, 70,
    ])
    expect(wrapper.vm.allMaterialsSum).toBe(8012)
  })

  it('recalculates row sums and total after changing every material setting', async () => {
    const changes = [
      ['walls_step_wood', 0.1],
      ['walls_step_metal', 0.1],
      ['walls_step_dowel4', 0.2],
      ['walls_step_dowel6', 0.2],
      ['walls_runner_profile_length', 2],
      ['walls_runner_profile_joint_length', 1],
      ['walls_profile_length', 2],
      ['walls_profile_mounted_step', 0.3],
      ['walls_profile_suspensions_step', 1],
    ]

    for (const [name, value] of changes) {
      const previousTotal = wrapper.vm.allMaterialsSum
      await wrapper.find(`input[name="${name}"]`).setValue(value)

      expect(wrapper.vm.allMaterialsSum).not.toBe(previousTotal)
    }

    const woodRow = wrapper.vm.rows.find((row) => row.id === 11)
    expect(woodRow.price).toBe(200)
    expect(woodRow.amount).toBeGreaterThan(375)
    expect(woodRow.sum).toBeGreaterThan(375)
  })

  it('adds one calculated material to the room estimate', async () => {
    roomMaterialsStore.addItem.mockResolvedValue({ status: 201 })

    await wrapper.vm.addMaterial(wrapper.vm.rows.find((row) => row.id === 11))

    expect(roomMaterialsStore.addItem).toHaveBeenCalledWith(31, {
      room_id: 31,
      material_id: 11,
      amount: 375,
      sum: 375,
    })
    expect(Notify.create).toHaveBeenCalledWith({
      type: 'positive',
      message: 'Строительный материал добавлен!',
    })
  })

  it('shows an error notification when a material cannot be added', async () => {
    roomMaterialsStore.addItem.mockResolvedValue({ status: 422 })

    await wrapper.vm.addMaterial(wrapper.vm.rows[0])

    expect(Notify.create).toHaveBeenCalledWith({
      type: 'negative',
      message: 'Не удалось добавить строительные материалы.',
    })
  })

  it('adds all calculated wall materials to the room estimate', async () => {
    roomMaterialsStore.addItems.mockResolvedValue({ status: 201 })

    await wrapper.find('input[name="walls_step_wood"]').setValue(0.1)

    const addAllButton = wrapper
      .findAllComponents({ name: 'QBtn' })
      .find((button) => button.props('label') === 'Добавить все материалы (стены)')

    await addAllButton.trigger('click')
    await flushPromises()

    expect(roomMaterialsStore.addItems).toHaveBeenCalledWith(31, {
      materials: wrapper.vm.rows.map((row) => ({
        material_id: row.id,
        amount: row.amount,
        sum: row.sum,
      })),
    })
    expect(
      roomMaterialsStore.addItems.mock.calls[0][1].materials.find(
        (material) => material.material_id === 11,
      ).amount,
    ).toBe(510)
    expect(Notify.create).toHaveBeenCalledWith({
      type: 'positive',
      message: 'Все строительные материалы для стен добавлены!',
    })
  })
})

describe('DrywallBuildingMaterialsForCeil', () => {
  it('uses nine reactive numeric material inputs and rounds drywall sheets up', async () => {
    const pinia = createTestingPinia({ createSpy: vi.fn })
    const materialsStore = useMaterialsStore(pinia)
    const roomMaterialsStore = useRoomMaterialsStore(pinia)

    materialsStore.items = [
      { id: 1, title: 'Лист гипсокартона', price: 500 },
      { id: 3, title: 'Профиль потолочный', price: 100 },
      { id: 4, title: 'Профиль направляющий', price: 100 },
      { id: 5, title: 'Подвес прямой', price: 10 },
      { id: 6, title: 'Саморез по металлу, 1000 шт', price: 1000 },
      { id: 11, title: 'Саморез по дереву, 200 шт', price: 200 },
      { id: 14, title: 'Саморез-семечка, 1000 шт', price: 1000 },
      { id: 15, title: 'Дюбель-гвоздь 4 см, 200 шт', price: 200 },
      { id: 16, title: 'Дюбель-гвоздь 6 см, 100 шт', price: 100 },
    ]
    materialsStore.loaded = true

    const wrapper = mount(DrywallBuildingMaterialsForCeil, {
      props: {
        roomId: 31,
        perimeter: 10,
        ceilSquare: 7,
        walls: roomWalls,
      },
      global: {
        plugins: [pinia],
      },
    })
    const materialInputs = wrapper.findAllComponents({ name: 'QInput' })

    expect(materialInputs).toHaveLength(9)
    expect(
      materialInputs.every((input) => input.find('input').attributes('type') === 'number'),
    ).toBe(true)
    expect(materialInputs.every((input) => input.find('input').attributes('step') === '0.01')).toBe(
      true,
    )
    expect(wrapper.vm.materialsInputs.ceilProfileMountedStep.value).toBe(0.4)
    expect(wrapper.vm.rows.some((row) => row.id === 6)).toBe(true)
    expect(wrapper.vm.rows.some((row) => row.id === 16)).toBe(true)
    expect(wrapper.vm.rows.find((row) => row.id === 1)?.amount).toBe(3)
    expect(wrapper.text()).not.toContain('Id материалов')
    expect(wrapper.text()).not.toContain('Наибольшая длина стен')

    const previousTotal = wrapper.vm.allMaterialsSum
    const woodPrice = wrapper.vm.rows.find((row) => row.id === 11).price
    await wrapper.find('input[name="step_wood"]').setValue(0.1)

    expect(wrapper.vm.rows.find((row) => row.id === 11).price).toBe(woodPrice)
    expect(wrapper.vm.allMaterialsSum).not.toBe(previousTotal)

    await wrapper.find('input[name="step_ceilingRunnerProfileLength"]').setValue(0)
    await wrapper.find('input[name="step_ceilingProfileLength"]').setValue(0)

    expect(wrapper.vm.rows.every((row) => Number.isFinite(row.amount))).toBe(true)
    expect(wrapper.vm.rows.every((row) => row.amount >= 1)).toBe(true)

    roomMaterialsStore.addItems.mockResolvedValue({ status: 201 })

    const addAllButton = wrapper
      .findAllComponents({ name: 'QBtn' })
      .find((button) => button.props('label') === 'Добавить все материалы (потолок)')

    await addAllButton.trigger('click')
    await flushPromises()

    const payload = roomMaterialsStore.addItems.mock.calls[0][1]
    expect(roomMaterialsStore.addItems).toHaveBeenCalledWith(31, {
      materials: wrapper.vm.rows.map((row) => ({
        material_id: row.id,
        amount: row.amount,
        sum: row.sum,
      })),
    })
    expect(payload.materials.every((material) => !('room_id' in material))).toBe(true)
    expect(Notify.create).toHaveBeenCalledWith({
      type: 'positive',
      message: 'Все строительные материалы для потолка добавлены!',
    })
  })
})
