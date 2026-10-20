<template>
  <h6 class="q-mt-md q-mb-md">Подсчет рекомендуемых материалов к покупке</h6>

  <div class="text-subtitle1 font-semibold">
    Периметр: <span class="text-weight-medium">{{ props.perimeter }}</span> м.
  </div>
  <div class="text-subtitle1 font-semibold">
    Площадь потолка: <span class="text-weight-medium">{{ props.ceilSquare }}</span> м.
  </div>
  <div class="text-subtitle1 font-semibold">
    Площадь стен: <span class="text-weight-medium">{{ props.wallsSquare }}</span> м.
  </div>

  <div>
    <drywall-building-materials-for-ceil
      v-if="props.isCountingCeil"
      :roomId="props.roomId"
      :perimeter="props.perimeter"
      :ceilSquare="props.ceilSquare"
      :walls="props.walls"
    />

    <drywall-building-materials-for-walls
      v-if="props.isCountingWalls"
      :roomId="props.roomId"
      :perimeter="props.perimeter"
      :wallsSquare="props.wallsSquare"
      :walls="props.walls"
      :height="props.height"
    />
  </div>
</template>

<script setup>
import DrywallBuildingMaterialsForCeil from '@/components/BuildingMaterialCalcs/DrywallBuildingMaterials/DrywallBuildingMaterialsForCeil.vue'
import DrywallBuildingMaterialsForWalls from '@/components/BuildingMaterialCalcs/DrywallBuildingMaterials/DrywallBuildingMaterialsForWalls.vue'

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
  walls: {
    type: Array,
    required: true,
    default: () => [],
  },
  height: {
    type: Number,
    required: true,
  },
  isCountingCeil: {
    type: Boolean,
    required: true,
  },
  isCountingWalls: {
    type: Boolean,
    required: true,
  },
})

</script>

<style scoped></style>
