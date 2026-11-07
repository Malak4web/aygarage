import { defineStore } from 'pinia'
import { defaultUser } from '../data/user.js'

export const useUserStore = defineStore('user', {
  state: () => {
    let savedBike = 'ZT350-T'
    let savedBikeYear = 2023
    let isAuthed = false
    let currentUser = null

    try {
      const storedBike = localStorage.getItem('ayg_selected_bike')
      if (storedBike) savedBike = storedBike

      const storedBikeYear = localStorage.getItem('ayg_selected_bike_year')
      if (storedBikeYear) savedBikeYear = parseInt(storedBikeYear, 10) || 2023

      const storedAuth = localStorage.getItem('ayg_auth')
      if (storedAuth === 'true') {
        isAuthed = true
        const storedUser = localStorage.getItem('ayg_user')
        currentUser = storedUser ? JSON.parse(storedUser) : structuredClone(defaultUser)
      }
    } catch (e) {
      console.warn('Failed to read user data from storage:', e)
    }

    return {
      user: currentUser,
      isAuthenticated: isAuthed,
      selectedBike: savedBike,
      selectedBikeYear: savedBikeYear
    }
  },

  getters: {
    userName: (state) => (state.isAuthenticated && state.user ? state.user.name : ''),
    userBikes: (state) => (state.isAuthenticated && state.user?.savedBikes ? state.user.savedBikes : []),
    userBikeYear: (state) => {
      const matched = state.user?.savedBikes?.find((b) => b.model.includes(state.selectedBike))
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

    login(phone, password) {
      this.isAuthenticated = true
      if (!this.user) {
        this.user = structuredClone(defaultUser)
      }
      this.user.phone = phone
      try {
        localStorage.setItem('ayg_auth', 'true')
        localStorage.setItem('ayg_user', JSON.stringify(this.user))
      } catch (e) {
        console.warn('Failed to save user:', e)
      }
    },

    register(name, phone, bikeModel = '') {
      this.isAuthenticated = true
      this.user = structuredClone(defaultUser)
      this.user.name = name
      this.user.phone = phone
      if (bikeModel) {
        this.selectedBike = bikeModel
        if (!this.user.savedBikes) this.user.savedBikes = []
        this.user.savedBikes.push({
          id: 'ub_' + Date.now(),
          model: 'Zontes ' + bikeModel,
          year: 2024,
          plate: 'جديد',
          odo: 0,
          nextService: 1000,
          lastVisit: 'اليوم',
          license: '2027',
          col: '#2e2e2e'
        })
      }
      try {
        localStorage.setItem('ayg_auth', 'true')
        localStorage.setItem('ayg_user', JSON.stringify(this.user))
        localStorage.setItem('ayg_selected_bike', this.selectedBike)
      } catch (e) {
        console.warn('Failed to save registration:', e)
      }
    },

    logout() {
      this.isAuthenticated = false
      this.user = null
      try {
        localStorage.setItem('ayg_auth', 'false')
        localStorage.removeItem('ayg_user')
      } catch (e) {
        console.warn('Failed to save auth state:', e)
      }
    },

    addBike(bike) {
      if (!this.user) {
        this.user = structuredClone(defaultUser)
      }
      if (!this.user.savedBikes) this.user.savedBikes = []
      this.user.savedBikes.push(bike)
      this.setSelectedBike(bike.model.replace('Zontes ', ''))
      try {
        localStorage.setItem('ayg_user', JSON.stringify(this.user))
      } catch (e) {
        console.warn('Failed to save user:', e)
      }
    },

    addOrder(order) {
      if (!this.user) {
        this.user = structuredClone(defaultUser)
      }
      if (!this.user.orders) this.user.orders = []
      this.user.orders.unshift(order)
      try {
        localStorage.setItem('ayg_user', JSON.stringify(this.user))
      } catch (e) {
        console.warn('Failed to save order to user:', e)
      }
    }
  }
})
