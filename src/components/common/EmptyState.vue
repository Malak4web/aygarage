<template>
  <div class="empty-state">
    <div class="empty-icon-wrap">
      <AppIcon :name="icon" :size="48" :stroke-width="1.4" />
    </div>
    <h3 class="empty-title">{{ title }}</h3>
    <p v-if="description || desc" class="empty-desc">{{ description || desc }}</p>
    <div v-if="$slots.action || actionLabel" class="empty-action">
      <slot name="action">
        <RouterLink v-if="actionTo" :to="actionTo" class="btn sm">
          {{ actionLabel }}
        </RouterLink>
        <button v-else-if="actionLabel" type="button" class="btn sm" @click="$emit('action')">
          {{ actionLabel }}
        </button>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import AppIcon from '@/components/common/AppIcon.vue'

defineProps({
  icon: {
    type: String,
    default: 'box'
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  desc: {
    type: String,
    default: ''
  },
  actionLabel: {
    type: String,
    default: ''
  },
  actionTo: {
    type: String,
    default: ''
  }
})

defineEmits(['action'])
</script>

<style scoped>
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 48px 20px;
  background: var(--sf);
  border-radius: var(--radius-card);
  border: 1px dashed var(--ln);
  margin: 20px 0;
}

.empty-icon-wrap {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #ffffff;
  display: grid;
  place-items: center;
  color: var(--mu);
  margin-bottom: 16px;
}

.empty-title {
  margin: 0 0 6px;
  font-size: 18px;
  font-weight: 700;
  color: var(--ink);
}

.empty-desc {
  margin: 0 0 16px;
  color: var(--mu);
  font-size: 14px;
  max-width: 40ch;
}

.empty-action {
  margin-top: 4px;
}
</style>
