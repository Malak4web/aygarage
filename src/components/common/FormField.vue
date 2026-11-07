<template>
  <div class="form-field" :class="{ 'has-error': !!error }">
    <label v-if="label" :for="id" class="field-label">
      {{ label }}
      <span v-if="required" class="req-star">*</span>
    </label>
    <div class="field-control">
      <slot>
        <input
          :id="id"
          :type="type"
          :value="modelValue"
          :placeholder="placeholder"
          :required="required"
          :dir="dir"
          @input="$emit('update:modelValue', $event.target.value)"
          class="form-input"
        />
      </slot>
    </div>
    <span v-if="error" class="field-error">{{ error }}</span>
    <small v-else-if="hint" class="field-hint">{{ hint }}</small>
  </div>
</template>

<script setup>
defineProps({
  id: {
    type: String,
    required: true
  },
  label: {
    type: String,
    default: ''
  },
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text'
  },
  placeholder: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  dir: {
    type: String,
    default: 'inherit'
  },
  error: {
    type: String,
    default: ''
  },
  hint: {
    type: String,
    default: ''
  }
})

defineEmits(['update:modelValue'])
</script>

<style scoped>
.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 14px;
}

.field-label {
  font-size: 13.5px;
  font-weight: 700;
  color: var(--ink);
}

.req-star {
  color: var(--sale);
  margin-inline-start: 2px;
}

.form-input {
  width: 100%;
  height: 46px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 14px;
  font-family: inherit;
  font-size: 14px;
  outline: none;
  background: #ffffff;
  color: var(--ink);
  transition: border-color 0.15s ease;
}

.form-input:focus {
  border-color: var(--ink);
}

.form-input:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: 1px;
}

.has-error .form-input {
  border-color: var(--sale);
}

.field-error {
  font-size: 12px;
  color: var(--sale);
}

.field-hint {
  font-size: 12px;
  color: var(--mu);
}
</style>
