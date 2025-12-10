<template>
  <h6 class="q-mt-md q-mb-md">Потолок</h6>

  <div class="text-subtitle1 font-semibold">Периметр: <span class="text-weight-medium">{{ perimeter }}</span> м.</div>
  <div class="text-subtitle1 font-semibold">Площадь потолка: <span class="text-weight-medium">{{ ceilSquare }}</span> м.</div>

  <div class="q-mt-md">
    <div class="text-subtitle1 font-semibold">
      Профиль потолочный направляющий:
      <span class="text-weight-medium">{{ ceilRunnerProfileCalc.amount }}</span> штук.
    </div>
    <div class="text-subtitle1 font-semibold">Id материалов: {{ ceilMaterials}}</div>
    <div class="text-subtitle1 font-semibold">Наибольшая длина стен: {{ maxCeilLength}}</div>
    <div class="text-subtitle1 font-semibold">Наибольшая ширина стен: {{maxCeilWidth}}</div>
<!--    <div>{{ walls }}</div>-->
    <div class="text-subtitle1 font-semibold">
      Профиль потолочный:
      <span class="text-weight-medium">{{ ceilProfileAmountCalc.amount }}</span> штук. ({{ ceilProfileAmountCalc.meters }} м.)
    </div>

<!--    <pre>{{ ceilMaterials }}</pre>-->

    <div class="q-mt-md">Шаги материалов</div>
    <div class="row" style="gap: 5px;">
      <div>
        <q-input filled v-model="materialsInputs.wood.value" label="деревянные саморезы" name="step_wood"/>
      </div>
      <div>
        <q-input filled v-model="materialsInputs.metal.value" label="Металлические саморезы" name="step_metal"/>
      </div>
      <div>
        <q-input filled v-model="materialsInputs.dowel4.value" label="Дюбель гвозди 4 см" name="step_dowel4"/>
      </div>
      <div>
        <q-input filled v-model="materialsInputs.dowel6.value" label="Дюбель гвозди 6 см" name="step_dowel6"/>
      </div>
      <div>
        <q-input filled v-model="materialsInputs.ceilingRunnerProfileLength.value" label="Длина направляющего профиля" name="step_ceilingRunnerProfileLength"/>
      </div>
      <div>
        <q-input filled v-model="materialsInputs.ceilingRunnerProfileJointLength.value" label="Длина стыка направляющего профиля" name="step_ceilingRunnerProfileJointLength"/>
      </div>
      <div>
        <q-input filled v-model="materialsInputs.ceilingProfileLength.value" label="Длина профиля" name="step_ceilingProfileLength"/>
      </div>
      <div>
        <q-input filled v-model="materialsInputs.ceilProfileMountedStep.value" label="Шаг профиля" name="step_ceilProfileMountedStep"/>
      </div>
      <div>
        <q-input filled v-model="materialsInputs.ceilProfileSuspensionsStep.value" label="Шаг подвеса" name="step_ceilProfileSuspensionsStep"/>
      </div>

    </div>

  </div>

  <!-- Список рекомендуемых, подсчитанных материалов. -->
  <div v-if="materialsStore.loaded" class="q-mt-md">
    <div class="text-subtitle1 font-bold">Рекомендуемые материалы к покупке</div>
    <q-table
      name="recomended_to_buy_materials"
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
            icon="add"
            @click="addMaterial(props.row)"
          />
        </q-td>
      </template>
    </q-table>

    <div class="q-mt-md text-subtitle1">Сумма всех материалов:  <strong>{{ allMaterialsSum }} </strong> ₽</div>
    <div>
      <q-btn color="primary" label="Добавить все материалы" @click="addAllMaterials"/>
    </div>
  </div>

  <!--  <pre>materialStepValueRows: {{ materialStepValueRows }}</pre>-->
</template>

<script setup>
import {computed, onMounted, ref, watch} from "vue";
import {useMaterialsStore} from "@/stores/materials.js";
import {Notify} from "quasar";
import {useRoomMaterialsStore} from "@/stores/roomMaterials.js";

const roomMaterialsStore = useRoomMaterialsStore();
const materialsStore = useMaterialsStore();

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
  },
  walls: {
    type: Array,
    required: true,
    default: () => [],
  }
});

const roomId = ref(props.roomId);
const ceilSquare = ref(props.ceilSquare);

