<template>
  <nav class="crumb" aria-label="مسار التنقل">
    <template v-for="(item, index) in items" :key="index">
      <RouterLink v-if="item.to && index < items.length - 1" :to="item.to">
        {{ item.label }}
      </RouterLink>
      <b v-else>{{ item.label }}</b>
      <span v-if="index < items.length - 1" class="sep">/</span>
    </template>
  </nav>
</template>

<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  items: {
    type: Array,
    required: true
    // Array of { label: string, to?: string }
  }
})
</script>

<style scoped>
.crumb {
  font-size: 13px;
  color: var(--mu);
  padding: 18px var(--page-pad) 0;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  max-width: 1280px;
  margin: 0 auto;
}

.crumb a {
  color: var(--mu);
  text-decoration: none;
}

.crumb a:hover {
  color: var(--ink);
  text-decoration: underline;
}

.crumb b {
  color: var(--ink);
  font-weight: 400;
}

.sep {
  color: var(--mu);
  opacity: 0.7;
}
</style>
