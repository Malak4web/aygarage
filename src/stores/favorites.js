import { defineStore } from 'pinia'

export const useFavoritesStore = defineStore('favorites', {
  state: () => {
    let saved = ['p1', 'p3']
    try {
      const stored = localStorage.getItem('ayg_favorites')
      if (stored) saved = JSON.parse(stored)
    } catch (e) {
      console.warn('Failed to parse favorites:', e)
    }
    return {
      favoriteIds: saved
    }
  },

  getters: {
    count: (state) => state.favoriteIds.length,
    isFavorite: (state) => (id) => state.favoriteIds.includes(id)
  },

  actions: {
    toggle(id) {
      const index = this.favoriteIds.indexOf(id)
      if (index === -1) {
        this.favoriteIds.push(id)
      } else {
        this.favoriteIds.splice(index, 1)
      }
      try {
        localStorage.setItem('ayg_favorites', JSON.stringify(this.favoriteIds))
      } catch (e) {
        console.warn('Failed to save favorites:', e)
      }
    }
  }
})
