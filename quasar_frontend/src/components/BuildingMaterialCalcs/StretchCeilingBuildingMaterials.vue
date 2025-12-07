<template>
  <h6 class="q-mt-md q-mb-md">Подсчет рекомендуемых материалов к покупке</h6>

  <div class="text-subtitle1 font-semibold">Периметр: {{ perimeter }}</div>
  <div class="text-subtitle1 font-semibold">Нужные id материалов: {{ needMaterialsIds }}</div>

  <div class="q-mt-md">
    <div>Шаги крепежа (саморезы)</div>
    <div class="row" style="gap: 5px;">
      <div>
        <q-input filled v-model="fastenerStepInputs.wood" label="деревянные саморезы"/>
      </div>
      <div>
        <q-input filled v-model="fastenerStepInputs.metal" label="Металлические саморезы"/>
      </div>
      <div>
        <q-input filled v-model="fastenerStepInputs.dowel" label="Дюбель гвозди"/>
      </div>
      <div>
        <q-input filled v-model="fastenerStepInputs.dowel" label="Дюбель гвозди"/>
      </div>
    </div>
    <div class="q-mt-md">Количество материалов</div>
    <div>
      <q-input filled v-model="scotchModel" label="Скотч малярный"/>
    </div>
    <!--    <div>fastenerStepInputs: {{ fastenerStepInputs }}</div>-->
  </div>

  <!-- Список рекомендуемых, подсчитанных материалов. -->
  <div v-if="materialsStore.loaded" class="q-mt-md">
<!--    <div v-for="(item, i) in calcedFastenerAmount" :key=i-->
<!--         class="row"-->
<!--         style="gap: 15px;"-->
<!--    >-->
<!--      <div>id: {{ item.id }}</div>-->
<!--      <div>title: {{ item.title }}</div>-->
<!--      <div>value: {{ item.value }}</div>-->
<!--      <div>amount: {{ item.amount }}</div>-->
<!--    </div>-->
    <div class="text-subtitle1 font-bold">Рекомендуемы материалы к покупке</div>
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
  }
});

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
  'wood': 0.16,
  'metal': 0.11,
  'dowel': 0.15,
});

const materialAmount = ref([
  {
    id: 28,
    value: 1,
  }
]);
const scotchModel = ref(
  materialAmount.value.find(i => i.id === 28)?.value || 0
);

const perimeter = ref(props.perimeter);

// 11 - саморез, дерево, 4 см.
//  6 - саморез, металл, 3.5 см.
// 14 - семечка, саморез, 1 см.
// 15 - дюбель гвоздь, 4 см.
// 16 - дюбель гвоздь, 6 см.
// 28 - скотч, клейкая лента.
const needMaterialsIds = [11, 14, 15, 28];
const needFastenerSteps = computed(() => [
  {
    id: 11,
    type: 'fastener',
    value: fastenerStepInputs.value.wood,
  },
  {
    id: 6,
    type: 'fastener',
    value: fastenerStepInputs.value.metal,
  },
  {
    id: 15,
    type: 'fastener',
    value: fastenerStepInputs.value.dowel,
  },
  {
    id: 16,
    type: 'fastener',
    value: fastenerStepInputs.value.dowel,
  },
  {
    // тут не крепеж!
    id: 28,
    value: scotchModel.value,
  }
]);

//const fastenerDefaultWidth = 0.2;

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

const deleteMaterial = () => {
}

watch(
  () => props.perimeter,
  (nv) => {
    perimeter.value = nv;
  },
  {immediate: true,}
);

onMounted(() => {
  materialsStore.loadItems();
});
</script>

<style scoped>

</style>
