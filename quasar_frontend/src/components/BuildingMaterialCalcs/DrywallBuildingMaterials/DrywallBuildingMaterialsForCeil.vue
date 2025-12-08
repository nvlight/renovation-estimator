<template>
  <h6 class="q-mt-md q-mb-md">Потолок</h6>

  <div class="text-subtitle1 font-semibold">Периметр: <span class="text-weight-medium">{{ perimeter }}</span> м.</div>
  <div class="text-subtitle1 font-semibold">Площадь потолка: <span class="text-weight-medium">{{ ceilSquare }}</span> м.</div>

  <div class="q-mt-md">
    <div class="text-subtitle1 font-semibold">
      Профиль потолочный направляющий:
      <span class="text-weight-medium">{{ ceilRunnerProfileAmount }}</span> штук.
    </div>
    <div class="text-subtitle1 font-semibold">Id материалов: {{ ceilMaterials}}</div>
    <div class="text-subtitle1 font-semibold">Максимальная длина стены: {{ maxCeilLength}}</div>
    <div class="text-subtitle1 font-semibold">Максимальная ширина стены: {{maxCeilWidth}}</div>
<!--    <div>{{ walls }}</div>-->
    <div class="text-subtitle1 font-semibold">
      Профиль потолочный:
      <span class="text-weight-medium">{{ ceilProfileAmountCalc.amount }}</span> штук. ({{ ceilProfileAmountCalc.meters }} м.)
    </div>

    <div class="q-mt-md">Шаги материалов</div>
    <div class="row" style="gap: 5px;">
      <div>
        <q-input filled v-model="fastenerStepInputs.wood" label="деревянные саморезы"/>
      </div>
      <div>
        <q-input filled v-model="fastenerStepInputs.metal" label="Металлические саморезы"/>
      </div>
      <div>
        <q-input filled v-model="fastenerStepInputs.dowel4" label="Дюбель гвозди 4 см"/>
      </div>
      <div>
        <q-input filled v-model="fastenerStepInputs.dowel6" label="Дюбель гвозди 6 см"/>
      </div>
      <div>
        <q-input filled v-model="ceilingRunnerProfileLength" label="Длина направляющего профиля"/>
      </div>
      <div>
        <q-input filled v-model="ceilingRunnerProfileJointLength" label="Длина стыка направляющего профиля"/>
      </div>
      <div>
        <q-input filled v-model="ceilingProfileLength" label="Длина профиля"/>
      </div>
      <div>
        <q-input filled v-model="ceilProfileMountedStep" label="Шаг профиля"/>
      </div>
      <div>
        <q-input filled v-model="ceilProfileSuspensionsStep" label="Шаг подвеса"/>
      </div>

    </div>

  </div>

  <!-- Список рекомендуемых, подсчитанных материалов. -->
  <div v-if="materialsStore.loaded" class="q-mt-md">
    <div class="text-subtitle1 font-bold">Рекомендуемые материалы к покупке</div>
    <q-table
      title=""
      :rows="rows"
      :columns="columns"
      row-key="name"
      :pagination="{ rowsPerPage: 10 }"
    >
      <!-- Ячейка с кнопкой удаления -->
      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <q-btn
            flat
            round
            size="sm"
            color="negative"
            icon="delete"
            @click="deleteMaterial()"
          />
        </q-td>
      </template>
    </q-table>
  </div>

  <!--  <pre>calcedFastenerAmount: {{ calcedFastenerAmount }}</pre>-->
</template>

<script setup>
import {computed, onMounted, ref, watch} from "vue";
import {useMaterialsStore} from "@/stores/materials.js";

const materialsStore = useMaterialsStore();

const props = defineProps({
  perimeter: {
    type: Number,
    required: true,
  },
  ceilSquare: {
    type: Number,
    required: false,
  },
  walls: {
    type: Array,
    required: true,
    default: () => [],
  }
});

const ceilSquare = ref(props.ceilSquare);

const columns = [
  {name: 'id', label: 'id', field: 'id', sortable: true, align: 'left'},
  {name: 'title', label: 'Материал', field: 'title', sortable: true, align: 'left'},  // Editable
  {name: 'value', label: 'значение', field: 'value', sortable: false, align: 'right'},  // Computed
  {name: 'amount', label: 'количество', field: 'amount', sortable: false, align: 'right'},  // Computed
];

