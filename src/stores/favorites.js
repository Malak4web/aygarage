import { defineStore } from 'pinia'
import { api } from '@/api'

const LEGACY_ID_MAP = {
  p1: 'motul-7100-10w40',
  p2: 'zontes-oil-filter',
  p3: 'brembo-brake-pads',
  p4: 'ls2-rapid-ii-helmet',
  p5: 'did-520-chain',
  p6: 'ngk-iridium-cr8eix',
  p7: 'alpinestars-smx-1-air',
  p8: 'yuasa-ytz10s-battery',
  p9: 'pirelli-diablo-rosso-iii',
  p10: 'castrol-power1-10w40',
  p11: 'liqui-moly-street-10w40',
  p12: 'shell-advance-ultra-10w40',
  p13: 'mobil-1-racing-4t-15w50',
  p14: 'repsol-moto-sintetico-10w40',
  p15: 'motul-300v-factory-line-15w50',
  p16: 'motul-5100-10w40'
}

export function resolveSlug(item) {
  if (!item) return ''
  if (typeof item === 'object') {
    return item.slug || LEGACY_ID_MAP[item.id] || String(item.id)
  }
  return LEGACY_ID_MAP[item] || String(item)
}

function loadStoredFavorites() {
  try {
    const raw = typeof localStorage !== 'undefined' ? localStorage.getItem('ayg_favorites') : null
    if (!raw) return []
    const parsed = JSON.parse(raw)
    if (!Array.isArray(parsed)) return []
    const converted = parsed.map(resolveSlug).filter(Boolean)
    if (JSON.stringify(converted) !== raw && typeof localStorage !== 'undefined') {
      localStorage.setItem('ayg_favorites', JSON.stringify(converted))
    }
    return converted
  } catch (e) {
    return []
  }
}

export const useFavoritesStore = defineStore('favorites', {
  state: () => {
    return {
      favoriteIds: loadStoredFavorites(),
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
    isFavorite: (state) => (productOrSlug) => {
      const slug = resolveSlug(productOrSlug)
      if (!slug) return false
      return state.favoriteIds.includes(slug)
    }
  },

  actions: {
    async syncFavorites() {
      const token = typeof localStorage !== 'undefined' ? localStorage.getItem('ayg_token') : null
      if (!token) {
        // Guest mode: load from localStorage
        this.favoriteIds = loadStoredFavorites()
        return
      }

      this.isLoading = true
      try {
        // Merge guest favorites if any exist in localStorage
        const stored = localStorage.getItem('ayg_favorites')
        if (stored) {
          let mergeSuccess = true
          try {
            const guestFavs = JSON.parse(stored)
            if (Array.isArray(guestFavs) && guestFavs.length > 0) {
              for (const item of guestFavs) {
                const slug = resolveSlug(item)
                if (slug) {
                  try {
                    await api.addFavorite(slug)
                  } catch (err) {
                    mergeSuccess = false
                  }
                }
              }
            }
          } catch (_) {
            mergeSuccess = false
          }

          // Item 4: Remove ayg_favorites only after the merge succeeds
          if (mergeSuccess) {
            localStorage.removeItem('ayg_favorites')
          }
        }

        // Fetch user favorites from backend API
        const list = await api.getFavorites()
        if (Array.isArray(list)) {
          this.favoriteProducts = list
          const slugs = new Set()
          list.forEach((p) => {
            if (p.slug) slugs.add(p.slug)
            if (p.id) slugs.add(String(p.id))
          })
          this.favoriteIds = Array.from(slugs)
        }
      } catch (e) {
        console.warn('Failed to sync favorites with server:', e)
      } finally {
        this.isLoading = false
      }
    },

    async toggle(productOrSlug) {
      const slug = resolveSlug(productOrSlug)
      if (!slug) return

      const token = typeof localStorage !== 'undefined' ? localStorage.getItem('ayg_token') : null
      const currentlyFav = this.isFavorite(slug)

      // Optimistic update
      if (currentlyFav) {
        this.favoriteIds = this.favoriteIds.filter((s) => s !== slug && s !== String(slug))
        this.favoriteProducts = this.favoriteProducts.filter(
          (p) => p.slug !== slug && p.id != slug
        )
      } else {
        this.favoriteIds.push(slug)
        if (typeof productOrSlug === 'object' && productOrSlug !== null) {
          this.favoriteProducts.push(productOrSlug)
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

      // Authenticated mode: call server API with slug
      try {
        if (currentlyFav) {
          await api.removeFavorite(slug)
        } else {
          await api.addFavorite(slug)
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
