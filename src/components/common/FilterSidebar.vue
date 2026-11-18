<template>
  <div>
    <!-- Desktop Sidebar (Only rendered on desktop) -->
    <aside v-if="isDesktop" class="flt desktop-sidebar">
      <slot />
    </aside>

    <!-- Mobile Bottom Sheet Dialog (Only rendered on mobile when open) -->
    <template v-else-if="isOpen">
      <Transition name="fade" appear>
        <div
          class="sheet-backdrop"
          @click="$emit('close')"
          role="presentation"
        ></div>
      </Transition>

      <Transition name="slide-up" appear>
        <div
          class="mobile-sheet"
          ref="sheetRef"
          role="dialog"
          aria-modal="true"
          :aria-label="title"
        >
          <span class="sheet-grab" aria-hidden="true"></span>
          <div class="sheet-head">
            <h3>{{ title }}</h3>
            <button
              type="button"
              class="close-sheet-btn"
              @click="$emit('close')"
              aria-label="إغلاق الفلاتر"
            >
              ✕
            </button>
          </div>

          <div class="sheet-body">
            <slot />
          </div>

          <div class="sheet-actions">
            <button
              type="button"
              class="btn full"
              @click="$emit('close')"
            >
              تطبيق الفلاتر
            </button>
          </div>
        </div>
      </Transition>
    </template>
  </div>
</template>

<script setup>
import { ref, toRef, onMounted, onUnmounted } from 'vue'
import { useModalTrap } from '@/utils/modal.js'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'الفلترة'
  }
})

const emit = defineEmits(['close'])

const isDesktop = ref(typeof window !== 'undefined' ? window.innerWidth > 900 : true)
const sheetRef = ref(null)

const handleResize = () => {
  if (typeof window !== 'undefined') {
    const desktop = window.innerWidth > 900
    isDesktop.value = desktop
    if (desktop && props.isOpen) {
      emit('close')
      document.body.style.overflow = ''
    }
  }
}

const isOpenRef = toRef(props, 'isOpen')
useModalTrap(isOpenRef, sheetRef, () => emit('close'))

onMounted(() => {
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
.flt {
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  align-self: flex-start;
  background: #ffffff;
}

@media (max-width: 900px) {
  .desktop-sidebar {
    display: none;
  }
}

.sheet-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 998;
}

.mobile-sheet {
  position: fixed;
  bottom: 0;
  inset-inline: 0;
  background: #ffffff;
  border-radius: 24px 24px 0 0;
  z-index: 999;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.2);
}

.sheet-grab {
  width: 40px;
  height: 5px;
  border-radius: 999px;
  background: #d9d9d9;
  margin: 8px auto 0;
  flex-shrink: 0;
}

.sheet-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 20px 14px;
  border-bottom: 1px solid var(--ln);
}

.sheet-head h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
}

.close-sheet-btn {
  background: none;
  border: none;
  font-size: 18px;
  color: var(--mu);
  cursor: pointer;
  padding: 4px;
}

.sheet-body {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
}

.sheet-actions {
  padding: 16px 20px;
  padding-bottom: calc(16px + env(safe-area-inset-bottom));
  border-top: 1px solid var(--ln);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}
</style>
