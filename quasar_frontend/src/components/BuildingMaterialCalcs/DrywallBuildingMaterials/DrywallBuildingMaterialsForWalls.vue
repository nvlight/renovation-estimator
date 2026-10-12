<template>
  <h6 class="q-mt-md q-mb-md">Стены</h6>

  <div class="text-subtitle1 font-semibold">
    Периметр: <span class="text-weight-medium">{{ perimeter }}</span> м.
  </div>
  <div class="text-subtitle1 font-semibold">
    Площадь стен: <span class="text-weight-medium">{{ wallsSquare }}</span> м.
  </div>
  <div class="text-subtitle1 font-semibold">
    Профиль потолочный направляющий:
    <span class="text-weight-medium">{{ wallRunnerProfileCalc.amount }}</span> штук.
  </div>
  <div class="text-subtitle1 font-semibold">
    Профиль потолочный:
    <span class="text-weight-medium">{{ wallProfileAmount }}</span> штук.
  </div>
  <div class="text-subtitle1 font-semibold">
    Листы гипсокартона:
    <span class="text-weight-medium">{{ drywallSheetsAmount }}</span> штук.
  </div>

  <div class="q-mt-md">
    <div>Шаги материалов</div>
    <div class="row" style="gap: 5px">
      <div>
        <q-input
          v-model.number="materialsInputs.wood.value"
          filled
          type="number"
          min="0"
          step="0.01"
          label="Деревянные саморезы"
          name="walls_step_wood"
        />
      </div>
      <div>
        <q-input
          v-model.number="materialsInputs.metal.value"
          filled
          type="number"
          min="0"
          step="0.01"
          label="Металлические саморезы"
          name="walls_step_metal"
        />
      </div>
      <div>
        <q-input
          v-model.number="materialsInputs.dowel4.value"
          filled
          type="number"
          min="0"
          step="0.01"
          label="Дюбель-гвозди 4 см"
          name="walls_step_dowel4"
        />
      </div>
      <div>
        <q-input
          v-model.number="materialsInputs.dowel6.value"
          filled
          type="number"
          min="0"
          step="0.01"
          label="Дюбель-гвозди 6 см"
          name="walls_step_dowel6"
        />
      </div>
      <div>
        <q-input
          v-model.number="materialsInputs.runnerProfileLength.value"
          filled
          type="number"
          min="0"
          step="0.01"
          label="Длина направляющего профиля"
          name="walls_runner_profile_length"
        />
      </div>
      <div>
        <q-input
          v-model.number="materialsInputs.runnerProfileJointLength.value"
          filled
          type="number"
          min="0"
          step="0.01"
          label="Длина стыка направляющего профиля"
          name="walls_runner_profile_joint_length"
        />
      </div>
      <div>
        <q-input
          v-model.number="materialsInputs.profileLength.value"
          filled
          type="number"
          min="0"
          step="0.01"
          label="Длина профиля"
          name="walls_profile_length"
        />
      </div>
      <div>
        <q-input
          v-model.number="materialsInputs.profileMountedStep.value"
          filled
          type="number"
          min="0"
          step="0.01"
          label="Шаг профиля"
          name="walls_profile_mounted_step"
        />
      </div>
      <div>
        <q-input
          v-model.number="materialsInputs.profileSuspensionsStep.value"
          filled
          type="number"
          min="0"
          step="0.01"
          label="Шаг подвеса"
          name="walls_profile_suspensions_step"
        />
      </div>
    </div>
  </div>

  <div v-if="materialsStore.loaded" class="q-mt-md">
    <div class="text-subtitle1 font-bold">Таблица материалов к покупке (стены)</div>
    <q-table
      name="recommended_wall_materials"
      title=""
      :rows="rows"
      :columns="columns"
      row-key="id"
      :pagination="{ rowsPerPage: 10 }"
    >
      <template #body-cell-actions="slotProps">
        <q-td :props="slotProps">
          <q-btn
            flat
            round
            size="sm"
            color="negative"
            icon="add"
            @click="addMaterial(slotProps.row)"
          />
        </q-td>
      </template>
    </q-table>

    <div class="q-mt-md text-subtitle1">
      Сумма всех материалов: <strong>{{ allMaterialsSum }}</strong> ₽
    </div>
    <div>
      <q-btn
        color="primary"
        label="Добавить все материалы (стены)"
        :disable="rows.length === 0"
        @click="addAllMaterials"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Notify } from 'quasar'
