import router from '@/router/index.js'
import { useUserStore } from '@/stores/user.js'

const API_BASE = import.meta.env.VITE_API_URL || ''

async function request(endpoint, options = {}) {
  const url = `${API_BASE}/api${endpoint.startsWith('/') ? endpoint : '/' + endpoint}`
  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    ...options.headers
  }

  const token = localStorage.getItem('ayg_token')
  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }

  let response
  try {
    response = await fetch(url, {
      ...options,
      headers
    })
  } catch (netErr) {
    const error = new Error('تعذر الاتصال بالخادم، يرجى التحقق من اتصالك بالإنترنت.')
    error.status = 0
    error.errors = {}
    throw error
  }

  const isLogoutRequest = endpoint.includes('/auth/logout')

  if (response.status === 401) {
    localStorage.removeItem('ayg_token')
    localStorage.setItem('ayg_auth', 'false')

    if (!isLogoutRequest) {
      try {
        const userStore = useUserStore()
        if (typeof userStore?.clearSession === 'function') {
          userStore.clearSession()
        } else if (userStore?.user) {
          userStore.user = null
        }
      } catch (_) {}

      // Review 3 Item 17: Redirect on 401 only when the current route has requiresAuth
      const requiresAuth = router?.currentRoute?.value?.meta?.requiresAuth
      const currentPath = router?.currentRoute?.value?.fullPath || (typeof window !== 'undefined' ? window.location.pathname : '')
      if (requiresAuth && currentPath && !currentPath.startsWith('/login') && router) {
        router.push(`/login?redirect=${encodeURIComponent(currentPath)}`)
      }
    } else {
      return null
    }
  }

  if (!response.ok) {
    let errorData = null
    try {
      errorData = await response.json()
    } catch (_) {}

    const message = errorData?.message || `خطأ في الاتصال بالخادم (${response.status})`
    const error = new Error(message)
    error.status = response.status

    // Item 13: turn 422 responses into { field: firstMessage } for the forms
    const formattedErrors = {}
    if (errorData?.errors && typeof errorData.errors === 'object') {
      for (const [key, val] of Object.entries(errorData.errors)) {
        formattedErrors[key] = Array.isArray(val) ? val[0] : String(val)
      }
    }
    error.errors = formattedErrors
    throw error
  }

  // Review 3 Item 17: Handle a 204 / empty body without calling response.json()
  if (response.status === 204 || response.headers.get('content-length') === '0') {
    return null
  }
  const responseText = await response.text()
  return responseText ? JSON.parse(responseText) : null
}

// Stored bookings fallback for booking phase 5

