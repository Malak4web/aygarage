import { defaultUser } from '../data/user.js'
import { serviceHistory } from '../data/serviceHistory.js'
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

  if (response.status === 401) {
    localStorage.removeItem('ayg_token')
    try {
      const userStore = useUserStore()
      if (typeof userStore?.logout === 'function') {
        userStore.logout()
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

// Stored orders & bookings fallback for orders/booking phases 4 & 5
let storedOrders = []
try {
  const o = localStorage.getItem('ayg_orders')
  if (o) storedOrders = JSON.parse(o)
} catch (e) {}

export const api = {
  // --- Settings ---
  async getSettings() {
    return await request('/settings')
  },

  // --- Categories ---
  async getCategories() {
    return await request('/categories')
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
    return [...serviceHistory]
  },

  // --- User / Auth (Phases 3 & 4 will migrate to real Sanctum endpoints) ---
  async getUser() {
    return { ...defaultUser }
  },

  async createOrder(orderData) {
    const orderId = String(Math.floor(1000 + Math.random() * 9000))
    const totalNum = typeof orderData.totalNum === 'number' ? orderData.totalNum : 0
    const order = {
      id: orderId,
      date: 'اليوم',
      status: 'قيد التجهيز',
      statusClass: 'ok',
      totalNum,
      subtotalNum: orderData.subtotalNum || 0,
      discountNum: orderData.discountNum || 0,
      deliveryFeeNum: orderData.deliveryFeeNum || 0,
      total: `${totalNum.toLocaleString('en')} ج.م`,
      items: orderData.items,
      customer: orderData.customer,
      deliveryMethod: orderData.deliveryMethod,
      paymentMethod: orderData.paymentMethod,
      deliveryDate: orderData.deliveryDate
    }
    storedOrders.unshift(order)
    try {
      localStorage.setItem('ayg_orders', JSON.stringify(storedOrders))
    } catch (e) {}
    return order
  },

  async getOrder(id) {
    try {
      const o = localStorage.getItem('ayg_orders')
      if (o) storedOrders = JSON.parse(o)
    } catch (e) {}
    const found = storedOrders.find((ord) => String(ord.id) === String(id))
    if (!found) {
      throw new Error(`الطلب #${id} غير موجود`)
    }
    return { ...found }
  },

  async getUserOrders() {
    try {
      const o = localStorage.getItem('ayg_orders')
      if (o) storedOrders = JSON.parse(o)
    } catch (e) {}

    let currentUser = null
    try {
      const u = localStorage.getItem('ayg_user')
      if (u) currentUser = JSON.parse(u)
    } catch (e) {}

    const cleanDefault = String(defaultUser.phone || '').replace(/\D+/g, '')
    const isDemo = currentUser && String(currentUser.phone || '').replace(/\D+/g, '') === cleanDefault

    if (currentUser && !isDemo) {
      const cleanPhone = String(currentUser.phone || '').replace(/\D+/g, '')
      return storedOrders.filter(
        (ord) => ord.customer?.phone && String(ord.customer.phone).replace(/\D+/g, '') === cleanPhone
      )
    }

    if (storedOrders.length === 0 && defaultUser.orders?.length > 0) {
      return [...defaultUser.orders]
    }
    return [...storedOrders]
  },

  async getUserBookings() {
    let storedBookings = []
    try {
      const b = localStorage.getItem('ayg_bookings')
      if (b) storedBookings = JSON.parse(b)
    } catch (e) {}

    let currentUser = null
    try {
      const u = localStorage.getItem('ayg_user')
      if (u) currentUser = JSON.parse(u)
    } catch (e) {}

    const cleanDefault = String(defaultUser.phone || '').replace(/\D+/g, '')
    const isDemo = currentUser && String(currentUser.phone || '').replace(/\D+/g, '') === cleanDefault

    if (currentUser && !isDemo) {
      const cleanPhone = String(currentUser.phone || '').replace(/\D+/g, '')
      return storedBookings.filter(
        (b) => b.phone && String(b.phone).replace(/\D+/g, '') === cleanPhone
      )
    }

    if (storedBookings.length === 0 && defaultUser.bookings?.length > 0) {
      return [...defaultUser.bookings]
    }
    return [...storedBookings]
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