import { useMaterialsStore } from '@/stores/materials.js'
import { useRoomMaterialsStore } from '@/stores/roomMaterials.js'

const materialsStore = useMaterialsStore()
const roomMaterialsStore = useRoomMaterialsStore()

const props = defineProps({
  roomId: {
    type: Number,
    required: true,
  },
  perimeter: {
    type: Number,
    required: true,
  },
  wallsSquare: {
    type: Number,
    required: true,
  },
  walls: {
    type: Array,
    required: true,
    default: () => [],
  },
  height: {
    type: Number,
    required: true,
  },
  isCountingWalls: {
    type: Boolean,
    required: true,
  },
})

const columns = [
  { name: 'actions', label: '#', field: 'actions', sortable: false, align: 'left' },
  { name: 'id', label: 'id', field: 'id', sortable: true, align: 'left' },
  { name: 'title', label: 'Материал', field: 'title', sortable: true, align: 'left' },
  { name: 'amount', label: 'Количество', field: 'amount', sortable: false, align: 'right' },
  { name: 'price', label: 'Цена', field: 'price', sortable: false, align: 'right' },
  { name: 'sum', label: 'Сумма', field: 'sum', sortable: false, align: 'right' },
]

const materialsInputs = ref({
  wood: { id: 11, value: 0.2 },
  metal: { id: 6, value: 0.21 },
  dowel4: { id: 15, value: 0.4 },
  dowel6: { id: 16, value: 0.39 },
  runnerProfileLength: { id: 4, value: 3 },
  runnerProfileJointLength: { value: 0.2 },
  profileLength: { id: 3, value: 3 },
  profileMountedStep: { value: 0.6 },
  profileSuspensionsStep: { id: 5, value: 0.5 },
})

const perimeter = computed(() => Number(props.perimeter) || 0)
const wallsSquare = computed(() => Number(props.wallsSquare) || 0)
const roomHeight = computed(() => Number(props.height) || 0)
const realWalls = computed(() => props.walls.filter((wall) => Number(wall.is_real) === 1))

const positiveNumber = (value) => {
  const number = Number(value)
  return Number.isFinite(number) && number > 0 ? number : 0
}

const wallFramePerimeter = computed(() => {
  return realWalls.value.reduce((sum, wall) => {
    const wallLength = positiveNumber(wall.length) / 100

    if (!wallLength || !roomHeight.value) {
      return sum
    }

    return sum + 2 * (wallLength + roomHeight.value)
  }, 0)
})

const wallRunnerProfileCalc = computed(() => {
  const profileLength = positiveNumber(materialsInputs.value.runnerProfileLength.value)
  const jointLength = positiveNumber(materialsInputs.value.runnerProfileJointLength.value)

  if (!profileLength || !wallFramePerimeter.value) {
    return { amount: 0, meters: 0 }
  }

  const amountWithoutJoints = Math.ceil(wallFramePerimeter.value / profileLength)
  const jointsLength = Math.max(amountWithoutJoints - 1, 0) * jointLength

  const amount = Math.ceil((wallFramePerimeter.value + jointsLength) / profileLength)

  return {
    amount,
    meters: amount * profileLength,
  }
})

const wallProfileLines = computed(() => {
  const mountedStep = positiveNumber(materialsInputs.value.profileMountedStep.value)

  if (!mountedStep) {
    return 0
  }

  return realWalls.value.reduce((sum, wall) => {
    const wallLength = positiveNumber(wall.length) / 100
    const innerLines = Math.max(Math.ceil(wallLength / mountedStep) - 1, 0)
    return sum + innerLines
  }, 0)
})

const wallProfileMeters = computed(() => wallProfileLines.value * roomHeight.value)

const wallProfileAmount = computed(() => {
  const profileLength = positiveNumber(materialsInputs.value.profileLength.value)

  if (!profileLength) {
    return 0
  }

  return Math.ceil(wallProfileMeters.value / profileLength)
})

