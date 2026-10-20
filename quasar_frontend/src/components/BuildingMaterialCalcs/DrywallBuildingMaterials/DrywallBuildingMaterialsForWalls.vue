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

  <drywall-material-inputs
    :inputs="materialsInputs"
    :fields="materialInputFields"
    @update-value="updateMaterialInput"
  />

  <drywall-materials-table
    :loaded="materialsStore.loaded"
    title="Таблица материалов к покупке (стены)"
    table-name="recommended_wall_materials"
    :rows="rows"
    :total="allMaterialsSum"
    add-all-label="Добавить все материалы (стены)"
    @add-material="addMaterial"
    @add-all="addAllMaterials"
  />
</template>

<script setup>
import { computed, ref } from 'vue'
import DrywallMaterialInputs from './DrywallMaterialInputs.vue'
import DrywallMaterialsTable from './DrywallMaterialsTable.vue'
import { amountByStep, mergeMaterialAmounts, positiveNumber } from './drywallMaterials.js'
import { useDrywallMaterials } from './useDrywallMaterials.js'

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
})

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

const materialInputFields = [
  { key: 'wood', label: 'Деревянные саморезы', name: 'walls_step_wood' },
  { key: 'metal', label: 'Металлические саморезы', name: 'walls_step_metal' },
  { key: 'dowel4', label: 'Дюбель-гвозди 4 см', name: 'walls_step_dowel4' },
  { key: 'dowel6', label: 'Дюбель-гвозди 6 см', name: 'walls_step_dowel6' },
  {
    key: 'runnerProfileLength',
    label: 'Длина направляющего профиля',
    name: 'walls_runner_profile_length',
  },
  {
    key: 'runnerProfileJointLength',
    label: 'Длина стыка направляющего профиля',
    name: 'walls_runner_profile_joint_length',
  },
  { key: 'profileLength', label: 'Длина профиля', name: 'walls_profile_length' },
  { key: 'profileMountedStep', label: 'Шаг профиля', name: 'walls_profile_mounted_step' },
  {
    key: 'profileSuspensionsStep',
    label: 'Шаг подвеса',
    name: 'walls_profile_suspensions_step',
  },
]

const updateMaterialInput = (key, value) => {
  materialsInputs.value[key].value = value
}

const perimeter = computed(() => Number(props.perimeter) || 0)
const wallsSquare = computed(() => Number(props.wallsSquare) || 0)
const roomHeight = computed(() => Number(props.height) || 0)
const realWalls = computed(() => props.walls.filter((wall) => Number(wall.is_real) === 1))

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
  return mergeMaterialAmounts(
    wallMaterialsForRunnerProfile.value,
    wallMaterialsForProfiles.value,
    { 1: drywallSheetsAmount.value },
  )
})

const { materialsStore, rows, allMaterialsSum, addMaterial, addAllMaterials } =
  useDrywallMaterials({
    roomId: computed(() => props.roomId),
    materialAmounts: wallMaterials,
    allAddedMessage: 'Все строительные материалы для стен добавлены!',
  })
</script>

<style scoped></style>
