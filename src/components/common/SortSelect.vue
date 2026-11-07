<template>
  <div class="sort-select-wrapper">
    <label v-if="label" :for="id" class="sort-label">{{ label }}</label>
    <div class="select-box">
      <select
        :id="id"
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        class="sort-select"
        aria-label="ترتيب حسب"
      >
        <option
          v-for="opt in options"
          :key="opt.value"
          :value="opt.value"
        >
          {{ opt.label }}
        </option>
      </select>
      <AppIcon name="down" :size="14" class="sort-icon" />
    </div>
  </div>
</template>

<script setup>
import AppIcon from '@/components/common/AppIcon.vue'

defineProps({
  modelValue: {
    type: String,
    required: true
  },
  options: {
    type: Array,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  id: {
    type: String,
    default: 'sort-select'
  }
})

defineEmits(['update:modelValue'])
</script>

<style scoped>
.sort-select-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
}

.sort-label {
  font-size: 13px;
  color: var(--mu);
  white-space: nowrap;
}

.select-box {
  position: relative;
  display: inline-flex;
  align-items: center;
}

.sort-select {
  appearance: none;
  background: #ffffff;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 6px 14px 6px 32px;
  font-size: 13px;
  font-family: inherit;
  color: var(--ink);
  cursor: pointer;
  outline: none;
}

:dir(ltr) .sort-select {
  padding: 6px 32px 6px 14px;
}

.sort-select:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: 1px;
}

.sort-icon {
  position: absolute;
  inset-inline-end: 10px;
  pointer-events: none;
  color: var(--mu);
}
</style>
