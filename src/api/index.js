import { products } from '../data/products.js'
import { bikes } from '../data/bikes.js'
import { categories } from '../data/categories.js'
import { services } from '../data/services.js'
import { serviceHistory } from '../data/serviceHistory.js'
import { defaultUser } from '../data/user.js'

// Helper to simulate network latency if needed, default fast for crisp UI
const delay = (ms = 50) => new Promise((resolve) => setTimeout(resolve, ms))

let storedOrders = []
try {
  const o = localStorage.getItem('ayg_orders')
  if (o) storedOrders = JSON.parse(o)
} catch (e) {}

export const api = {
  async getCategories() {
    await delay()
    return [...categories]
  },

  async getProducts(filterFn) {
    await delay()
    if (typeof filterFn === 'function') {
      return products.filter(filterFn)
    }
    return [...products]
  },

  async getProductBySlug(slug) {
    await delay()
    const product = products.find((p) => p.slug === slug)
    if (!product) {
      throw new Error(`المنتج غير موجود: ${slug}`)
    }
    return { ...product }
  },

  async getBikes(filterFn) {
    await delay()
    if (typeof filterFn === 'function') {
      return bikes.filter(filterFn)
    }
    return [...bikes]
  },

  async getBikeBySlug(slug) {
    await delay()
    const bike = bikes.find((b) => b.slug === slug)
    if (!bike) {
      throw new Error(`الموتوسيكل غير موجود: ${slug}`)
    }
    return { ...bike }
  },

  async getServices() {
    await delay()
    return [...services]
  },

  async getServiceHistory() {
    await delay()
    return [...serviceHistory]
  },

  async getUser() {
    await delay()
    return { ...defaultUser }
  },

  async createOrder(orderData) {
    await delay(120)
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
      paymentMethod: orderData.paymentMethod
    }
    storedOrders.unshift(order)
    try {
      localStorage.setItem('ayg_orders', JSON.stringify(storedOrders))
    } catch (e) {}
    return order
  },

  async getOrder(id) {
    await delay(50)
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
    await delay(50)
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
    await delay(50)
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
    await delay(120)
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
