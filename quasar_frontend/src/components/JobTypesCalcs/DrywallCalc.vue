<template>
  <div class="text-h6">Гипсокартон</div>

  <!-- Динамические стены --- поля по количеству walls -->
  <div class=" q-mt-sm">
    <div class="text-subtitle1">Стены</div>
    <div class="flex flex-wrap" style="gap: 5px;">
      <template v-for="(tmpWall, index) in etalonWalls" :key="index">
        <q-input
          :name="`stretch_wall_${index}`"
          style="max-width: 110px;"
          v-model.number="tmpWall.length"
          type="number"
          :label="`Стена ${index + 1}`"
          outlined
          dense
          :min="0"
        >
          <template #prepend>
            <q-icon name="straighten"/>
          </template>
        </q-input>
      </template>

      <q-btn @click="resetWalls" label="Сбросить"></q-btn>
    </div>

    <div class="q-mt-sm">
      <div class="text-subtitle1">Периметр: <span class="text-weight-medium">{{ perimeter }} м.кв.</span></div>
      <div class="text-subtitle1">Площадь стен (проемы не учитываются): <span class="text-weight-medium">{{
          wallsSquare
        }} м.кв.</span></div>
      <div class="text-subtitle1">Площадь стен (проемы учитываются): <span
        class="text-weight-medium">{{ improvedWallsSquare }} м.кв.</span></div>
      <div class="text-subtitle1">Площадь потолка: <span class="text-weight-medium">{{ ceilSquare }} м.кв.</span>
      </div>
      <!--      <div class="text-subtitle1">Площадь пола: <span class="text-weight-medium">{{ flooringSquare }} м.кв.</span></div>-->
      <div class="text-subtitle1">Площадь (потолок + стены): <span class="text-weight-medium">{{ ceilWithWallsSquare }} м.кв.</span>
      </div>

      <!--      <pre>{{ props.walls }}</pre>-->

      <div class="q-mt-md text-subtitle1">
        <q-checkbox v-model="counting.ceil" label="Считать потолок" name="counting_ceil"/>
        <q-checkbox v-model="counting.walls" label="Считать стены" name="counting_walls"/>
        <div class="flex items-center text-weight-medium" style="gap: 21px;">
          <div>Потолок, сумма: {{ ceilSquareSum.toLocaleString('ru-RU') }} ₽</div>
          <div class="flex items-center" style="gap: 10px;">
            <div>+</div>
            <q-input style="width: 45px;" v-model="incDecSquareMetersForCeil.inc" label="кв.м." name="ceilAddSquareMetersValue"/>
          </div>
          <div class="flex items-center" style="gap: 10px;">
            <div>-</div>
            <q-input style="width: 45px;" v-model="incDecSquareMetersForCeil.dec" label="кв.м." name="ceilRemoveSquareMetersValue"/>
          </div>
        </div>

        <div class="flex items-center text-weight-medium" style="gap: 21px;">
          <div>Стены, сумма: {{ wallsSquareSum.toLocaleString('ru-RU') }} ₽</div>
          <div class="flex items-center" style="gap: 10px;">
            <div>+</div>
            <q-input style="width: 45px;" v-model="incDecSquareMetersForWalls.inc" label="кв.м." name="wallsAddSquareMetersValue"/>
          </div>
          <div class="flex items-center" style="gap: 10px;">
            <div>-</div>
            <q-input style="width: 45px;" v-model="incDecSquareMetersForWalls.dec" label="кв.м." name="wallsRemoveSquareMetersValue"/>
          </div>
        </div>

        <div class="text-weight-medium">Итоговая сумма: {{ totalDrywallSum.toLocaleString('ru-RU') }} ₽</div>

      </div>

      <div class="flex justify-end q-mt-md">
        <q-btn @click="addJobs" color="primary" label="Добавить работу" :disable="!isAddJobBtnActive"/>
      </div>
    </div>
  </div>

  <div>
    <DrywallBuildingMaterials
      :roomId="roomId"
      :perimeter="perimeter"
      :ceilSquare="ceilSquare"
      :wallsSquare="improvedWallsSquare"
      :walls="etalonWalls"
    />
  </div>
</template>

<script setup>
import {computed, defineProps, onMounted, ref, watch} from 'vue'
import {useRoomJobsStore} from "@/stores/roomJobs.js";
import DrywallBuildingMaterials from "@/components/BuildingMaterialCalcs/DrywallBuildingMaterials/DrywallBuildingMaterials.vue";

const props = defineProps({
  roomId: {
    type: Number,
    required: true,
  },
  walls: {
    type: Array,
    required: false,
    default: () => [],
  },
  height: {
    type: Number,
    required: true,
  }
})

const roomJobsStore = useRoomJobsStore();
const roomId = ref(props.roomId);
const etalonWalls = ref([]);
const roomHeight = ref(props.height);

const pricePerSquareMeter = ref(600);
const counting = ref({
  ceil: true,
  walls: true,
});
/**
 * Периметр стен
 * @type {ComputedRef<number>}
 */
const perimeter = computed(() => {
  const ch = etalonWalls.value.reduce((prev, wall) => Number(wall.length + prev), 0);
  return +((ch / 100).toFixed(2));
});

/**
 * Площадь потолка
 * @type {ComputedRef<number>}
 */
const ceilSquare = computed(() => {
  return calculatePolygonSquare(etalonWalls.value);
});

/**
 * Сумма площади потолка
 * @type {ComputedRef<number>}
 */
