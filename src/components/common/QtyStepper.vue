<template>
  <div class="qty" :style="{ height: height }">
    <button
      type="button"
      class="qty-btn"
      @click="decrement"
      :disabled="modelValue <= min"
      aria-label="تقليل الكمية"
    >
      −
    </button>
    <span class="qty-val">{{ modelValue }}</span>
    <button
      type="button"
      class="qty-btn"
      @click="increment"
      :disabled="max !== undefined && modelValue >= max"
      aria-label="زيادة الكمية"
    >
      +
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Number,
    default: 1
  },
  min: {
    type: Number,
    default: 1
  },
  max: {
    type: Number,
    default: 99
  },
  height: {
    type: String,
    default: '46px'
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

const decrement = () => {
  if (props.modelValue > props.min) {
    const val = props.modelValue - 1
    emit('update:modelValue', val)
    emit('change', val)
  }
}

const increment = () => {
  if (props.max === undefined || props.modelValue < props.max) {
    const val = props.modelValue + 1
    emit('update:modelValue', val)
    emit('change', val)
  }
}
</script>

<style scoped>
.qty {
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 14px;
  font-size: 16px;
  background: #ffffff;
  user-select: none;
}

.qty-btn {
  font-size: 18px;
  font-weight: 700;
  color: var(--ink);
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px 8px;
  display: grid;
  place-items: center;
  line-height: 1;
}

.qty-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.qty-val {
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}
</style>
