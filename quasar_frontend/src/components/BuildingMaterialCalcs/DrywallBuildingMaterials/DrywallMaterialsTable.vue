<template>
  <div v-if="loaded" class="q-mt-md">
    <div class="text-subtitle1 font-bold">{{ title }}</div>
    <q-table
      :name="tableName"
      title=""
      :rows="rows"
      :columns="materialColumns"
      row-key="id"
      :pagination="{ rowsPerPage: 10 }"
    >
      <template #body-cell-actions="slotProps">
        <q-td :props="slotProps">
          <q-btn
            flat
            round
            size="sm"
            color="negative"
            icon="add"
            @click="emit('add-material', slotProps.row)"
          />
        </q-td>
      </template>
    </q-table>

    <div class="q-mt-md text-subtitle1">
      Сумма всех материалов: <strong>{{ total }}</strong> ₽
    </div>
    <div>
      <q-btn
        color="primary"
        :label="addAllLabel"
        :disable="rows.length === 0"
        @click="emit('add-all')"
      />
    </div>
  </div>
</template>

<script setup>
import { materialColumns } from './drywallMaterials.js'

defineProps({
  loaded: {
    type: Boolean,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  tableName: {
    type: String,
    required: true,
  },
  rows: {
    type: Array,
    required: true,
  },
  total: {
    type: Number,
    required: true,
  },
  addAllLabel: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['add-material', 'add-all'])
</script>