const rows = computed(() => {
  return calcedFastenerAmount.value.map(item => {
    return {
      id: item.id,
      title: item.title,
      value: item.value,
      amount: item.amount,
    }
  })
});

const fastenerStepInputs = ref({
  'wood': 0.2,
  'metal': 0.21,
  'dowel4': 0.4,
  'dowel6': 0.39,
});

const perimeter = ref(props.perimeter);

// 3  - Профиль потолочный КМ Стандарт 47х17 мм 3 м 0,50 мм
// 4  - Профиль потолочный направляющий КМ Стандарт 17х20 мм 3 м 0,50 мм
// 11 - саморез, дерево, 4 см.
//  6 - саморез, металл, 3.5 см.
// 5  - Подвес прямой
// 14 - семечка, саморез, 1 см.
// 15 - дюбель гвоздь, 4 см.
// 16 - дюбель гвоздь, 6 см.
// 28 - скотч, клейкая лента.

const needFastenerSteps = computed(() => [
  {
    id: 6,
    type: 'fastener',
    value: fastenerStepInputs.value.metal,
  },
  {
    id: 11,
    type: 'fastener',
    value: fastenerStepInputs.value.wood,
  },
  {
    id: 15,
    type: 'fastener',
    value: fastenerStepInputs.value.dowel4,
  },
  {
    id: 16,
    type: 'fastener',
    value: fastenerStepInputs.value.dowel6,
  },
]);
const calcedFastenerAmount = computed(() => {
  return needFastenerSteps.value.map(item => {
    return {
      id: item.id,
      title: materialsStore.loaded
        ? materialsStore.items.find(i => i.id === item.id)?.title || '-'
        : 'title',
      value: +item.value,
      amount: item.type === 'fastener' ? Math.ceil(perimeter.value / item.value) : +item.value,
    }
  })
});

/*
    #1 подсчет профиля потолочного направляющего.
    Сколько потребуется штук, также сколько потребуется для него дюбелей, саморезов и тд.
 */
const ceilingRunnerProfileLength = ref(3);
const ceilingRunnerProfileJointLength = ref(0.2);

/**
 * Профиль потолочный направляющий - подсчет
 * @type {ComputedRef<number>}
 */
const ceilRunnerProfileAmount = computed( () => {
  const amount = perimeter.value / ceilingRunnerProfileLength.value;

  // теперь нужно добавить стыки, каждый стык имеет длину, например 20 см.
  const jointLength = (amount - 1) * ceilingRunnerProfileJointLength.value;
  const jointAmount = jointLength / ceilingRunnerProfileLength.value;

  return +(Math.ceil(amount + jointAmount).toFixed(2));
});

// ^ количество и тип материалов к покупке
// материалы для направляющего профиля
const ceilMaterials1 = computed( () => {
  return {
    15: Math.ceil((ceilRunnerProfileAmount.value * ceilingRunnerProfileLength.value) / fastenerStepInputs.value.dowel4),
    11: Math.ceil((ceilRunnerProfileAmount.value * ceilingRunnerProfileLength.value) / fastenerStepInputs.value.wood),
  }
});
// материалы для профиля потолочного.
const ceilMaterials2 = computed( () => {
  return {
    5: ceilProfileAmountCalc.value.suspencionsAmount,
    15: ceilProfileAmountCalc.value.suspencionsAmount * 3, // по 3 дюбеля на 1 подвес
    11: ceilProfileAmountCalc.value.suspencionsAmount * 4, // по 4 самореза на 1 подвес,
    14: ceilProfileAmountCalc.value.seedScrews,
  }
});

/**
 * Суммированный объект со всеми материалами
 * @type {ComputedRef<{}>}
 */
const ceilMaterials = computed(() => {
  const materials1 = ceilMaterials1.value; // {15: x, 11: y}
  const materials2 = ceilMaterials2.value; // {5: a, 15: b, 11: c, 14: d}

  // Создаем новый объект для результата
  const result = {};

  // Собираем все уникальные ключи из обоих объектов
  const allKeys = new Set([
    ...Object.keys(materials1),
    ...Object.keys(materials2)
  ]);

  // Суммируем значения по каждому ключу
  allKeys.forEach(key => {
    const val1 = materials1[key] || 0;
    const val2 = materials2[key] || 0;
    result[key] = val1 + val2;
  });

  return result;
});

/*
    #2 подсчет профиля потолочного направляющего.
    Сколько потребуется штук, также сколько потребуется для него дюбелей, саморезов и тд.
 */
