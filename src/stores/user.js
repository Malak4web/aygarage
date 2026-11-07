import { defineStore } from 'pinia'
import { defaultUser } from '../data/user.js'

export const useUserStore = defineStore('user', {
  state: () => {
    let savedBike = 'ZT350-T'
    let isAuthed = true
    let currentUser = { ...defaultUser }

    try {
      const storedBike = localStorage.getItem('ayg_selected_bike')
      if (storedBike) savedBike = storedBike

      const storedUser = localStorage.getItem('ayg_user')
      if (storedUser) {
        currentUser = JSON.parse(storedUser)
      }

      const storedAuth = localStorage.getItem('ayg_auth')
      if (storedAuth !== null) {
        isAuthed = storedAuth === 'true'
      }
    } catch (e) {
      console.warn('Failed to read user data from storage:', e)
    }

    return {
      user: currentUser,
      isAuthenticated: isAuthed,
      selectedBike: savedBike,
      returnUrl: '/'
    }
  },

  getters: {
    userName: (state) => (state.isAuthenticated && state.user ? state.user.name : ''),
    userBikes: (state) => (state.user?.savedBikes ? state.user.savedBikes : [])
  },

  actions: {
    setSelectedBike(model) {
      this.selectedBike = model
      try {
        localStorage.setItem('ayg_selected_bike', model)
      } catch (e) {
        console.warn('Failed to save selected bike:', e)
      }
    },

    login(phone, password) {
      this.isAuthenticated = true
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
      this.user.name = name
      this.user.phone = phone
      if (bikeModel) {
        this.selectedBike = bikeModel
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
      try {
        localStorage.setItem('ayg_auth', 'false')
      } catch (e) {
        console.warn('Failed to save auth state:', e)
      }
    },

    addBike(bike) {
      if (!this.user.savedBikes) this.user.savedBikes = []
      this.user.savedBikes.push(bike)
      this.setSelectedBike(bike.model.replace('Zontes ', ''))
      try {
        localStorage.setItem('ayg_user', JSON.stringify(this.user))
      } catch (e) {
        console.warn('Failed to save user:', e)
      }
    }
  }
})
