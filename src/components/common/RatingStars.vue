<template>
  <div class="rate">
    <span class="st" aria-hidden="true">
      <AppIcon
        v-for="i in 5"
        :key="i"
        name="star"
        :size="12"
      />
    </span>
    <span class="rate-val">{{ rating }}</span>
    <span v-if="count !== undefined" class="rate-count">({{ count }})</span>
  </div>
</template>

<script setup>
import AppIcon from '@/components/common/AppIcon.vue'

defineProps({
  rating: {
    type: Number,
    default: 5
  },
  count: {
    type: [Number, String],
    default: null
  }
})
</script>

<style scoped>
.rate {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--mu);
}

.st {
  display: flex;
  gap: 1px;
  color: var(--star);
}

.rate-val {
  font-weight: 700;
  color: var(--ink);
}

.rate-count {
  color: var(--mu);
}
</style>
