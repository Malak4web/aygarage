<template>
  <div class="pager" v-if="totalPages > 1">
    <button
      type="button"
      class="page-btn"
      :disabled="currentPage <= 1"
      @click="$emit('update:currentPage', currentPage - 1)"
      aria-label="الصفحة السابقة"
    >
      ‹
    </button>
    <button
      v-for="p in totalPages"
      :key="p"
      type="button"
      class="page-btn"
      :class="{ on: p === currentPage }"
      @click="$emit('update:currentPage', p)"
    >
      {{ p }}
    </button>
    <button
      type="button"
      class="page-btn"
      :disabled="currentPage >= totalPages"
      @click="$emit('update:currentPage', currentPage + 1)"
      aria-label="الصفحة التالية"
    >
      ›
    </button>
  </div>
</template>

<script setup>
defineProps({
  currentPage: {
    type: Number,
    default: 1
  },
  totalPages: {
    type: Number,
    default: 1
  }
})

defineEmits(['update:currentPage'])
</script>

<style scoped>
.pager {
  display: flex;
  gap: 6px;
  justify-content: center;
  margin-top: 26px;
}

.page-btn {
  width: 38px;
  height: 38px;
  border: 1px solid var(--ln);
  border-radius: 8px;
  display: grid;
  place-items: center;
  font-size: 14px;
  background: #ffffff;
  color: var(--ink);
  cursor: pointer;
  transition: all 0.15s ease;
}

.page-btn:hover:not(:disabled) {
  border-color: var(--ink);
}

.page-btn.on {
  background: var(--ink);
  color: #ffffff;
  border-color: var(--ink);
  font-weight: 700;
}

.page-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}
</style>