const columns = [
  {name: 'actions', label: '#', field: 'actions', sortable: false, align: 'left'},
  {name: 'id', label: 'id', field: 'id', sortable: true, align: 'left'},
  {name: 'title', label: 'Материал', field: 'title', sortable: true, align: 'left'},  // Editable
  {name: 'amount', label: 'количество', field: 'amount', sortable: false, align: 'right'},  // Computed
  {name: 'price', label: 'цена', field: 'price', sortable: false, align: 'right'},  // Computed
  {name: 'sum', label: 'сумма', field: 'sum', sortable: false, align: 'right'},  // Computed
];

const perimeter = ref(props.perimeter);
const walls = ref(props.walls);

const materialsInputs = ref({
  wood: {
    id: 11, // 11 - саморез, дерево, 4 см.
    value: 0.2,
  },
  metal: {
    id: 6, //  6 - саморез, металл, 3.5 см.
    value: 0.21,
  },
  dowel4: {
    id: 1, // 15 - дюбель гвоздь, 4 см.
    value: 0.4,
  },
  dowel6: {
    id: 1, // 16 - дюбель гвоздь, 4 см.
    value: 0.39,
  },
  ceilingRunnerProfileLength: {
    id: 4, // 4  - Профиль потолочный направляющий КМ Стандарт 17х20 мм 3 м 0,50 мм
    value: 3,
  },
  ceilingRunnerProfileJointLength: {
    id: 1, // сколько нужно дополнительно, чтобы соединить 2 направляющих, установлено в 20 см.
    value: 0.2,
  },
  ceilingProfileLength: {
    id: 3, // 3  - Профиль потолочный КМ Стандарт 47х17 мм 3 м 0,50 мм
    value: 3,
  },
  ceilProfileMountedStep: {
    id: 1, // шаг установки профиля.
    value: 0.4,
  },
  ceilProfileSuspensionsStep: {
    id: 5, // 5  - Подвес прямой, шаг установки.
    value: 0.5,
  },
});

/**
 * Получает сумму по конктретной позиции стр.материала, с учетом .шт, например 200 шт написано в стр. материале
 * @param title
 * @param price
 * @param value
 * @returns {number}
 */
const getMaterialTotalSum = (title, price, value) => {
  // Проверяем наличие слова "шт" в заголовке (без учета регистра)
  const hasPieces = /шт/i.test(title);
  let amount = 0;

  if (hasPieces) {
    // Ищем число перед словом "шт"
    // Регулярное выражение ищет число (целое или дробное) перед "шт"
    const match = title.match(/(\d+(?:[.,]\d+)?)\s*шт/i);

    if (match && match[1]) {
      // Заменяем запятую на точку для корректного преобразования
      const quantityPerPack = parseFloat(match[1].replace(',', '.'));

      if (quantityPerPack > 0) {
        // Делим value на количество в упаковке и умножаем на цену
        // todo: если точно считать то так, если по упаковкам, нужно округлить вверх
        // возможно потом для крепежей нужно пересмотреть этот код!
        amount = (value / quantityPerPack) * price; // old variation
        //amount = Math.ceil(value / quantityPerPack) * material.price; // new variation
      } else {
        // Если не удалось извлечь количество, используем стандартный расчет
        amount = value * price;
      }
    } else {
      // Если паттерн не найден, используем стандартный расчет
      amount = value * price;
    }
  } else {
    // Если нет слова "шт", просто умножаем
    amount = value * price;
  }
  amount = Math.ceil(amount);

  return amount;
}

const rows = computed(() => {
  return Object.entries(ceilMaterials.value).map(([id, value]) => {
    const material = materialsStore.items.find(m => m.id === parseInt(id));

    if (!material) return null
    let sum = getMaterialTotalSum(material.title, material.price, value);

    return {
      id: material.id,
      title: material.title,
      amount: value,
      price: material.price,
      sum,
    }
  }).filter(Boolean)
});

/**
 * Получает сумму
 * @type {ComputedRef<number>}
 */
const allMaterialsSum = computed(() => {
  let sm = 0;

  sm = rows.value.reduce((acc, cv) => {
    return acc + cv.sum;
  }, sm);

  return sm;
});

/*
    #1 подсчет профиля потолочного направляющего.
    Сколько потребуется штук, также сколько потребуется для него дюбелей, саморезов и тд.
 */

/**
 * Профиль потолочный направляющий - подсчет
 * @type {ComputedRef<{amount: *, meters: *}>}
 */
