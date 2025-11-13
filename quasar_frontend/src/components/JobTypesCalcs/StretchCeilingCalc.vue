<template>
  <div class="text-h6">Натяжной потолок</div>

  <div class="q-mt-sm">
    <q-select
      v-model="selectedPrice"
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

    <div v-if="selectedPrice" class="q-mt-sm">
        Вы выбрали: {{ selectedPrice }}
    </div>

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

const selectedPrice = ref(null);
const etalonWalls = ref([]);

const perimeter = computed(() => {
  const ch = etalonWalls.value.reduce( (prev, wall) => Number(wall.length + prev), 0);
  return +((ch / 100).toFixed(2));
});

const ceilingSquare = computed( () => {
  return calculatePolygonSquare(etalonWalls.value);
});

/**
 * Подсчет площади многоугольника по формуле Гаусса.
 * @param segments
 * @returns {number}
 */
const calculatePolygonSquare = (segments) => {
  // Строим точки вершин, начиная с (0, 0)
  const points = [{ x: 0, y: 0 }];
  let x = 0;
  let y = 0;

  for (let val of segments) {
    const angleRad = val.angle * Math.PI / 180;
    x += val.length / 100 * Math.cos(angleRad);
    y += val.length / 100 * Math.sin(angleRad);
    points.push({ x, y });
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

// статические цены потолков!
const prices = [
  {id: 1, name: 'Матовый белый до 3.2м', slug: 'mat_white_3.2', price: 400},
  {id: 2, name: 'Матовый белый до 4м., 5м.', slug: 'mat_white_4', price: 450},
  {id: 3, name: 'Глянцевый белый до 3.2м', slug: 'glossy_white_3.2', price: 450},
  {id: 4, name: 'Глянцевый белый до 4м., 5м', slug: 'glossy_white_4', price: 500},
  {id: 5, name: 'Матовый цветной до 3.2м', slug: 'mat_color_3.2', price: 450},
  {id: 6, name: 'Матовый цветной до 4м., 5м.', slug: 'mat_color_4', price: 500},
  {id: 7, name: 'Глянцевый цветной до 3.2м', slug: 'glossy_color_3.2', price: 550},
  {id: 8, name: 'Глянцевый цветной до 4м., 5м', slug: 'glossy_color_4', price: 600},
  {id: 9, name: 'Фактурные до 3.2м', slug: 'textured_3.2', price: 850},
  {id: 10, name: 'Искры (эффект) до 3.2м', slug: 'sparks_effect_3.2', price: 1000},
  {id: 11, name: 'Облака до 3.2м', slug: 'the_clouds_3.2', price: 1000},
  {id: 12, name: 'Фотопечать до 3.2м', slug: 'photo_printing', price: 3500},
];

const priceOptions = prices.map(item => ({
    label: `${item.name} — ${item.price} ₽`,
    value: item.slug,
  })
);

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
  { immediate: true }
)

const resetWalls = () => {
  etalonWalls.value = JSON.parse(JSON.stringify(props.walls));

  // Создаём чистые объекты (без ссылок на прокси / VNode / DOM)
  // etalonWalls.value = props.walls.map(w => ({
  //   length: Number(w.length ?? 0),
  //   angle: Number(w.angle ?? 0),
  //   is_real: Number(w.is_real ?? 0)
  // }))
}

onMounted(() => {

});

</script>

<style scoped>
</style>
