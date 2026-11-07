<template>
  <label
    class="rad-card"
    :class="{ on: modelValue === value, disabled }"
  >
    <input
      type="radio"
      :name="name"
      :value="value"
      :checked="modelValue === value"
      :disabled="disabled"
      @change="$emit('update:modelValue', value)"
      class="hidden-radio"
    />
    <i class="rad-indicator" aria-hidden="true"></i>
    <div class="rad-content">
      <div class="rad-header">
        <b class="rad-title">{{ title }}</b>
        <span v-if="badge" class="rad-badge">{{ badge }}</span>
      </div>
      <small v-if="desc" class="rad-desc">{{ desc }}</small>
      <slot />
    </div>
  </label>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    required: true
  },
  value: {
    type: [String, Number, Boolean],
    required: true
  },
  name: {
    type: String,
    default: ''
  },
  title: {
    type: String,
    required: true
  },
  desc: {
    type: String,
    default: ''
  },
  badge: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

defineEmits(['update:modelValue'])
</script>

<style scoped>
.rad-card {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 16px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  cursor: pointer;
  background: #ffffff;
  transition: all 0.15s ease;
  position: relative;
}

.rad-card:hover {
  border-color: #b5b5b5;
}

.rad-card.on {
  border-color: var(--ink);
  background: var(--sf);
}

.rad-card.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.hidden-radio {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.rad-card:has(.hidden-radio:focus-visible) {
  outline: 2px solid var(--ink);
  outline-offset: 2px;
}

.rad-indicator {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 1.5px solid var(--mu);
  margin-top: 2px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.rad-card.on .rad-indicator {
  border-color: var(--ink);
}

.rad-card.on .rad-indicator::after {
  content: '';
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--ink);
}

.rad-content {
  flex: 1;
}

.rad-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.rad-title {
  font-size: 14.5px;
  color: var(--ink);
}

.rad-badge {
  font-size: 12px;
  font-weight: 700;
  color: var(--ok);
}

.rad-desc {
  display: block;
  font-size: 13px;
  color: var(--mu);
  margin-top: 2px;
}
</style>
