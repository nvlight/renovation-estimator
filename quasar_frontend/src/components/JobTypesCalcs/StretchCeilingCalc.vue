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
<!--        <div class="text-subtitle1">Натяжной потолок — цена за 1 м.кв.: <span class="text-weight-medium">{{ selectedCeiling.value }} ₽</span></div>-->
        <div class="flex justify-between text-subtitle1">Натяжной потолок + установка: <span class="text-weight-medium">{{ selectedCeilingSum }} ₽</span></div>
<!--        <div class="text-subtitle1">Багет — цена за 1 м.<span class="text-weight-medium">{{ baget.price }} ₽</span></div>-->
        <div class="flex justify-between items-center text-subtitle1" style="gap: 15px;">
          <span>Багеты</span>
          <q-input
            style="width: 110px"
            v-model.number="baget.count"
            name="baget"
            label="метров"
            :rules="[val => val > 0 || 'Введите метры > 0']"
          />
          <span class="text-weight-medium">{{ bagetSum }} ₽</span>
        </div>
        <div class="flex justify-between items-center text-subtitle1" style="gap: 15px;">
          <span>Подлюстренники </span>
          <q-input
            style="width: 110px"
            v-model.number="chandeliers.count"
            name="chandeliers"
            label="штук"
            :rules="[val => val > 0 || 'Введите шт > 0']"
          />
          <span class="text-weight-medium">{{ chandeliersSum }} ₽</span>
        </div>
        <div class="flex justify-between items-center text-subtitle1" style="gap: 15px;">
          <span>Светильники </span>
          <q-input
            style="width: 110px"
            v-model.number="luminaire.count"
            name="luminaire"
            label="штук"
            :rules="[val => val > 0 || 'Введите шт > 0']"
          />
          <span class="text-weight-medium">{{ luminairesSum }} ₽</span>
        </div>
        <div class="flex justify-between items-center text-subtitle1" style="gap: 15px;">
          <span>Трубы </span>
          <q-input
            style="width: 110px"
            v-model.number="pipes.count"
            name="pipes"
            label="штук"
            :rules="[val => val > 0 || 'Введите шт > 0']"
          />
          <span class="text-weight-medium">{{ pipesSum }} ₽</span>
        </div>

        <div class="flex justify-between text-subtitle1">
          <span>Доставка:</span>
          <div>
            <q-checkbox v-model="isCountDelivery" name="is_count_delivery"/>
            <span class="text-weight-medium">{{ deliveryPrice }} ₽</span>
          </div>
        </div>

        <q-separator class="q-ma-sm" />
        <div class="flex justify-between text-subtitle1">
          <span class="text-weight-medium">Итоговая сумма:</span>
          <span class="text-weight-bold">{{ ceilingResult.sum }} ₽</span></div>
<!--        <div class="text-2xl">{{ ceilingResult.sumInfo }}</div>-->

        <div class="flex justify-end q-mt-md">
          <q-btn color="primary" label="Добавить работу" />
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import {ref, defineProps, onMounted, watch, computed} from 'vue'

const props = defineProps({
  walls: {
    type: Array,
    required: false,
    default: () => [],
  }
})

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

const baget = ref({
  count: 10,
  price: 100,
});

const bagetSum = computed( () => {
  return baget.value.count * baget.value.price;
});

const chandeliers = ref({
  count: 1,
  price: 300,
});
const chandeliersSum = computed( () => {
  return chandeliers.value.count * chandeliers.value.price;
});

const luminaire = ref({
  count: 0,
  price: 250,
});
const luminairesSum = computed( () => {
  return luminaire.value.count * luminaire.value.price;
});

const pipes = ref({
  count: 0,
  price: 350,
});
const pipesSum = computed( () => {
  return pipes.value.count * pipes.value.price;
});

const isCountDelivery = ref(true);
const deliveryPrice = ref(1000);

const perimeter = computed(() => {
  const ch = etalonWalls.value.reduce((prev, wall) => Number(wall.length + prev), 0);
  return +((ch / 100).toFixed(2));
});

const ceilingSquare = computed(() => {
  return calculatePolygonSquare(etalonWalls.value);
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

const selectedCeilingSum = computed(() => {
  let square = Math.ceil(ceilingSquare.value);
  if (square < 10) square = 10;
  return square * selectedCeiling.value.value;
})

const ceilingResult = computed( () => {
  let sum = selectedCeilingSum.value + bagetSum.value + chandeliersSum.value + luminairesSum.value + pipesSum.value +
    (isCountDelivery.value ? deliveryPrice.value : 0);

  let sumInfo = [];
  if (ceilingSquare.value){
    sumInfo.push(`Потолок + установка: ${selectedCeilingSum.value}`);
  }
  if (bagetSum.value){
    sumInfo.push(`Багеты: ${bagetSum.value}`);
  }
  if (chandeliersSum.value){
    sumInfo.push(`Подлюстренники: ${chandeliersSum.value}`);
  }
  if (luminairesSum.value){
    sumInfo.push(`Светильники: ${luminairesSum.value}`);
  }
  if (pipesSum.value){
    sumInfo.push(`Трубы: ${pipesSum.value}`);
  }
  if (isCountDelivery.value){
    sumInfo.push(`Доставка: ${deliveryPrice.value}`);
  }


  return {sum, sumInfo}
});

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
