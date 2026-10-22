import { createTestingPinia } from '@pinia/testing'
import { flushPromises, mount } from '@vue/test-utils'
import { installQuasarPlugin } from '@quasar/quasar-app-extension-testing-unit-vitest'
import { Notify } from 'quasar'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import PuttyCalc from '@/components/JobTypesCalcs/PuttyCalc.vue'
import { useMaterialsStore } from '@/stores/materials.js'
import { useRoomJobsStore } from '@/stores/roomJobs.js'
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

const walls = [
  { length: 400, angle: 0, is_real: 1 },
  { length: 300, angle: 90, is_real: 1 },
  { length: 400, angle: 180, is_real: 0 },
  { length: 300, angle: 270, is_real: 1 },
]

describe('PuttyCalc', () => {
  let wrapper
  let materialsStore
  let roomJobsStore
  let roomMaterialsStore

  beforeEach(() => {
    vi.clearAllMocks()
    const pinia = createTestingPinia({ createSpy: vi.fn })
    materialsStore = useMaterialsStore(pinia)
    roomJobsStore = useRoomJobsStore(pinia)
    roomMaterialsStore = useRoomMaterialsStore(pinia)
    materialsStore.items = [
      {
        id: 37,
        product_code: 189676979,
        title: 'Шпаклевка Сатентек 20 кг',
        price: '875.00',
      },
    ]
    materialsStore.loaded = true

    wrapper = mount(PuttyCalc, {
      props: {
        roomId: 31,
        walls,
        height: 2.7,
      },
      global: {
        plugins: [pinia],
      },
    })
  })

  it('calculates two layers for the ceiling and every real wall by default', () => {
    expect(wrapper.vm.ceilingSquare).toBe(12)
    expect(wrapper.vm.realWalls.map((wall) => wall.index)).toEqual([0, 1, 3])
    expect(wrapper.vm.selectedWallIndexes).toEqual([0, 1, 3])
    expect(wrapper.vm.selectedWallsSquare).toBe(27)
    expect(wrapper.vm.layers).toMatchObject({ ceil: 2, walls: 2 })
    expect(wrapper.vm.ceilingCoatedSquare).toBe(24)
    expect(wrapper.vm.wallsCoatedSquare).toBe(54)
    expect(wrapper.vm.ceilingJobSum).toBe(4800)
    expect(wrapper.vm.wallsJobSum).toBe(10800)
    expect(wrapper.vm.ceilingPuttyBags).toBe(2)
    expect(wrapper.vm.wallsPuttyBags).toBe(3)
    expect(wrapper.vm.ceilingMaterialSum).toBe(1750)
    expect(wrapper.vm.wallsMaterialSum).toBe(2625)
    expect(wrapper.find('input[name="putty_wall_2"]').exists()).toBe(false)
  })

  it('recalculates selected walls, layers and area adjustments and clamps area at zero', async () => {
    wrapper.vm.selectedWallIndexes = [0]
    await wrapper.find('input[name="putty_walls_layers"]').setValue(3)
    await wrapper.find('input[name="putty_walls_add_square_meters"]').setValue(1.4)
    await wrapper.find('input[name="putty_walls_remove_square_meters"]').setValue(0.2)

    expect(wrapper.vm.selectedWallsSquare).toBe(10.8)
    expect(wrapper.vm.wallsBillableSquare).toBe(12)
    expect(wrapper.vm.wallsCoatedSquare).toBe(36)
    expect(wrapper.vm.wallsJobSum).toBe(7200)
    expect(wrapper.vm.wallsPuttyBags).toBe(2)

    await wrapper.find('input[name="putty_ceil_remove_square_meters"]').setValue(100)

    expect(wrapper.vm.ceilingBillableSquare).toBe(0)
    expect(wrapper.vm.ceilingJobSum).toBe(0)
    expect(wrapper.vm.ceilingPuttyBags).toBe(0)
  })

  it('adds separate ceiling and wall jobs with integer API payloads', async () => {
    roomJobsStore.addRoomJob.mockResolvedValue({ status: 201 })

    await wrapper.findComponent('[name="add_putty_job"]').trigger('click')
    await flushPromises()

    expect(roomJobsStore.addRoomJob).toHaveBeenCalledTimes(2)
    expect(roomJobsStore.addRoomJob).toHaveBeenNthCalledWith(1, 31, {
      room_id: 31,
      title: 'Шпатлевка, потолок',
      sum: 4800,
      more_info: [
        {
          name: 'Шпатлевка, потолок',
          price: 200,
          amount: 24,
          sum: 4800,
        },
      ],
    })
    expect(roomJobsStore.addRoomJob).toHaveBeenNthCalledWith(2, 31, {
      room_id: 31,
      title: 'Шпатлевка, стены',
      sum: 10800,
      more_info: [
        {
          name: 'Шпатлевка, стены',
          price: 200,
          amount: 54,
          sum: 10800,
        },
      ],
    })
    expect(Notify.create).toHaveBeenCalledWith({
      type: 'positive',
      message: 'Работа по шпатлевке добавлена!',
    })
  })

  it('does not add a disabled surface to the work estimate', async () => {
    roomJobsStore.addRoomJob.mockResolvedValue({ status: 201 })
    wrapper.vm.counting.walls = false

    await wrapper.vm.addJobs()

    expect(roomJobsStore.addRoomJob).toHaveBeenCalledTimes(1)
    expect(roomJobsStore.addRoomJob.mock.calls[0][1].title).toBe('Шпатлевка, потолок')
    expect(wrapper.vm.totalJobSum).toBe(4800)
  })

  it('adds the catalog putty separately for ceiling and walls', async () => {
    roomMaterialsStore.addItem.mockResolvedValue({ status: 201 })

    await wrapper.vm.addMaterial('ceil')
    await wrapper.vm.addMaterial('walls')

    expect(roomMaterialsStore.addItem).toHaveBeenNthCalledWith(1, 31, {
      room_id: 31,
      material_id: 37,
      amount: 2,
      sum: 1750,
    })
    expect(roomMaterialsStore.addItem).toHaveBeenNthCalledWith(2, 31, {
      room_id: 31,
      material_id: 37,
      amount: 3,
      sum: 2625,
    })
  })

  it('shows an error when putty cannot be added to the estimate', async () => {
    roomMaterialsStore.addItem.mockResolvedValue({ status: 422 })

    await wrapper.vm.addMaterial('ceil')

    expect(Notify.create).toHaveBeenCalledWith({
      type: 'negative',
      message: 'Не удалось добавить шпатлевку.',
    })
  })

  it('disables material actions when putty is absent from the catalog', async () => {
    materialsStore.items = []
    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('не найден в каталоге')
    expect(wrapper.vm.canAddCeilingMaterial).toBe(false)
    expect(wrapper.vm.canAddWallsMaterial).toBe(false)

    await wrapper.vm.addMaterial('ceil')
    expect(roomMaterialsStore.addItem).not.toHaveBeenCalled()
  })
})
