<template>
  <div class="text-h6">Натяжной потолок</div>

  <div class="q-mt-sm">
    <q-select
      v-model="selectedCeiling"
      :options="priceOptions"
      option-value="slug"
      option-label="label"
      label="Выберите тип потолка"
      outlined
      dense
      emit-value
      map-options
      name="stretch_ceiling_select"
    />

<!--    <div v-if="selectedCeiling" class="q-mt-sm">Вы выбрали: {{ selectedCeiling }}</div>-->

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
        <div class="text-subtitle1">Периметр: <span class="text-weight-medium">{{ perimeter }} м.</span></div>
        <div class="text-subtitle1">Площадь: <span class="text-weight-medium">{{ ceilingSquare }} м.</span></div>
<!--        <div class="flex justify-between text-subtitle1">Натяжной потолок + установка: <span class="text-weight-medium">{{-->
<!--            selectedCeilingSum-->
<!--          }} ₽</span></div>-->

        <q-table
          class="q-mt-sm"
          title=""
          :rows="computedRows"
          :columns="columns"
          row-key="name"
          :pagination="{ rowsPerPage: 0 }"
          :hide-bottom="true"
        >
          <!-- Слот для editable count -->
          <template v-slot:body-cell-count="props">
            <q-td :props="props">
              <div class="row justify-center q-gutter-sm">
                <!-- убираю инпут для строки с Итого -->
                <template v-if="!props.row.isFooter">
                  <q-input
                    v-model.number="props.row.count"
                    style="max-width: 110px; "
                    class=""
                    dense
                    outlined
                    type="number"
                    placeholder="Количество"
                  />
                </template>
              </div>
            </q-td>
          </template>

          <!-- Слот для computed sum (вычисляем на лету или через функцию) -->
          <template v-slot:body-cell-sum="props">
            <q-td :props="props">
              {{ getSum(props.row) }}
            </q-td>
          </template>

        </q-table>

        <div class="flex justify-between q-mt-md text-subtitle1">
          <span class="text-weight-medium">Итоговая сумма:</span>
          <span class="text-weight-bold">{{ totalSum }} ₽</span></div>
        <!--        <div class="text-2xl">{{ ceilingResult.sumInfo }}</div>-->

<!--        <pre>{{ computedRowSumsInfo }}</pre>-->

        <div class="flex justify-end q-mt-md">
          <q-btn @click="addJob" color="primary" label="Добавить работу"/>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import {ref, defineProps, onMounted, watch, computed} from 'vue'
import {useRoomJobsStore} from "@/stores/roomJobs.js";

const props = defineProps({
  roomId: {
    type: Number,
    required: true,
  },
  walls: {
    type: Array,
    required: false,
    default: () => [],
  }
})

const roomJobsStore = useRoomJobsStore();
const roomId = ref(props.roomId);

const etalonWalls = ref([]);

// статические цены потолков!
const prices = [
  {id: 1, name: 'Матовый белый до 3.2м', slug: 'mat_white_3.2', price: 600},
  {id: 2, name: 'Матовый белый до 4м., 5м.', slug: 'mat_white_4', price: 650},
  {id: 3, name: 'Глянцевый белый до 3.2м', slug: 'glossy_white_3.2', price: 650},
  {id: 4, name: 'Глянцевый белый до 4м., 5м', slug: 'glossy_white_4', price: 700},
  {id: 5, name: 'Матовый цветной до 3.2м', slug: 'mat_color_3.2', price: 650},
  {id: 6, name: 'Матовый цветной до 4м., 5м.', slug: 'mat_color_4', price: 700},
  {id: 7, name: 'Глянцевый цветной до 3.2м', slug: 'glossy_color_3.2', price: 700},
  {id: 8, name: 'Глянцевый цветной до 4м., 5м', slug: 'glossy_color_4', price: 750},
  {id: 9, name: 'Фактурные до 3.2м', slug: 'textured_3.2', price: 850},
  {id: 10, name: 'Искры (эффект) до 3.2м', slug: 'sparks_effect_3.2', price: 1000},
  {id: 11, name: 'Облака до 3.2м', slug: 'the_clouds_3.2', price: 1000},
  {id: 12, name: 'Фотопечать до 3.2м', slug: 'photo_printing', price: 3500},
];

