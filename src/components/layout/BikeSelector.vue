<template>
  <div class="bike-selector-wrap" ref="wrapRef">
    <button
      type="button"
      class="hi-btn"
      @click="isOpen = !isOpen"
      :aria-expanded="isOpen"
      aria-label="اختيار موديل موتوسيكلك"
    >
      <AppIcon name="bike" :size="24" />
      <span class="hi-text">
        <small class="hi-label">موتوسيكلي</small>
        <b>{{ userStore.selectedBike }}</b>
      </span>
    </button>

    <div v-if="isOpen" class="bike-dropdown" role="menu">
      <div class="dropdown-header">
        <b>اختر موديل الموتوسيكل</b>
        <small>لتخصيص قطع الغيار المتوافقة</small>
      </div>
      <div class="models-list">
        <button
          v-for="model in storeConfig.models"
          :key="model"
          type="button"
          class="model-option"
          :class="{ active: userStore.selectedBike === model }"
          @click="selectModel(model)"
        >
          <span>{{ model }}</span>
          <AppIcon v-if="userStore.selectedBike === model" name="check" :size="16" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useUserStore } from '@/stores/user.js'
import { storeConfig } from '@/config/store.js'
import AppIcon from '@/components/common/AppIcon.vue'

const userStore = useUserStore()
const isOpen = ref(false)
const wrapRef = ref(null)

const selectModel = (model) => {
  userStore.setSelectedBike(model)
  isOpen.value = false
}

const handleClickOutside = (e) => {
  if (wrapRef.value && !wrapRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.bike-selector-wrap {
  position: relative;
}

.hi-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  line-height: 1.3;
  white-space: nowrap;
  color: var(--ink);
  text-align: start;
  padding: 4px 6px;
  border-radius: 8px;
  transition: background 0.15s ease;
}

.hi-btn:hover {
  background: var(--sf);
}

.hi-label {
  display: block;
  color: var(--mu);
  font-size: 12px;
}

.hi-text b {
  display: block;
  font-size: 14px;
}

.bike-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  inset-inline-start: 0;
  width: 220px;
  background: #ffffff;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
  z-index: 100;
  padding: 10px;
}

.dropdown-header {
  padding: 6px 10px 10px;
  border-bottom: 1px solid var(--ln);
  margin-bottom: 6px;
}

.dropdown-header b {
  display: block;
  font-size: 14px;
}

.dropdown-header small {
  display: block;
  font-size: 12px;
  color: var(--mu);
}

.models-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.model-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: var(--ink);
  width: 100%;
  text-align: start;
  transition: background 0.15s ease;
}

.model-option:hover {
  background: var(--sf);
}

.model-option.active {
  background: var(--sf2);
  font-weight: 700;
}
</style>
