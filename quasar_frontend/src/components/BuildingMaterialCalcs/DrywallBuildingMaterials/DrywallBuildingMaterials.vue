<template>
  <h6 class="q-mt-md q-mb-md">Подсчет рекомендуемых материалов к покупке</h6>

  <div class="text-subtitle1 font-semibold">Периметр: <span class="text-weight-medium">{{ perimeter }}</span> м.</div>
  <div class="text-subtitle1 font-semibold">Площадь потолка: <span class="text-weight-medium">{{ ceilSquare }}</span> м.</div>
  <div class="text-subtitle1 font-semibold">Площадь стен: <span class="text-weight-medium">{{ wallsSquare }}</span> м.</div>

  <div>
    <drywall-building-materials-for-ceil
      :roomId="props.roomId"
      :perimeter="perimeter"
      :ceilSquare="ceilSquare"
      :walls="walls"
    />
  </div>
</template>

<script setup>
import {onMounted, ref, watch} from "vue";
import DrywallBuildingMaterialsForCeil
  from "@/components/BuildingMaterialCalcs/DrywallBuildingMaterials/DrywallBuildingMaterialsForCeil.vue";

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
  wallsSquare: {
    type: Number,
    required: false,
  },
  walls:{
    type: Array,
    required: true,
    default: () => [],
  }
});

const perimeter = ref(props.perimeter)
const ceilSquare = ref(props.ceilSquare);
const wallsSquare = ref(props.wallsSquare);
const walls = ref(props.walls);

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
  () => props.wallsSquare,
  (nv) => {
    wallsSquare.value = nv;
  },
  {immediate: true}
);
watch(
  () => props.walls,
  (nv) => {
    walls.value = nv;
  },
  {immediate: true}
);


onMounted(() => {

});
</script>

<style scoped>

</style>
