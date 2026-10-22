<template>
  <div class="text-h6">Шпатлевка</div>

  <div class="q-mt-md">
    <div class="text-subtitle1 text-weight-medium">Потолок</div>
    <q-checkbox v-model="counting.ceil" name="putty_counting_ceil" label="Считать потолок" />

    <div class="row q-col-gutter-md q-mt-xs">
      <q-input
        v-model.number="layers.ceil"
        class="col-12 col-sm-4"
        name="putty_ceil_layers"
        type="number"
        min="1"
        step="1"
        outlined
        dense
        label="Количество слоев"
      />
      <q-input
        v-model.number="squareAdjustments.ceil.inc"
        class="col-6 col-sm-4"
        name="putty_ceil_add_square_meters"
        type="number"
        min="0"
        step="0.01"
        outlined
        dense
        label="+ м.кв."
      />
      <q-input
        v-model.number="squareAdjustments.ceil.dec"
        class="col-6 col-sm-4"
        name="putty_ceil_remove_square_meters"
        type="number"
        min="0"
        step="0.01"
        outlined
        dense
        label="- м.кв."
      />
    </div>

    <div class="q-mt-sm text-body1">
      <div>
        Исходная площадь: <strong>{{ ceilingSquare }} м.кв.</strong>
      </div>
      <div>
        Расчетная площадь: <strong>{{ ceilingBillableSquare }} м.кв.</strong>
      </div>
      <div>
        Площадь с учетом слоев: <strong>{{ ceilingCoatedSquare }} м.кв.</strong>
      </div>
      <div>
        Стоимость работы: <strong>{{ ceilingJobSum.toLocaleString('ru-RU') }} ₽</strong>
      </div>
      <div>
        Шпатлевка: <strong>{{ ceilingPuttyBags }} меш.</strong>
        <span v-if="puttyMaterial"> на {{ ceilingMaterialSum.toLocaleString('ru-RU') }} ₽ </span>
      </div>
    </div>

    <q-btn
      class="q-mt-sm"
      color="primary"
      name="add_putty_materials_for_ceil"
      label="Добавить строительные материалы для потолка"
      :disable="!canAddCeilingMaterial"
      :loading="addingMaterial.ceil"
      @click="addMaterial('ceil')"
    />
  </div>

  <q-separator class="q-my-lg" />

  <div>
    <div class="text-subtitle1 text-weight-medium">Стены</div>
    <q-checkbox v-model="counting.walls" name="putty_counting_walls" label="Считать стены" />

    <div class="q-mt-xs text-body1">Выберите стены для расчета:</div>
    <div class="flex flex-wrap q-gutter-sm q-mt-xs">
      <q-checkbox
        v-for="item in realWalls"
        :key="item.index"
        v-model="selectedWallIndexes"
        :name="`putty_wall_${item.index}`"
        :val="item.index"
        :disable="!counting.walls"
        :label="`Стена ${item.index + 1}: ${formatNumber(item.lengthMeters)} м (${formatNumber(item.square)} м.кв.)`"
      />
    </div>

    <div class="row q-col-gutter-md q-mt-sm">
      <q-input
        v-model.number="layers.walls"
        class="col-12 col-sm-4"
        name="putty_walls_layers"
        type="number"
        min="1"
        step="1"
        outlined
        dense
        label="Количество слоев"
      />
      <q-input
        v-model.number="squareAdjustments.walls.inc"
        class="col-6 col-sm-4"
        name="putty_walls_add_square_meters"
        type="number"
        min="0"
        step="0.01"
        outlined
        dense
        label="+ м.кв."
      />
      <q-input
        v-model.number="squareAdjustments.walls.dec"
        class="col-6 col-sm-4"
        name="putty_walls_remove_square_meters"
        type="number"
        min="0"
        step="0.01"
        outlined
        dense
        label="- м.кв."
      />
    </div>

    <div class="q-mt-sm text-body1">
      <div>
        Площадь выбранных стен: <strong>{{ selectedWallsSquare }} м.кв.</strong>
      </div>
      <div>
        Расчетная площадь: <strong>{{ wallsBillableSquare }} м.кв.</strong>
      </div>
      <div>
        Площадь с учетом слоев: <strong>{{ wallsCoatedSquare }} м.кв.</strong>
      </div>
      <div>
        Стоимость работы: <strong>{{ wallsJobSum.toLocaleString('ru-RU') }} ₽</strong>
      </div>
      <div>
        Шпатлевка: <strong>{{ wallsPuttyBags }} меш.</strong>
        <span v-if="puttyMaterial"> на {{ wallsMaterialSum.toLocaleString('ru-RU') }} ₽ </span>
      </div>
    </div>

    <q-btn
      class="q-mt-sm"
      color="primary"
      name="add_putty_materials_for_walls"
      label="Добавить строительные материалы для стен"
      :disable="!canAddWallsMaterial"
      :loading="addingMaterial.walls"
      @click="addMaterial('walls')"
    />
  </div>

  <div v-if="materialsStore.loaded && !puttyMaterial" class="q-mt-md text-negative">
    Материал «Шпаклевка Сатентек 20 кг» не найден в каталоге.
  </div>

  <div class="q-mt-lg text-subtitle1 text-weight-medium">
    Общая стоимость работы: {{ totalJobSum.toLocaleString('ru-RU') }} ₽
  </div>
  <div class="flex justify-end q-mt-md">
    <q-btn
      color="primary"
      name="add_putty_job"
      label="Добавить работу"
      :disable="!canAddJob"
      :loading="addingJob"
      @click="addJobs"
    />
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { Notify } from 'quasar'
import { useMaterialsStore } from '@/stores/materials.js'
import { useRoomJobsStore } from '@/stores/roomJobs.js'
import { useRoomMaterialsStore } from '@/stores/roomMaterials.js'

