import { defineStore } from 'pinia'
import { storeConfig } from '../config/store.js'

export const useCartStore = defineStore('cart', {
  state: () => {
    let savedItems = []
    let savedCoupon = ''
    try {
      const stored = localStorage.getItem('ayg_cart')
      if (stored) {
        savedItems = JSON.parse(stored)
      }
      const storedCoupon = localStorage.getItem('ayg_coupon')
      if (storedCoupon) {
        savedCoupon = storedCoupon
      }
    } catch (e) {
      console.warn('Failed to parse cart from storage:', e)
    }

    return {
      items: savedItems,
      couponCode: savedCoupon,
      couponError: ''
    }
  },

  getters: {
    count: (state) => state.items.reduce((sum, item) => sum + item.qty, 0),
    totalPieces: (state) => state.items.reduce((sum, item) => sum + item.qty, 0),

    subtotal: (state) => {
      return state.items.reduce((sum, item) => {
        const price = item.selectedOption?.price || item.product.price
        return sum + price * item.qty
      }, 0)
    },

    discount(state) {
      if (state.couponCode && state.couponCode.trim().toUpperCase() === 'ZONTES10') {
        return Math.round(this.subtotal * 0.1)
      }
      return 0
    },

    isFreeDelivery(state) {
      return this.subtotal >= storeConfig.freeDeliveryThreshold
    },

    freeDeliveryRemaining() {
      return Math.max(0, storeConfig.freeDeliveryThreshold - this.subtotal)
    },

    deliveryFee() {
      if (this.items.length === 0) return 0
      return this.isFreeDelivery ? 0 : storeConfig.deliveryFee
    },

    total() {
      return Math.max(0, this.subtotal - this.discount + this.deliveryFee)
    }
  },

  actions: {
    persist() {
      try {
        localStorage.setItem('ayg_cart', JSON.stringify(this.items))
        localStorage.setItem('ayg_coupon', this.couponCode)
      } catch (e) {
        console.warn('Failed to save cart:', e)
      }
    },

    addItem(product, qty = 1, selectedOption = null) {
      const lineId = `${product.id}_${selectedOption?.id || 'default'}`
      const existing = this.items.find((i) => i.lineId === lineId)

      if (existing) {
        existing.qty += qty
      } else {
        this.items.push({
          lineId,
          productId: product.id,
          qty,
          selectedOption,
          product: {
            id: product.id,
            slug: product.slug,
            name: product.name,
            brand: product.brand,
            type: product.type,
            color: product.color,
            price: product.price,
            oldPrice: product.oldPrice,
            image: product.image,
            fits: product.fits || []
          }
        })
      }
      this.persist()
    },

    removeItem(lineId) {
      this.items = this.items.filter((i) => i.lineId !== lineId)
      this.persist()
    },

    updateQty(lineId, qty) {
      const item = this.items.find((i) => i.lineId === lineId)
      if (item) {
        item.qty = Math.max(1, qty)
        this.persist()
      }
    },

    clearCart() {
      this.items = []
      this.couponCode = ''
      this.couponError = ''
      this.persist()
    },

    applyCoupon(code) {
      const clean = (code || '').trim()
      if (clean.toUpperCase() === 'ZONTES10') {
        this.couponCode = clean
        this.couponError = ''
        this.persist()
        return true
      } else {
        this.couponCode = ''
        this.couponError = 'كود الخصم غير صحيح'
        this.persist()
        return false
      }
    },

    removeCoupon() {
      this.couponCode = ''
      this.couponError = ''
      this.persist()
    }
  }
})
