<template>
  <div class="q-mt-md">
    <div>Шаги материалов</div>
    <div class="row" style="gap: 5px">
      <div v-for="field in fields" :key="field.key">
        <q-input
          :model-value="inputs[field.key].value"
          filled
          type="number"
          min="0"
          step="0.01"
          :label="field.label"
          :name="field.name"
          @update:model-value="updateValue(field.key, $event)"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  inputs: {
    type: Object,
    required: true,
  },
  fields: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['update-value'])

const updateValue = (key, value) => {
  emit('update-value', key, Number(value))
}
</script>