const props = defineProps({
  roomId: {
    type: Number,
    required: true,
  },
  walls: {
    type: Array,
    default: () => [],
  },
  height: {
    type: Number,
    required: true,
  },
})

const JOB_PRICE_PER_SQUARE_METER = 200
const PUTTY_PRODUCT_CODE = 189676979
const PUTTY_BAG_WEIGHT = 20
const PUTTY_KG_PER_SQUARE_METER = 1

const materialsStore = useMaterialsStore()
const roomJobsStore = useRoomJobsStore()
const roomMaterialsStore = useRoomMaterialsStore()

const counting = reactive({ ceil: true, walls: true })
const layers = reactive({ ceil: 2, walls: 2 })
const squareAdjustments = reactive({
  ceil: { inc: 0, dec: 0 },
  walls: { inc: 0, dec: 0 },
})
const selectedWallIndexes = ref([])
const addingJob = ref(false)
const addingMaterial = reactive({ ceil: false, walls: false })

const nonNegativeNumber = (value) => {
  const number = Number(value)
  return Number.isFinite(number) && number > 0 ? number : 0
}

const normalizedLayers = (value) => {
  const number = Number(value)
  return Number.isFinite(number) ? Math.max(1, Math.round(number)) : 1
}

const formatNumber = (value) => Number(value).toLocaleString('ru-RU', { maximumFractionDigits: 2 })

const calculatePolygonSquare = (segments) => {
  const points = [{ x: 0, y: 0 }]
  let x = 0
  let y = 0

  segments.forEach((segment) => {
    const angle = (Number(segment.angle) || 0) * (Math.PI / 180)
    const length = nonNegativeNumber(segment.length) / 100
    x += length * Math.cos(angle)
    y += length * Math.sin(angle)
    points.push({ x, y })
  })

  let area = 0
  points.forEach((point, index) => {
    const nextPoint = points[(index + 1) % points.length]
    area += point.x * nextPoint.y - nextPoint.x * point.y
  })

  return Number((Math.abs(area) / 2).toFixed(2))
}

const roomHeight = computed(() => nonNegativeNumber(props.height))
const ceilingSquare = computed(() => calculatePolygonSquare(props.walls))
const realWalls = computed(() =>
  props.walls
    .map((wall, index) => {
      const lengthMeters = nonNegativeNumber(wall.length) / 100
      return {
        index,
        lengthMeters,
        square: Number((lengthMeters * roomHeight.value).toFixed(2)),
      }
    })
    .filter((_, index) => Number(props.walls[index]?.is_real) === 1),
)

const selectedWallsSquare = computed(() => {
  const selected = new Set(selectedWallIndexes.value)
  const square = realWalls.value.reduce(
    (sum, wall) => (selected.has(wall.index) ? sum + wall.square : sum),
    0,
  )
  return Number(square.toFixed(2))
})

const adjustedSquare = (baseSquare, adjustment) =>
  Math.max(
    0,
    nonNegativeNumber(baseSquare) +
      nonNegativeNumber(adjustment.inc) -
      nonNegativeNumber(adjustment.dec),
  )