export const api = {
  // --- Settings ---
  async getSettings() {
    return await request('/settings')
  },

  // --- Categories (Loaded once, reused across components) ---
  _categoriesPromise: null,
  async getCategories() {
    if (!this._categoriesPromise) {
      this._categoriesPromise = request('/categories').catch((err) => {
        this._categoriesPromise = null
        throw err
      })
    }
    return await this._categoriesPromise
  },

  // --- Products ---
  async getProducts(filterOrParams) {
    let queryParams = ''
    if (filterOrParams && typeof filterOrParams === 'object' && typeof filterOrParams !== 'function') {
      const searchParams = new URLSearchParams()
      for (const [k, v] of Object.entries(filterOrParams)) {
        if (v !== undefined && v !== null && v !== '') {
          if (Array.isArray(v)) {
            v.forEach((item) => searchParams.append(`${k}[]`, item))
          } else if (typeof v === 'boolean') {
            searchParams.append(k, v ? '1' : '0')
          } else {
            searchParams.append(k, v)
          }
        }
      }
      queryParams = '?' + searchParams.toString()
    }

    const res = await request(`/products${queryParams}`)
    let list = Array.isArray(res) ? res : (res.data || [])
    if (res.meta) list.meta = res.meta
    if (res.facets) list.facets = res.facets

    if (typeof filterOrParams === 'function') {
      return list.filter(filterOrParams)
    }
    return list
  },

  async getProductBySlug(slug) {
    return await request(`/products/${encodeURIComponent(slug)}`)
  },

  async getReviews(productSlug) {
    if (!productSlug) return []
    return await request(`/products/${encodeURIComponent(productSlug)}/reviews`)
  },

  // --- Bikes ---
  async getBikes(filterOrParams) {
    let queryParams = ''
    if (filterOrParams && typeof filterOrParams === 'object' && typeof filterOrParams !== 'function') {
      const searchParams = new URLSearchParams()
      for (const [k, v] of Object.entries(filterOrParams)) {
        if (v !== undefined && v !== null && v !== '') {
          if (Array.isArray(v)) {
            v.forEach((item) => searchParams.append(`${k}[]`, item))
          } else if (typeof v === 'boolean') {
            searchParams.append(k, v ? '1' : '0')
          } else {
            searchParams.append(k, v)
          }
        }
      }
      queryParams = '?' + searchParams.toString()
    }

    const res = await request(`/bikes${queryParams}`)
    let list = Array.isArray(res) ? res : (res.data || [])
    if (res.meta) list.meta = res.meta
    if (res.facets) list.facets = res.facets

    if (typeof filterOrParams === 'function') {
      return list.filter(filterOrParams)
    }
    return list
  },

  async getBikeBySlug(slug) {
    return await request(`/bikes/${encodeURIComponent(slug)}`)
  },

  // --- Services ---
  async getServices() {
    return await request('/services')
  },

  // --- Service History (Account) ---
  async getServiceHistory() {
    return []
  },

  // --- User / Auth (Phase 3) ---
  async login(phone, password) {
    return await request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ phone, password })
    })
  },

  async register(userData) {
    return await request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(userData)
    })
  },

  async logout() {
    return await request('/auth/logout', {
      method: 'POST'
    })
  },

  async getMe() {
    return await request('/auth/me')
  },

  async updateMe(data) {
    return await request('/auth/me', {
      method: 'PUT',
      body: JSON.stringify(data)
    })
  },

  async getUser() {
    return await this.getMe()
  },

  // --- Customer Bikes (Phase 3) ---
  async getUserBikes() {
    return await request('/me/bikes')
  },

  async addUserBike(bikeData) {
    return await request('/me/bikes', {
      method: 'POST',
      body: JSON.stringify(bikeData)
    })
  },

  async updateUserBike(id, bikeData) {
    return await request(`/me/bikes/${id}`, {
      method: 'PUT',
      body: JSON.stringify(bikeData)
    })
  },

  async deleteUserBike(id) {
    return await request(`/me/bikes/${id}`, {
      method: 'DELETE'
    })
  },

  // --- Customer Addresses (Phase 3) ---
  async getUserAddresses() {
    return await request('/me/addresses')
  },

  async saveAddress(addressData) {
    return await request('/me/addresses', {
      method: 'POST',
      body: JSON.stringify(addressData)
    })
  },

  async updateAddress(id, addressData) {
    return await request(`/me/addresses/${id}`, {
      method: 'PUT',
      body: JSON.stringify(addressData)
    })
  },

  async deleteAddress(id) {
    return await request(`/me/addresses/${id}`, {
      method: 'DELETE'
    })
  },

  // --- Customer Favorites (Phase 3) ---
  async getFavorites() {
    return await request('/me/favorites')
  },

  async addFavorite(identifier) {
    return await request(`/me/favorites/${encodeURIComponent(identifier)}`, {
      method: 'POST'
    })
  },

  async removeFavorite(identifier) {
    return await request(`/me/favorites/${encodeURIComponent(identifier)}`, {
      method: 'DELETE'
    })
  },

  // --- Sell Requests (Phase 3) ---
  async createSellRequest(data) {
    return await request('/sell-requests', {
      method: 'POST',
      body: JSON.stringify(data)
    })
  },

  async checkCoupon(code, subtotal) {
    return await request('/coupons/check', {
      method: 'POST',
      body: JSON.stringify({ code, subtotal })
    })
  },

  async createOrder(orderData) {
    const res = await request('/orders', {
      method: 'POST',
      body: JSON.stringify(orderData)
    })
    return res?.data || res
  },

  async getOrder(number) {
    const res = await request(`/me/orders/${encodeURIComponent(number)}`)
    return res?.data || res
  },

  async getUserOrders() {
    const res = await request('/me/orders')
    return res?.data || res || []
  },

  async getUserBookings() {
    // Until Phase 5 bookings API is implemented
    return []
  },

  async createBooking(bookingData) {
    const bookingId = bookingData.id || ('BK-' + Math.floor(1000 + Math.random() * 9000))
    const booking = {
      id: bookingId,
      status: 'مؤكد',
      ...bookingData
    }
    let storedBookings = []
    try {
      const b = localStorage.getItem('ayg_bookings')
      if (b) storedBookings = JSON.parse(b)
    } catch (e) {}
    storedBookings.unshift(booking)
    try {
      localStorage.setItem('ayg_bookings', JSON.stringify(storedBookings))
    } catch (e) {}
    return booking
  }
}
