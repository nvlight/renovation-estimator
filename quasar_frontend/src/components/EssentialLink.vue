<template>
  <q-item
    clickable
    :to="props.to"
    :active="props.active"
    active-class="text-primary"
    @click="emit('select')"
  >
    <q-item-section v-if="props.icon" avatar>
      <q-icon :name="props.icon" />
    </q-item-section>

    <q-item-section>
      <q-item-label>{{ props.title }}</q-item-label>
      <q-item-label v-if="props.caption" caption>{{ props.caption }}</q-item-label>
    </q-item-section>
  </q-item>
</template>

<script setup>
const emit = defineEmits(['select'])

const props = defineProps({
  title: {
    type: String,
    required: true,
  },

  caption: {
    type: String,
    default: '',
  },

  to: {
    type: [String, Object],
    required: true,
  },

  icon: {
    type: String,
    default: '',
  },

  active: {
    type: Boolean,
    default: false,
  },
})
</script>
