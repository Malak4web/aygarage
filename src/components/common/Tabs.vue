<template>
  <div class="tabs" role="tablist">
    <button
      v-for="tab in tabs"
      :key="tab.id"
      type="button"
      role="tab"
      :id="`tab-${tab.id}`"
      :aria-controls="`panel-${tab.id}`"
      :aria-selected="modelValue === tab.id"
      class="tab-btn"
      :class="{ on: modelValue === tab.id }"
      @click="$emit('update:modelValue', tab.id)"
    >
      {{ tab.label }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    required: true
  },
  tabs: {
    type: Array,
    required: true
  }
})

defineEmits(['update:modelValue'])
</script>

<style scoped>
.tabs {
  display: flex;
  gap: 8px;
  border-bottom: 1px solid var(--ln);
  margin-bottom: 24px;
  overflow-x: auto;
  white-space: nowrap;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.tabs::-webkit-scrollbar {
  display: none;
}

.tab-btn {
  background: none;
  border: none;
  padding: 12px 18px;
  font-size: 15px;
  font-weight: 700;
  color: var(--mu);
  cursor: pointer;
  position: relative;
  transition: color 0.15s ease;
  font-family: inherit;
}

.tab-btn:hover {
  color: var(--ink);
}

.tab-btn.on {
  color: var(--ink);
}

.tab-btn.on::after {
  content: '';
  position: absolute;
  bottom: -1px;
  inset-inline-start: 0;
  inset-inline-end: 0;
  height: 2px;
  background: var(--ink);
}

.tab-btn:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: -2px;
}
</style>
