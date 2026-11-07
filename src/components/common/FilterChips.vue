<template>
  <div v-if="chips.length > 0" class="filter-chips">
    <div
      v-for="chip in chips"
      :key="chip.id"
      class="chip-pill"
    >
      <span>{{ chip.label }}</span>
      <button
        type="button"
        class="chip-remove"
        @click="$emit('remove', chip.id)"
        :aria-label="`إزالة الفلتر ${chip.label}`"
      >
        ✕
      </button>
    </div>

    <button
      v-if="chips.length > 1"
      type="button"
      class="clear-all-btn"
      @click="$emit('clear-all')"
    >
      مسح الكل
    </button>
  </div>
</template>

<script setup>
defineProps({
  chips: {
    type: Array,
    default: () => []
  }
})

defineEmits(['remove', 'clear-all'])
</script>

<style scoped>
.filter-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
  margin-bottom: 16px;
}

.chip-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  background: var(--sf);
  border: 1px solid var(--ln);
  border-radius: 999px;
  font-size: 12px;
  font-weight: 500;
  color: var(--ink);
}

.chip-remove {
  background: none;
  border: none;
  padding: 0;
  font-size: 11px;
  color: var(--mu);
  cursor: pointer;
  display: grid;
  place-items: center;
}

.chip-remove:hover {
  color: var(--sale);
}

.clear-all-btn {
  background: none;
  border: none;
  font-size: 12px;
  font-weight: 700;
  color: var(--sale);
  cursor: pointer;
  padding: 4px 6px;
  text-decoration: underline;
}
</style>