const wallSuspensionsAmount = computed(() => {
  const suspensionsStep = positiveNumber(materialsInputs.value.profileSuspensionsStep.value)

  if (!suspensionsStep || !roomHeight.value) {
    return 0
  }

  return wallProfileLines.value * Math.ceil(roomHeight.value / suspensionsStep)
})

const wallSeedScrewsAmount = computed(() => {
  return wallProfileLines.value * 5 + wallSuspensionsAmount.value * 4
})

const drywallSheetsAmount = computed(() => Math.ceil(wallsSquare.value / 3))

const amountByStep = (length, step) => {
  const normalizedStep = positiveNumber(step)

  if (!normalizedStep || !length) {
    return 0
  }

  return Math.ceil(length / normalizedStep)
}

const wallMaterialsForRunnerProfile = computed(() => {
  const runnerProfileMeters = wallRunnerProfileCalc.value.meters

  return {
    15: amountByStep(runnerProfileMeters, materialsInputs.value.dowel4.value),
    16: amountByStep(runnerProfileMeters, materialsInputs.value.dowel6.value),
    11: amountByStep(runnerProfileMeters, materialsInputs.value.wood.value),
    6: amountByStep(runnerProfileMeters, materialsInputs.value.metal.value),
    4: wallRunnerProfileCalc.value.amount,
  }
})

const wallMaterialsForProfiles = computed(() => ({
  5: wallSuspensionsAmount.value,
  15: wallSuspensionsAmount.value * 3,
  11: wallSuspensionsAmount.value * 4,
  14: wallSeedScrewsAmount.value,
  3: wallProfileAmount.value,
}))

const wallMaterials = computed(() => {
  const result = {}
  const materialGroups = [
    wallMaterialsForRunnerProfile.value,
    wallMaterialsForProfiles.value,
    { 1: drywallSheetsAmount.value },
  ]

  materialGroups.forEach((group) => {
    Object.entries(group).forEach(([id, amount]) => {
      result[id] = (result[id] || 0) + amount
    })
  })

  return result
})

const getMaterialTotalSum = (title, price, amount) => {
  const packageMatch = title.match(/(\d+(?:[.,]\d+)?)\s*шт/i)
  const quantityPerPackage = packageMatch ? Number.parseFloat(packageMatch[1].replace(',', '.')) : 1

  if (!Number.isFinite(quantityPerPackage) || quantityPerPackage <= 0) {
    return Math.ceil(amount * price)
  }

  return Math.ceil((amount / quantityPerPackage) * price)
}

const rows = computed(() => {
  return Object.entries(wallMaterials.value)
    .map(([id, amount]) => {
      const material = materialsStore.items.find((candidate) => candidate.id === Number(id))

      if (!material || amount < 1) {
        return null
      }

      return {
        id: material.id,
        title: material.title,
        amount,
        price: material.price,
        sum: getMaterialTotalSum(material.title, material.price, amount),
      }
    })
    .filter(Boolean)
})

const allMaterialsSum = computed(() => {
  return rows.value.reduce((sum, row) => sum + row.sum, 0)
})

const notifyAddError = () => {
  Notify.create({
    type: 'negative',
    message: 'Не удалось добавить строительные материалы.',
  })
}

const addMaterial = async (row) => {
  const response = await roomMaterialsStore.addItem(props.roomId, {
    room_id: props.roomId,
    material_id: row.id,
    amount: row.amount,
    sum: row.sum,
  })

  if (response?.status >= 200 && response.status < 300) {
    Notify.create({
      type: 'positive',
      message: 'Строительный материал добавлен!',
    })
    return
  }

  notifyAddError()
}

const addAllMaterials = async () => {
  const materials = rows.value.map((row) => ({
    material_id: row.id,
    amount: row.amount,
    sum: row.sum,
  }))

  if (materials.length === 0) {
    return
  }

  const response = await roomMaterialsStore.addItems(props.roomId, { materials })

  if (response?.status >= 200 && response.status < 300) {
    Notify.create({
      type: 'positive',
      message: 'Все строительные материалы для стен добавлены!',
    })
    return
  }

  notifyAddError()
}
</script>

<style scoped></style>