const ceilingBillableSquare = computed(() =>
  Math.round(adjustedSquare(ceilingSquare.value, squareAdjustments.ceil)),
)
const wallsBillableSquare = computed(() =>
  Math.round(adjustedSquare(selectedWallsSquare.value, squareAdjustments.walls)),
)
const ceilingCoatedSquare = computed(
  () => ceilingBillableSquare.value * normalizedLayers(layers.ceil),
)
const wallsCoatedSquare = computed(() => wallsBillableSquare.value * normalizedLayers(layers.walls))
const ceilingJobSum = computed(() => ceilingCoatedSquare.value * JOB_PRICE_PER_SQUARE_METER)
const wallsJobSum = computed(() => wallsCoatedSquare.value * JOB_PRICE_PER_SQUARE_METER)
const totalJobSum = computed(
  () => (counting.ceil ? ceilingJobSum.value : 0) + (counting.walls ? wallsJobSum.value : 0),
)

const puttyMaterial = computed(() =>
  materialsStore.items.find((material) => Number(material.product_code) === PUTTY_PRODUCT_CODE),
)
const bagsFor = (coatedSquare) =>
  coatedSquare > 0 ? Math.ceil((coatedSquare * PUTTY_KG_PER_SQUARE_METER) / PUTTY_BAG_WEIGHT) : 0
const ceilingPuttyBags = computed(() => bagsFor(ceilingCoatedSquare.value))
const wallsPuttyBags = computed(() => bagsFor(wallsCoatedSquare.value))
const materialPrice = computed(() => Number(puttyMaterial.value?.price) || 0)
const ceilingMaterialSum = computed(() => ceilingPuttyBags.value * materialPrice.value)
const wallsMaterialSum = computed(() => wallsPuttyBags.value * materialPrice.value)

const canAddJob = computed(
  () =>
    (counting.ceil && ceilingCoatedSquare.value > 0) ||
    (counting.walls && wallsCoatedSquare.value > 0),
)
const canAddCeilingMaterial = computed(() =>
  Boolean(puttyMaterial.value && counting.ceil && ceilingPuttyBags.value > 0),
)
const canAddWallsMaterial = computed(() =>
  Boolean(puttyMaterial.value && counting.walls && wallsPuttyBags.value > 0),
)

const successfulResponse = (response) => response?.status >= 200 && response.status < 300
const jobPayload = (surface) => {
  const isCeiling = surface === 'ceil'
  const title = isCeiling ? 'Шпатлевка, потолок' : 'Шпатлевка, стены'
  const amount = isCeiling ? ceilingCoatedSquare.value : wallsCoatedSquare.value
  const sum = isCeiling ? ceilingJobSum.value : wallsJobSum.value

  return {
    room_id: props.roomId,
    title,
    sum,
    more_info: [
      {
        name: title,
        price: JOB_PRICE_PER_SQUARE_METER,
        amount,
        sum,
      },
    ],
  }
}

const addJobs = async () => {
  const surfaces = []
  if (counting.ceil && ceilingCoatedSquare.value > 0) surfaces.push('ceil')
  if (counting.walls && wallsCoatedSquare.value > 0) surfaces.push('walls')
  if (surfaces.length === 0) return

  addingJob.value = true
  try {
    const responses = await Promise.all(
      surfaces.map((surface) => roomJobsStore.addRoomJob(props.roomId, jobPayload(surface))),
    )
    const success = responses.every(successfulResponse)
    Notify.create({
      type: success ? 'positive' : 'negative',
      message: success
        ? 'Работа по шпатлевке добавлена!'
        : 'Не удалось добавить работу по шпатлевке.',
    })
  } finally {
    addingJob.value = false
  }
}

const addMaterial = async (surface) => {
  const material = puttyMaterial.value
  const bags = surface === 'ceil' ? ceilingPuttyBags.value : wallsPuttyBags.value
  const sum = surface === 'ceil' ? ceilingMaterialSum.value : wallsMaterialSum.value
  if (!material || bags < 1) return

  addingMaterial[surface] = true
  try {
    const response = await roomMaterialsStore.addItem(props.roomId, {
      room_id: props.roomId,
      material_id: material.id,
      amount: bags,
      sum,
    })
    const success = successfulResponse(response)
    Notify.create({
      type: success ? 'positive' : 'negative',
      message: success
        ? `Шпатлевка для ${surface === 'ceil' ? 'потолка' : 'стен'} добавлена!`
        : 'Не удалось добавить шпатлевку.',
    })
  } finally {
    addingMaterial[surface] = false
  }
}

watch(
  () => props.walls,
  () => {
    selectedWallIndexes.value = realWalls.value.map((wall) => wall.index)
  },
  { immediate: true, deep: true },
)
</script>
