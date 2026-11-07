import { nextTick, watch, onUnmounted } from 'vue'

const FOCUSABLE_SELECTORS = [
  'a[href]',
  'button:not([disabled])',
  'input:not([disabled])',
  'select:not([disabled])',
  'textarea:not([disabled])',
  '[tabindex]:not([tabindex="-1"])'
].join(',')

/**
 * Traps keyboard focus, locks body scroll, and handles Escape key for accessible modals and drawers.
 * @param {import('vue').Ref<boolean>} isOpenRef
 * @param {import('vue').Ref<HTMLElement | null>} containerRef
 * @param {() => void} onClose
 */
export function useModalTrap(isOpenRef, containerRef, onClose) {
  let prevActiveElement = null

  const handleKeydown = (e) => {
    if (!isOpenRef.value) return

    if (e.key === 'Escape') {
      e.preventDefault()
      if (typeof onClose === 'function') onClose()
      return
    }

    if (e.key === 'Tab') {
      const container = containerRef.value
      if (!container) return

      const focusables = Array.from(container.querySelectorAll(FOCUSABLE_SELECTORS))
        .filter((el) => el.offsetWidth > 0 || el.offsetHeight > 0 || el.getClientRects().length > 0)

      if (focusables.length === 0) {
        e.preventDefault()
        return
      }

      const first = focusables[0]
      const last = focusables[focusables.length - 1]

      if (e.shiftKey) {
        if (document.activeElement === first || !container.contains(document.activeElement)) {
          e.preventDefault()
          last.focus()
        }
      } else {
        if (document.activeElement === last || !container.contains(document.activeElement)) {
          e.preventDefault()
          first.focus()
        }
      }
    }
  }

  watch(
    () => isOpenRef.value,
    async (isOpen) => {
      if (isOpen) {
        prevActiveElement = document.activeElement
        document.body.style.overflow = 'hidden'
        window.addEventListener('keydown', handleKeydown)
        await nextTick()
        const container = containerRef.value
        if (container) {
          const focusables = Array.from(container.querySelectorAll(FOCUSABLE_SELECTORS))
            .filter((el) => el.offsetWidth > 0 || el.offsetHeight > 0 || el.getClientRects().length > 0)
          if (focusables.length > 0) {
            focusables[0].focus()
          } else if (container.focus) {
            container.focus()
          }
        }
      } else {
        document.body.style.overflow = ''
        window.removeEventListener('keydown', handleKeydown)
        if (prevActiveElement && prevActiveElement.focus) {
          try {
            prevActiveElement.focus()
          } catch (e) {}
        }
      }
    },
    { immediate: true }
  )

  onUnmounted(() => {
    document.body.style.overflow = ''
    window.removeEventListener('keydown', handleKeydown)
  })
}