const ceilSquareSum = computed(() => {
  let square = Math.round(ceilSquare.value) * pricePerSquareMeter.value;

  const incSum = squareSumBy(incDecSquareMetersForCeil.value.inc);
  const decSum = squareSumBy(incDecSquareMetersForCeil.value.dec);
  square += (incSum - decSum);

  if (square <= 0) {
    square = 0;
  }

  return +(Math.ceil(square).toFixed(2));
});

/**
 * Сумма площади стен
 * @type {ComputedRef<number>}
 */
const wallsSquareSum = computed(() => {
  let square = Math.round(improvedWallsSquare.value) * pricePerSquareMeter.value;

  const incSum = squareSumBy(incDecSquareMetersForWalls.value.inc);
  const decSum = squareSumBy(incDecSquareMetersForWalls.value.dec);
  square += (incSum - decSum);

  if (square <= 0) {
    square = 0;
  }

  return +(Math.round(square).toFixed(2));
});

/**
 * Площадь потолка + стен
 * @type {ComputedRef<unknown>}
 */
const ceilWithWallsSquare = computed(() => {
  return improvedWallsSquare.value + ceilSquare.value;
});

/**
 * Подсчет суммы работы по заданному кол-ву квадратных метров
 * @param oneSquareMeterPrice
 * @returns {number}
 */
const squareSumBy = (oneSquareMeterPrice) => {
  const square = Math.round(oneSquareMeterPrice) * pricePerSquareMeter.value;
  return +(Math.round(square).toFixed(2));
}

const incDecSquareMetersForCeil = ref({
  inc: 0,
  dec: 0,
});
const incDecSquareMetersForWalls = ref({
  inc: 0,
  dec: 0,
});

/**
 * Финальная сумма работы с учетом всех нюансов.
 * @type {ComputedRef<number>}
 */
const totalDrywallSum = computed(() => {
  let sum = 0;
  if (counting.value.ceil) {
    sum += ceilSquareSum.value;
  }
  if (counting.value.walls) {
    sum += wallsSquareSum.value;
  }
  return sum;
})

/**
 * Подсчет площади многоугольника по формуле Гаусса.
 * @param segments
 * @returns {number}
 */
const calculatePolygonSquare = (segments) => {
  // Строим точки вершин, начиная с (0, 0)
  const points = [{x: 0, y: 0}];
  let x = 0;
  let y = 0;

  for (let val of segments) {
    const angleRad = val.angle * Math.PI / 180;
    x += val.length / 100 * Math.cos(angleRad);
    y += val.length / 100 * Math.sin(angleRad);
    points.push({x, y});
  }

  // Формула шнурков (shoelace) для площади
  let area = 0;
  const n = points.length;
  for (let i = 0; i < n; i++) {
    const j = (i + 1) % n;
    area += points[i].x * points[j].y;
    area -= points[j].x * points[i].y;
  }

  return +(Math.abs(area) / 2).toFixed(2);
}

/**
 * Подсчитывает площадь стен. Проемы не учитываются
 * @type {ComputedRef<number>}
 */
const wallsSquare = computed(() => {
  return +((perimeter.value * roomHeight.value).toFixed(2));
})

/**
 * Подсчитывает площь стен. Стены, которые являеются проемами не считаются (параметр is_real)
 * @param segments
 * @param h
 * @returns {number}
 */
const improvedWallsSquare = computed(() => {
  let totalLength = 0;

  for (let seg of etalonWalls.value) {
    if (seg.is_real === 1) {
      totalLength += seg.length / 100; // переводим см в метры
    }
  }

  return totalLength * roomHeight.value;
});

// {"sum": 9600, "name": "Потолок + установка", "price": 600, "amount": 16},
const computedStoreInfoForCeil = computed(() => {
  return {
    room_id: roomId.value,
    sum: ceilSquareSum.value,
    title: 'Гипсокартон, потолок',
    more_info: [{
      "name": "Гипсокартон, потолок",
      "price": pricePerSquareMeter.value,
      "amount": Math.round(ceilSquare.value),
      "sum": ceilSquareSum.value,
    }],
  }
});

// {"sum": 9600, "name": "Потолок + установка", "price": 600, "amount": 16},
const computedStoreInfoForWalls = computed(() => {
  return {
    room_id: roomId.value,
    sum: wallsSquareSum.value,
    title: 'Гипсокартон, стены',
    more_info: [{
      "name": "Гипсокартон, стены",
      "price": pricePerSquareMeter.value,
      "amount": Math.round(improvedWallsSquare.value),
      "sum": wallsSquareSum.value,
    }],
  }
});

const isAddJobBtnActive = computed(() => {
  return counting.value.ceil || counting.value.walls;
});

/**
 * Добавление работ по гипсокартону (потолок, стены)
 * @returns {Promise<void>}
 */
const addJobs = async () => {
  if (counting.value.ceil) {
    await roomJobsStore.addRoomJob(roomId.value, computedStoreInfoForCeil.value);
  }
  if (counting.value.walls) {
   await roomJobsStore.addRoomJob(roomId.value, computedStoreInfoForWalls.value);
  }
};

const resetWalls = () => {
  etalonWalls.value = JSON.parse(JSON.stringify(props.walls));
}

watch(
  () => props.walls,
  (newVal) => {
    if (newVal?.length) {
      etalonWalls.value = JSON.parse(JSON.stringify(newVal));
    }
  },
  {immediate: true}
);
watch(
  () => props.height,
  (nv) => {
    roomHeight.value = nv;
  },
  {immediate: true}
);

onMounted(() => {
});

</script>

<style scoped>
</style>