const ceilRunnerProfileCalc = computed( () => {
  const amount = perimeter.value / materialsInputs.value.ceilingRunnerProfileLength.value;

  // теперь нужно добавить стыки, каждый стык имеет длину, например 20 см.
  const jointLength = (amount - 1) * materialsInputs.value.ceilingRunnerProfileJointLength.value;
  const jointAmount = jointLength / materialsInputs.value.ceilingRunnerProfileLength.value;

  const amount2 = +(Math.ceil(amount + jointAmount).toFixed(2));
  const meters = amount2 * materialsInputs.value.ceilingProfileLength.value;

  return {amount: amount2, meters,};
});

// ^ количество и тип материалов к покупке
// материалы для направляющего профиля (проход по периметру)
const ceilMaterials1 = computed( () => {
  return {
    15: Math.ceil((ceilRunnerProfileCalc.value.amount * materialsInputs.value.ceilingRunnerProfileLength.value) / materialsInputs.value.dowel4),
    11: Math.ceil((ceilRunnerProfileCalc.value.amount * materialsInputs.value.ceilingRunnerProfileLength.value) / materialsInputs.value.wood.value),
    4: ceilRunnerProfileCalc.value.amount,
  }
});
// материалы для профиля потолочного. (прокидывание линий между направляющими)
const ceilMaterials2 = computed( () => {
  return {
    5: ceilSuspensionsAmount.value,
    15: ceilSuspensionsAmount.value * 3, // по 3 дюбеля на 1 подвес
    11: ceilSuspensionsAmount.value * 4, // по 4 самореза на 1 подвес,
    14: ceilSeedScrewsAmount.value,
    3: ceilProfileAmountCalc.value.amount,
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

// Получение наибольшой длины и ширины стен.
const maxCeilWidth = computed( () => {
  return maxCeilDimensions.value.maxWidth;
});
const maxCeilLength = computed( () => {
  return maxCeilDimensions.value.maxLength;
});

/**
 * Потолок - кол-во прямых подвесов
 * @type {ComputedRef<number>}
 */
const ceilSuspensionsAmount = computed(() => {
  let amount = Math.round(ceilProfileAmountCalc.value.maxWallsWidth / 100 / materialsInputs.value.ceilProfileSuspensionsStep.value);
  if (amount < 1)  {
    amount = 0;
  }else{
    //suspensionsAmount--; на каждую линию экономится 1 штука. Но лучше посчитаю больше, вдруг умники ровно принесут!
    amount *= ceilProfileAmountCalc.value.lines;
  }
  return amount;
});

/**
 * Потолок - кол-во саморезов (семечек)
 * @type {ComputedRef<number>}
 */
const ceilSeedScrewsAmount = computed(() => {
  // подсчитаю количество нужных семечек (id=14)
  // 5 штук на 1 линию (линия предполагается по ширине) + 4 штуки на каждый подвес!
  let amount = ceilProfileAmountCalc.value.lines * 5;
  amount += ceilSuspensionsAmount.value * 4;

  return amount;
});

/**
 * Подсчитывает количество профилей направляющих
 */
const ceilProfileAmountCalc = computed(() => {
  let min = maxCeilWidth.value;
  let max = maxCeilLength.value;
  if (maxCeilLength.value < min) {
    min = maxCeilLength.value; // наименьшая сторона
    max = maxCeilWidth.value; // наибольшая сторона
  }
  const lines = Math.ceil((max / 100) / materialsInputs.value.ceilProfileMountedStep.value);
  let meters = lines * min / 100;
  // если метры не кратны 3-м, т.е. нужно дополнить до ближайшей 3-ки
  const ch = meters % materialsInputs.value.ceilingProfileLength.value;
  if (ch !== 0){
    meters += materialsInputs.value.ceilingProfileLength.value - ch;
  }
  const amount = meters / materialsInputs.value.ceilingProfileLength.value;

  return  {
    meters,
    amount,
    maxWallsLength: max,
    maxWallsWidth: min,
    lines,
  }
});

const addMaterial = (row) => {
  let data = {
    room_id: roomId.value,
    material_id: row.id,
    amount: row.amount,
    sum: row.sum,
  };
  const result = roomMaterialsStore.addItem(roomId.value, data);

  result.then(response => {
    if (response.status >= 200 && response.status < 300) {

      Notify.create({
        type: 'positive',
        message: 'Строительный материал добавлен!',
      });

      return true;
    } else {
      // Имитируем ошибку
      throw new Error(`HTTP ${response.status}`);
    }
  }).catch((error) => {
    console.log(error);
  })
}

const addAllMaterials = () => {

  Notify.create({
    type: 'positive',
    message: 'Все строительные материалы добавлены!',
  });
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
