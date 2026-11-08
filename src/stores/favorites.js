import { defineStore } from 'pinia'
import { api } from '@/api'

export const useFavoritesStore = defineStore('favorites', {
  state: () => {
    let saved = []
    try {
      const stored = localStorage.getItem('ayg_favorites')
      if (stored) saved = JSON.parse(stored)
    } catch (e) {
      console.warn('Failed to parse favorites:', e)
    }
    return {
      favoriteIds: Array.isArray(saved) ? saved : [],
      favoriteProducts: [],
      isLoading: false
    }
  },

  getters: {
    count: (state) => {
      const token = typeof localStorage !== 'undefined' ? localStorage.getItem('ayg_token') : null
      if (token && state.favoriteProducts.length > 0) {
        return state.favoriteProducts.length
      }
      return state.favoriteIds.length
    },
    isFavorite: (state) => (idOrSlug) => {
      if (!idOrSlug) return false
      if (state.favoriteIds.includes(idOrSlug)) return true
      if (typeof idOrSlug === 'number' && state.favoriteIds.includes(String(idOrSlug))) return true
      if (typeof idOrSlug === 'string' && !isNaN(idOrSlug) && state.favoriteIds.includes(Number(idOrSlug))) return true
      return false
    }
  },

  actions: {
    async syncFavorites() {
      const token = typeof localStorage !== 'undefined' ? localStorage.getItem('ayg_token') : null
      if (!token) {
        // Guest mode: load from localStorage
        try {
          const stored = localStorage.getItem('ayg_favorites')
          if (stored) this.favoriteIds = JSON.parse(stored)
        } catch (_) {}
        return
      }

      this.isLoading = true
      try {
        // Merge guest favorites if any exist in localStorage
        const stored = localStorage.getItem('ayg_favorites')
        if (stored) {
          try {
            const guestFavs = JSON.parse(stored)
            if (Array.isArray(guestFavs) && guestFavs.length > 0) {
              for (const item of guestFavs) {
                try {
                  await api.addFavorite(item)
                } catch (_) {}
              }
            }
          } catch (_) {}
          localStorage.removeItem('ayg_favorites')
        }

        // Fetch user favorites from backend API
        const list = await api.getFavorites()
        if (Array.isArray(list)) {
          this.favoriteProducts = list
          const ids = new Set()
          list.forEach((p) => {
            if (p.id) {
              ids.add(p.id)
              ids.add(String(p.id))
            }
            if (p.slug) {
              ids.add(p.slug)
            }
          })
          this.favoriteIds = Array.from(ids)
        }
      } catch (e) {
        console.warn('Failed to sync favorites with server:', e)
      } finally {
        this.isLoading = false
      }
    },

    async toggle(productOrId) {
      const identifier = typeof productOrId === 'object' && productOrId !== null
        ? (productOrId.slug || productOrId.id)
        : productOrId

      const token = typeof localStorage !== 'undefined' ? localStorage.getItem('ayg_token') : null
      const currentlyFav = this.isFavorite(identifier)

      // Optimistic update
      if (currentlyFav) {
        this.favoriteIds = this.favoriteIds.filter(
          (id) => id !== identifier && id !== String(identifier) && id !== Number(identifier)
        )
        if (typeof productOrId === 'object') {
          this.favoriteProducts = this.favoriteProducts.filter(
            (p) => p.id !== productOrId.id && p.slug !== productOrId.slug
          )
        } else {
          this.favoriteProducts = this.favoriteProducts.filter(
            (p) => p.id != identifier && p.slug !== identifier
          )
        }
      } else {
        this.favoriteIds.push(identifier)
        if (typeof productOrId === 'object' && productOrId !== null) {
          this.favoriteProducts.push(productOrId)
        }
      }

      if (!token) {
        // Guest mode: save to localStorage
        try {
          localStorage.setItem('ayg_favorites', JSON.stringify(this.favoriteIds))
        } catch (e) {
          console.warn('Failed to save favorites:', e)
        }
        return
      }

      // Authenticated mode: call server API
      try {
        if (currentlyFav) {
          await api.removeFavorite(identifier)
        } else {
          await api.addFavorite(identifier)
        }
      } catch (err) {
        console.error('Failed to update favorite on server:', err)
        // Revert on error
        await this.syncFavorites()
      }
    },

    clearFavorites() {
      this.favoriteIds = []
      this.favoriteProducts = []
    }
  }
})
