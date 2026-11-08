import { defineStore } from 'pinia'
import { api } from '@/api'
import { useFavoritesStore } from './favorites.js'

export const useUserStore = defineStore('user', {
  state: () => {
    let savedBike = 'ZT350-T'
    let savedBikeYear = 2023
    let token = null

    try {
      const storedBike = localStorage.getItem('ayg_selected_bike')
      if (storedBike) savedBike = storedBike

      const storedBikeYear = localStorage.getItem('ayg_selected_bike_year')
      if (storedBikeYear) savedBikeYear = parseInt(storedBikeYear, 10) || 2023

      token = localStorage.getItem('ayg_token')
    } catch (e) {
      console.warn('Failed to read storage in user store:', e)
    }

    return {
      user: null,
      token: token || null,
      isAuthenticated: Boolean(token),
      selectedBike: savedBike,
      selectedBikeYear: savedBikeYear,
      isLoading: false
    }
  },

  getters: {
    userName: (state) => (state.isAuthenticated && state.user ? state.user.name : ''),
    userBikes: (state) => (state.isAuthenticated && state.user?.savedBikes ? state.user.savedBikes : []),
    userBikeYear: (state) => {
      const matched = state.user?.savedBikes?.find((b) => b.model?.includes(state.selectedBike) || b.bikeModelCode === state.selectedBike)
      if (matched?.year) return matched.year
      if (state.user?.savedBikes?.[0]?.year) return state.user.savedBikes[0].year
      return state.selectedBikeYear || 2023
    }
  },

  actions: {
    setSelectedBike(model, year = null) {
      this.selectedBike = model
      try {
        localStorage.setItem('ayg_selected_bike', model)
      } catch (e) {
        console.warn('Failed to save selected bike:', e)
      }
      if (year) {
        this.selectedBikeYear = Number(year)
        try {
          localStorage.setItem('ayg_selected_bike_year', String(year))
        } catch (e) {
          console.warn('Failed to save selected bike year:', e)
        }
      }
    },

    setSelectedBikeYear(year) {
      this.selectedBikeYear = Number(year)
      try {
        localStorage.setItem('ayg_selected_bike_year', String(year))
      } catch (e) {
        console.warn('Failed to save selected bike year:', e)
      }
    },

    async fetchUser() {
      const token = localStorage.getItem('ayg_token')
      if (!token) {
        this.clearSession()
        return null
      }

      this.isLoading = true
      try {
        const userData = await api.getMe()
        this.user = userData
        this.token = token
        this.isAuthenticated = true
        localStorage.setItem('ayg_auth', 'true')
        return userData
      } catch (err) {
        if (err.status === 401) {
          this.clearSession()
        }
        return null
      } finally {
        this.isLoading = false
      }
    },

    async login(phone, password) {
      const res = await api.login(phone, password)
      this.token = res.token
      this.user = res.user
      this.isAuthenticated = true

      try {
        localStorage.setItem('ayg_token', res.token)
        localStorage.setItem('ayg_auth', 'true')
      } catch (e) {
        console.warn('Failed to persist token:', e)
      }

      // Sync favorites upon logging in
      try {
        const favStore = useFavoritesStore()
        await favStore.syncFavorites()
      } catch (_) {}

      return res
    },

    async register(userData) {
      const res = await api.register(userData)
      this.token = res.token
      this.user = res.user
      this.isAuthenticated = true

      try {
        localStorage.setItem('ayg_token', res.token)
        localStorage.setItem('ayg_auth', 'true')
      } catch (e) {
        console.warn('Failed to persist token:', e)
      }

      if (userData.bikeModel) {
        this.setSelectedBike(userData.bikeModel)
      }

      // Sync favorites
      try {
        const favStore = useFavoritesStore()
        await favStore.syncFavorites()
      } catch (_) {}

      return res
    },

    clearSession() {
      this.isAuthenticated = false
      this.user = null
      this.token = null

      try {
        localStorage.removeItem('ayg_token')
        localStorage.setItem('ayg_auth', 'false')
        sessionStorage.removeItem('ayg_pending_booking')
      } catch (e) {
        console.warn('Failed to clear auth state:', e)
      }

      try {
        const favStore = useFavoritesStore()
        favStore.clearFavorites()
      } catch (_) {}
    },

    async logout() {
      const token = this.token || (typeof localStorage !== 'undefined' ? localStorage.getItem('ayg_token') : null)
      if (token) {
        try {
          await api.logout()
        } catch (_) {}
      }

      this.clearSession()

      try {
        const { useCartStore } = await import('./cart.js')
        const cartStore = useCartStore()
        if (typeof cartStore.clearCart === 'function') {
          cartStore.clearCart()
        }
      } catch (_) {}
    },

    async addBike(bikeData) {
      const newBike = await api.addUserBike(bikeData)
      if (!this.user) {
        this.user = { savedBikes: [] }
      }
      if (!this.user.savedBikes) {
        this.user.savedBikes = []
      }
      this.user.savedBikes.push(newBike)

      const bikeCode = newBike.bikeModelCode || (newBike.model ? newBike.model.replace(/^Zontes\s+/i, '') : '')
      if (bikeCode) {
        this.setSelectedBike(bikeCode, newBike.year)
      }
      return newBike
    },

    async deleteBike(bikeId) {
      await api.deleteUserBike(bikeId)
      if (this.user?.savedBikes) {
        this.user.savedBikes = this.user.savedBikes.filter((b) => b.id != bikeId && b.dbId != bikeId)
      }
    },

    async updateProfile(profileData) {
      const res = await api.updateMe(profileData)
      if (res.user) {
        this.user = { ...this.user, ...res.user }
      }
      return res
    },

    addOrder(order) {
      if (!this.user) return
      if (!this.user.orders) this.user.orders = []
      this.user.orders.unshift(order)
    }
  }
})
