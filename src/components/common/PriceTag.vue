<template>
  <div class="price-tag" :class="sizeClass">
    <b class="current-price">
      {{ formattedPrice }}
      <small>ج.م</small>
    </b>
    <s v-if="oldPrice && oldPrice > price" class="old-price">
      {{ formattedOldPrice }}
    </s>
    <span v-if="showSavings && savings" class="saving-badge">
      وفّر {{ savings }} ج.م
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  price: {
    type: Number,
    required: true
  },
  oldPrice: {
    type: Number,
    default: 0
  },
  size: {
    type: String,
    default: 'normal' // 'normal', 'large', 'small'
  },
  showSavings: {
    type: Boolean,
    default: false
  }
})

const formattedPrice = computed(() => props.price.toLocaleString('en'))
const formattedOldPrice = computed(() => (props.oldPrice ? props.oldPrice.toLocaleString('en') : ''))

const savings = computed(() => {
  if (props.oldPrice && props.oldPrice > props.price) {
    return (props.oldPrice - props.price).toLocaleString('en')
  }
  return null
})

const sizeClass = computed(() => `price-${props.size}`)
</script>

<style scoped>
.price-tag {
  display: flex;
  align-items: baseline;
  gap: 8px;
  font-variant-numeric: tabular-nums;
}

.current-price {
  font-size: 18px;
  font-weight: 800;
  color: var(--ink);
}

.current-price small {
  font-size: 12px;
  font-weight: 400;
  margin-inline-start: 2px;
}

.old-price {
  color: var(--mu);
  font-size: 13px;
  text-decoration: line-through;
}

.saving-badge {
  color: var(--sale);
  font-size: 12.5px;
  font-weight: 700;
}

.price-large .current-price {
  font-size: 30px;
}

.price-small .current-price {
  font-size: 15px;
}
</style>
