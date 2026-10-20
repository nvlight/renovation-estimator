<template>
  <h6 class="q-mt-md q-mb-md">Потолок</h6>

  <div class="text-subtitle1 font-semibold">
    Периметр: <span class="text-weight-medium">{{ perimeter }}</span> м.
  </div>
  <div class="text-subtitle1 font-semibold">
    Площадь потолка: <span class="text-weight-medium">{{ ceilSquare }}</span> м.
  </div>
  <div class="text-subtitle1 font-semibold">
    Профиль потолочный направляющий:
    <span class="text-weight-medium">{{ ceilRunnerProfileCalc.amount }}</span> штук.
  </div>
  <div class="text-subtitle1 font-semibold">
    Профиль потолочный:
    <span class="text-weight-medium">{{ ceilProfileAmountCalc.amount }}</span> штук.
    ({{ ceilProfileAmountCalc.meters }} м.)
  </div>

  <drywall-material-inputs
    :inputs="materialsInputs"
    :fields="materialInputFields"
    @update-value="updateMaterialInput"
  />

  <drywall-materials-table
    :loaded="materialsStore.loaded"
    title="Таблица материалов к покупке (потолок)"
    table-name="recommended_ceiling_materials"
    :rows="rows"
    :total="allMaterialsSum"
    add-all-label="Добавить все материалы (потолок)"
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
  ceilSquare: {
    type: Number,
    required: false,
    default: 0,
  },
  walls: {
    type: Array,
    required: true,
    default: () => [],
  },
})

const materialsInputs = ref({
  wood: { id: 11, value: 0.2 },
  metal: { id: 6, value: 0.21 },
  dowel4: { id: 15, value: 0.4 },
  dowel6: { id: 16, value: 0.39 },
  ceilingRunnerProfileLength: { id: 4, value: 3 },
  ceilingRunnerProfileJointLength: { value: 0.2 },
  ceilingProfileLength: { id: 3, value: 3 },
  ceilProfileMountedStep: { value: 0.4 },
  ceilProfileSuspensionsStep: { id: 5, value: 0.5 },
})

const materialInputFields = [
  { key: 'wood', label: 'Деревянные саморезы', name: 'step_wood' },
  { key: 'metal', label: 'Металлические саморезы', name: 'step_metal' },
  { key: 'dowel4', label: 'Дюбель-гвозди 4 см', name: 'step_dowel4' },
  { key: 'dowel6', label: 'Дюбель-гвозди 6 см', name: 'step_dowel6' },
  {
    key: 'ceilingRunnerProfileLength',
    label: 'Длина направляющего профиля',
    name: 'step_ceilingRunnerProfileLength',
  },
  {
    key: 'ceilingRunnerProfileJointLength',
    label: 'Длина стыка направляющего профиля',
    name: 'step_ceilingRunnerProfileJointLength',
  },
  {
    key: 'ceilingProfileLength',
    label: 'Длина профиля',
    name: 'step_ceilingProfileLength',
  },
  {
    key: 'ceilProfileMountedStep',
    label: 'Шаг профиля',
    name: 'step_ceilProfileMountedStep',
  },
  {
    key: 'ceilProfileSuspensionsStep',
    label: 'Шаг подвеса',
    name: 'step_ceilProfileSuspensionsStep',
  },
]

const updateMaterialInput = (key, value) => {
  materialsInputs.value[key].value = value
}

const perimeter = computed(() => Number(props.perimeter) || 0)
const ceilSquare = computed(() => Number(props.ceilSquare) || 0)

const calculateRectangleDimensions = (vectors) => {
  let x = 0
  let y = 0
  let minX = 0
  let maxX = 0
  let minY = 0
  let maxY = 0

  vectors.forEach((vector) => {
    const angle = Number(vector.angle) || 0
    const length = positiveNumber(vector.length)
    const radians = (angle * Math.PI) / 180

    x += length * Math.cos(radians)
    y += length * Math.sin(radians)
    minX = Math.min(minX, x)
    maxX = Math.max(maxX, x)
    minY = Math.min(minY, y)
    maxY = Math.max(maxY, y)
  })

  const width = Math.round(Math.abs(maxX - minX) * 1000) / 1000
  const height = Math.round(Math.abs(maxY - minY) * 1000) / 1000

  return {
    maxLength: Math.floor(Math.max(width, height)),
    maxWidth: Math.floor(Math.min(width, height)),
  }
}