const walls = ref(props.walls);
const ceilingProfileLength = ref(3);
const ceilProfileMountedStep = ref(0.4);
const ceilProfileSuspensionsStep = ref(0.5);
/**
 * Для стен (правильный многоугольник) находит максимальную длину и ширину, чтобы представить в виде прямоугольника,
 * чтобы посчитать с его помщью количесво прямых профилей.
 * @param vectors
 * @returns {{maxLength: number, maxWidth: number, width: number, height: number, closureError: (string|string), points: [{x: number, y: number}]}}
 */
const calculateRectangleDimensions = (vectors) => {
  let x = 0, y = 0;
  let minX = 0, maxX = 0, minY = 0, maxY = 0;

  // Обходим все векторы
  for (const v of vectors) {
    const rad = v.angle * Math.PI / 180;

    // Более точное вычисление проекций
    const dx = v.length * Math.cos(rad);
    const dy = v.length * Math.sin(rad);

    // Обновляем позицию
    x += dx;
    y += dy;

    // Обновляем границы
    if (x < minX) minX = x;
    if (x > maxX) maxX = x;
    if (y < minY) minY = y;
    if (y > maxY) maxY = y;
  }

  // Вычисляем габариты с высокой точностью
  const width = Math.abs(maxX - minX);
  const height = Math.abs(maxY - minY);

  // Округляем до целых (учитывая погрешности)
  const roundedWidth = Math.round(width * 1000) / 1000;
  const roundedHeight = Math.round(height * 1000) / 1000;

  // Определяем длину и ширину
  const maxLength = Math.max(roundedWidth, roundedHeight);
  const maxWidth = Math.min(roundedWidth, roundedHeight);

  return {
    maxLength: Math.floor(maxLength.toFixed(10)),
    maxWidth: Math.floor(maxWidth.toFixed(10)),
    exactWidth: width,
    exactHeight: height,
    points: { minX, maxX, minY, maxY }
  };
}

const maxCeilDimensions = ref(calculateRectangleDimensions(walls.value));
const maxCeilWidth = computed( () => {
  return maxCeilDimensions.value.maxWidth;
});
const maxCeilLength = computed( () => {
  return maxCeilDimensions.value.maxLength;
});

/**
 * Подсчет количества профилей прямых (а также метража), нужных для их установки с выбранным шагом.
 * @type {ComputedRef<void>}
 */
const ceilProfileAmountCalc = computed(() => {
  let min = maxCeilWidth.value;
  let max = maxCeilLength.value;
  if (maxCeilLength.value < min) {
    min = maxCeilLength.value;
    max = maxCeilWidth.value;
  }
  const lines = Math.ceil((max / 100) / ceilProfileMountedStep.value);
  let meters = lines * min / 100;
  // если метры не кратны 3-м, т.е. нужно дополнить до ближайшей 3-ки
  const ch = meters % ceilingProfileLength.value;
  if (ch !== 0){
    meters += ceilingProfileLength.value - ch;
  }

  // todo: это нужно отсюда вынести
  // крепежи тут же посчитаем (подвесы прямые)
  let suspencionsAmount = Math.round(min / 100 / ceilProfileSuspensionsStep.value);
  if (suspencionsAmount < 1)  {
    suspencionsAmount = 0;
  }else{
    //suspencionsAmount--; на каждую линию экономится 1 штука. Но лучше посчитаю больше, вдруг умники ровно принесут!
    suspencionsAmount *= lines;
  }

  // todo: это также нужно отсюда вынести
  // подсчитаю количество нужных семечек (id=14)
  // 5 штук на 1 линию (линия предполагается по ширине) + 4 штуки на каждый подвес!
  let seedScrews = lines * 5;
  seedScrews += suspencionsAmount * 4;

  return  {
    meters: meters,
    amount: meters / ceilingProfileLength.value,
    suspencionsAmount: suspencionsAmount,
    seedScrews: seedScrews,
  }
});

const deleteMaterial = () => {
}

watch(
  () => props.perimeter,
  (nv) => {
    perimeter.value = nv;
  },
  {immediate: true,}
);
watch(
  () => props.ceilSquare,
  (nv) => {
    ceilSquare.value = nv;
  },
  {immediate: true}
);
watch(
  () => props.walls,
  (nv) => {
    walls.value = nv;
    maxCeilDimensions.value = calculateRectangleDimensions(walls.value);
  },
  {immediate: true}
);

onMounted(() => {
});
</script>

<style scoped>

</style>
