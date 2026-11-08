import { defineStore } from 'pinia'
import { api } from '@/api'

const LEGACY_ID_MAP = {
  p1: 'motul-7100-10w40',
  p2: 'zontes-oil-filter-zt350',
  p3: 'brembo-front-brake-pads',
  p4: 'ls2-rapid-ii-helmet',
  p5: 'did-520-vx3-chain',
  p6: 'ngk-iridium-cr8eix',
  p7: 'alpinestars-smx-1-air',
  p8: 'yuasa-ytz10s-battery',
  p9: 'motul-5100-15w50',
  p10: 'pirelli-angel-st-rear',
  p11: 'castrol-power1-racing-10w40',
  p12: 'liqui-moly-street-race-10w40',
  p13: 'shell-advance-ultra-10w40',
  p14: 'mobil-1-racing-4t-15w50',
  p15: 'motul-300v-factory-line-10w40',
  p16: 'repsol-moto-racing-10w40'
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
                    // Item 1: Treat 404 as done (drop entry). Keep localStorage only on network or 5xx errors
                    if (err?.status && err.status !== 404) {
                      mergeSuccess = false
                    } else if (!err?.status) {
                      mergeSuccess = false
                    }
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
      this.favoriteProducts = []
      this.favoriteIds = loadStoredFavorites()
    }
  }
})