const maxCeilDimensions = computed(() => calculateRectangleDimensions(props.walls))
const maxCeilWidth = computed(() => maxCeilDimensions.value.maxWidth)
const maxCeilLength = computed(() => maxCeilDimensions.value.maxLength)

const ceilRunnerProfileCalc = computed(() => {
  const profileLength = positiveNumber(materialsInputs.value.ceilingRunnerProfileLength.value)
  const jointLength = positiveNumber(materialsInputs.value.ceilingRunnerProfileJointLength.value)

  if (!profileLength || !perimeter.value) {
    return { amount: 0, meters: 0 }
  }

  const amountWithoutJoints = perimeter.value / profileLength
  const jointsLength = Math.max(amountWithoutJoints - 1, 0) * jointLength
  const amount = Math.ceil(amountWithoutJoints + jointsLength / profileLength)

  return {
    amount,
    meters: amount * profileLength,
  }
})

const ceilProfileAmountCalc = computed(() => {
  const mountedStep = positiveNumber(materialsInputs.value.ceilProfileMountedStep.value)
  const profileLength = positiveNumber(materialsInputs.value.ceilingProfileLength.value)
  const max = maxCeilLength.value
  const min = maxCeilWidth.value

  if (!mountedStep || !profileLength || !max || !min) {
    return { meters: 0, amount: 0, maxWallsLength: max, maxWallsWidth: min, lines: 0 }
  }

  const lines = Math.ceil(max / 100 / mountedStep)
  const requiredMeters = (lines * min) / 100
  const amount = Math.ceil(requiredMeters / profileLength)

  return {
    meters: amount * profileLength,
    amount,
    maxWallsLength: max,
    maxWallsWidth: min,
    lines,
  }
})

const ceilSuspensionsAmount = computed(() => {
  const suspensionsStep = positiveNumber(materialsInputs.value.ceilProfileSuspensionsStep.value)

  if (!suspensionsStep || !ceilProfileAmountCalc.value.maxWallsWidth) {
    return 0
  }

  const suspensionsPerLine = Math.round(
    ceilProfileAmountCalc.value.maxWallsWidth / 100 / suspensionsStep,
  )

  return Math.max(suspensionsPerLine, 0) * ceilProfileAmountCalc.value.lines
})

const ceilSeedScrewsAmount = computed(() => {
  return ceilProfileAmountCalc.value.lines * 5 + ceilSuspensionsAmount.value * 4
})

const drywallSheetsAmount = computed(() => Math.ceil(ceilSquare.value / 3))

const ceilMaterialsForRunnerProfile = computed(() => {
  const runnerProfileMeters = ceilRunnerProfileCalc.value.meters

  return {
    15: amountByStep(runnerProfileMeters, materialsInputs.value.dowel4.value),
    16: amountByStep(runnerProfileMeters, materialsInputs.value.dowel6.value),
    11: amountByStep(runnerProfileMeters, materialsInputs.value.wood.value),
    6: amountByStep(runnerProfileMeters, materialsInputs.value.metal.value),
    4: ceilRunnerProfileCalc.value.amount,
  }
})

const ceilMaterialsForProfiles = computed(() => ({
  5: ceilSuspensionsAmount.value,
  15: ceilSuspensionsAmount.value * 3,
  11: ceilSuspensionsAmount.value * 4,
  14: ceilSeedScrewsAmount.value,
  3: ceilProfileAmountCalc.value.amount,
}))

const ceilMaterials = computed(() => {
  return mergeMaterialAmounts(
    ceilMaterialsForRunnerProfile.value,
    ceilMaterialsForProfiles.value,
    { 1: drywallSheetsAmount.value },
  )
})

const { materialsStore, rows, allMaterialsSum, addMaterial, addAllMaterials } =
  useDrywallMaterials({
    roomId: computed(() => props.roomId),
    materialAmounts: ceilMaterials,
    allAddedMessage: 'Все строительные материалы для потолка добавлены!',
  })
</script>

<style scoped></style>
