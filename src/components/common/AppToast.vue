<template>
  <Transition name="toast">
    <div
      v-if="toastStore.visible"
      class="toast-container"
      role="status"
      aria-live="polite"
    >
      <div class="toast-card" :class="`toast-${toastStore.type}`">
        <span class="toast-msg">{{ toastStore.message }}</span>
        <button
          type="button"
          class="toast-close"
          @click="toastStore.hide"
          aria-label="إغلاق الإشعار"
        >
          ✕
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { useToastStore } from '@/stores/toast.js'

const toastStore = useToastStore()
</script>

<style scoped>
.toast-container {
  position: fixed;
  bottom: 24px;
  inset-inline-start: 50%;
  transform: translateX(50%);
  z-index: 9999;
  pointer-events: none;
}

:dir(ltr) .toast-container {
  transform: translateX(-50%);
}

.toast-card {
  pointer-events: auto;
  display: flex;
  align-items: center;
  gap: 12px;
  background: #161616;
  color: #ffffff;
  padding: 12px 20px;
  border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  font-size: 14px;
  font-weight: 500;
  max-width: 90vw;
}

.toast-success {
  border-inline-start: 4px solid var(--ok, #1b7f4b);
}

.toast-warning {
  border-inline-start: 4px solid var(--star, #f2a516);
}

.toast-error {
  border-inline-start: 4px solid var(--sale, #c62828);
}

.toast-info {
  border-inline-start: 4px solid #ffffff;
}

.toast-close {
  background: none;
  border: none;
  color: #a0a0a0;
  cursor: pointer;
  padding: 0;
  font-size: 14px;
  display: grid;
  place-items: center;
  margin-inline-start: 8px;
}

.toast-close:hover {
  color: #ffffff;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(16px);
}
</style>
