import { defineStore } from 'pinia'

export const useToastStore = defineStore('toast', {
  state: () => ({
    message: '',
    type: 'info', // 'info', 'success', 'warning', 'error'
    visible: false,
    timeoutId: null
  }),

  actions: {
    show(message, type = 'info', duration = 3500) {
      if (this.timeoutId) {
        clearTimeout(this.timeoutId)
      }
      this.message = message
      this.type = type
      this.visible = true

      this.timeoutId = setTimeout(() => {
        this.visible = false
        this.message = ''
      }, duration)
    },

    hide() {
      if (this.timeoutId) {
        clearTimeout(this.timeoutId)
      }
      this.visible = false
      this.message = ''
    }
  }
})
