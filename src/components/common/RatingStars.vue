<template>
  <div class="rate" :aria-label="`التقييم ${rating} من 5`">
    <span class="st" aria-hidden="true">
      <span
        v-for="i in 5"
        :key="i"
        class="star-wrap"
      >
        <svg
          class="star-base"
          width="13"
          height="13"
          viewBox="0 0 24 24"
          fill="none"
          stroke="#d0d0d0"
          stroke-width="2"
        >
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
        </svg>
        <span
          class="star-fill"
          :style="{ width: `${getFillPercent(i)}%` }"
        >
          <svg
            class="star-filled-svg"
            width="13"
            height="13"
            viewBox="0 0 24 24"
            fill="var(--star, #f2a516)"
          >
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
          </svg>
        </span>
      </span>
    </span>
    <span class="rate-val">{{ rating }}</span>
    <span v-if="count !== null && count !== undefined && count !== ''" class="rate-count">({{ count }})</span>
  </div>
</template>

<script setup>
const props = defineProps({
  rating: {
    type: Number,
    default: 5
  },
  count: {
    type: [Number, String],
    default: null
  }
})

const getFillPercent = (i) => {
  if (props.rating >= i) return 100
  if (props.rating <= i - 1) return 0
  return Math.round((props.rating - (i - 1)) * 100)
}
</script>

<style scoped>
.rate {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 12.5px;
  color: var(--mu);
}

.st {
  display: flex;
  gap: 2px;
}

.star-wrap {
  position: relative;
  display: inline-flex;
  width: 13px;
  height: 13px;
}

.star-base {
  display: block;
}

.star-fill {
  position: absolute;
  top: 0;
  inset-inline-start: 0;
  height: 100%;
  overflow: hidden;
  pointer-events: none;
}

.star-filled-svg {
  display: block;
}

.rate-val {
  font-weight: 700;
  color: var(--ink);
}

.rate-count {
  color: var(--mu);
}
</style>