const columns = [
  {name: 'name', label: 'Название', field: 'name', sortable: true, align: 'left'},
  {name: 'count', label: 'Количество', field: 'count', sortable: true, align: 'center'},  // Editable
  {name: 'price', label: 'Цена за ед.', field: 'price', sortable: true, align: 'center'},  // Readonly
  {name: 'sum', label: 'Сумма', field: 'sum', sortable: false, align: 'right'},  // Computed
];

const rows = ref([
  {name: 'Потолок + установка', count: 1, price: prices[0].price},
  {name: 'Багеты', count: 1, price: 55},
  {name: 'Люстры', count: 1, price: 200},
  {name: 'Светильники', count: 0, price: 150},
  {name: 'Вставка', count: 0, price: 30},
  {name: 'Трубы', count: 0, price: 150},
  {name: 'Доставка', count: 1, price: 1000}
]);

const computedRows = computed(() => [
  {
    name: rows.value[0].name,
    count: processedCeilingSquare.value, // rows.value[0].count,
    price: selectedCeiling.value.value , //rows.value[0].price,
  },
  {
    name: rows.value[1].name,
    count: processedPerimeter.value, // rows.value[0].count,
    price: rows.value[1].price , //rows.value[0].price,
  },
  ...rows.value.slice(2),  // Все обычные строки
]);

const computedRowSumsInfo = computed( () => {
  return computedRows.value
    .filter(item => {
      return item && typeof item.count === 'number' && item.count > 0;
    })
    .map(item => ({
      name: item.name,
      amount: item.count,
      price: item.price,
      sum: item.price * item.count,
    }));
});

const computedStoreInfo = computed( () => {
  return {
    room_id: roomId.value,
    sum: totalSum.value,
    title: 'Натяжной потолок',
    more_info: computedRowSumsInfo.value,
  }
});

// Computed как функция (для каждой строки)
const getSum = computed(() => (row) => {
  return row.count * row.price;  // Или с форматированием: (row.count * row.price).toFixed(2)
});

/**
 * Итоговая сумма после выбора всех параметров.
 * @type {ComputedRef<number>}
 */
const totalSum = computed(() => {
  return computedRows.value.reduce((acc, row) => acc + getSum.value(row), 0);
});

/**
 * Периметр потолка
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
const ceilingSquare = computed(() => {
  return calculatePolygonSquare(etalonWalls.value);
});

/**
 * Обработанная сумма в кв.м. натяжного потолка.
 * Округление идет в большу сторону;
 * Если квадратура меньше 10 кв.м. то считается 10 кв.м. не важно выходит 9 или 3 кв.м.
 * @type {ComputedRef<number>}
 */
const processedCeilingSquare = computed(() => {
  let sum = calculatePolygonSquare(etalonWalls.value);
  sum = Math.ceil(sum);
  if (sum < 10) sum = 10;
  return sum;
});

/**
 * Обработанная сумма в метрах багетов.
 * Если число не четное, прибавляют 1 метр.
 * К полученное числу прибавляю еще 2 метра про запас (~ 110 рублей)
 * @type {ComputedRef<number>}
 */
const processedPerimeter = computed( () => {
  let val = perimeter.value;
  val = Math.ceil(val);

  if (val % 2 !== 0){
    val++;
  }

  val += 2;
  return val;
});

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

const priceOptions = prices.map(item => ({
    label: `${item.name} — ${item.price} ₽`,
    value: item.price,
    id: item.id,
  })
);
const selectedCeiling = ref(priceOptions[0]);

const addJob = () => {
  roomJobsStore.addRoomJob(roomId.value, computedStoreInfo.value);
};

const resetWalls = () => {
  etalonWalls.value = JSON.parse(JSON.stringify(props.walls));

  // Создаём чистые объекты (без ссылок на прокси / VNode / DOM)
  // etalonWalls.value = props.walls.map(w => ({
  //   length: Number(w.length ?? 0),
  //   angle: Number(w.angle ?? 0),
  //   is_real: Number(w.is_real ?? 0)
  // }))
}

watch(
  () => props.walls,
  (newVal) => {
    if (newVal?.length) {
      etalonWalls.value = JSON.parse(JSON.stringify(newVal));

      //etalonWalls.value = structuredClone(toRaw(newVal)); // не работает!

      // Создаём чистые объекты (без ссылок на прокси / VNode / DOM)
      // etalonWalls.value = newVal.map(w => ({
      //   length: Number(w.length ?? 0),
      //   angle: Number(w.angle ?? 0),
      //   is_real: Number(w.is_real ?? 0)
      // }))
    }
  },
  {immediate: true}
);

onMounted(() => {

});

</script>

<style scoped>
</style>
