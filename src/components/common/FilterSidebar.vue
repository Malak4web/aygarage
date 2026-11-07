<template>
  <div>
    <!-- Desktop Sidebar (Only rendered on desktop) -->
    <aside v-if="isDesktop" class="flt desktop-sidebar">
      <slot />
    </aside>

    <!-- Mobile Bottom Sheet Dialog (Only rendered on mobile when open) -->
    <template v-else-if="isOpen">
      <Transition name="fade">
        <div
          class="sheet-backdrop"
          @click="$emit('close')"
          role="presentation"
        ></div>
      </Transition>

      <Transition name="slide-up">
        <div
          class="mobile-sheet"
          role="dialog"
          aria-modal="true"
          :aria-label="title"
        >
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
import { ref, watch, onMounted, onUnmounted } from 'vue'

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

const handleResize = () => {
  if (typeof window !== 'undefined') {
    isDesktop.value = window.innerWidth > 900
  }
}

const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.isOpen) {
    emit('close')
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
  window.addEventListener('keydown', handleKeydown)
})

// Body scroll lock when sheet is open
watch(
  () => props.isOpen,
  (open) => {
    if (open) {
      document.body.style.overflow = 'hidden'
    } else {
      document.body.style.overflow = ''
    }
  }
)

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
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
  border-radius: 20px 20px 0 0;
  z-index: 999;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.2);
}

.sheet-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 20px;
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
