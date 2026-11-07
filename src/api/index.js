import { products } from '../data/products.js'
import { bikes } from '../data/bikes.js'
import { categories } from '../data/categories.js'
import { services } from '../data/services.js'
import { defaultUser } from '../data/user.js'

// Helper to simulate network latency if needed, default fast for crisp UI
const delay = (ms = 50) => new Promise((resolve) => setTimeout(resolve, ms))

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

  async getUser() {
    await delay()
    return { ...defaultUser }
  },

  async createOrder(orderData) {
    await delay(120)
    const orderId = String(Math.floor(1000 + Math.random() * 9000))
    const order = {
      id: orderId,
      date: 'اليوم',
      status: 'قيد التجهيز',
      statusClass: 'ok',
      total: orderData.total,
      items: orderData.items,
      customer: orderData.customer,
      deliveryMethod: orderData.deliveryMethod,
      paymentMethod: orderData.paymentMethod
    }
    return order
  },

  async createBooking(bookingData) {
    await delay(120)
    const bookingId = 'BK-' + Math.floor(1000 + Math.random() * 9000)
    return {
      id: bookingId,
      status: 'مؤكد',
      ...bookingData
    }
  }
}
