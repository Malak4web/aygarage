<template>
  <div class="pager" v-if="totalPages > 1" role="navigation" aria-label="أرقام الصفحات">
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
      :aria-current="p === currentPage ? 'page' : undefined"
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
  border-radius: var(--radius-btn);
  background: #ffffff;
  color: var(--ink);
  font-size: 14px;
  font-weight: 700;
  display: grid;
  place-items: center;
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
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-btn:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: 2px;
}
</style>
