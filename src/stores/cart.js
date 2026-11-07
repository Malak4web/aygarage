import { defineStore } from 'pinia'
import { storeConfig } from '../config/store.js'
import { useToastStore } from './toast.js'

export const useCartStore = defineStore('cart', {
  state: () => {
    let savedItems = []
    let savedCoupon = ''
    try {
      const stored = localStorage.getItem('ayg_cart')
      if (stored) {
        const parsed = JSON.parse(stored)
        if (Array.isArray(parsed)) {
          savedItems = parsed
            .filter((item) => item && item.product && item.product.id)
            .map((item) => {
              if (!item.lineId) {
                const optId = item.selectedOption?.id || 'default'
                item.lineId = `${item.product.id}_${optId}`
              }
              return item
            })
        }
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

    remainingForFreeDelivery() {
      return this.freeDeliveryRemaining
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
    getDeliveryFee(method = 'home') {
      if (this.items.length === 0 || method === 'pickup') return 0
      return this.isFreeDelivery ? 0 : storeConfig.deliveryFee
    },

    getTotal(method = 'home') {
      return Math.max(0, this.subtotal - this.discount + this.getDeliveryFee(method))
    },

    persist() {
      try {
        localStorage.setItem('ayg_cart', JSON.stringify(this.items))
        localStorage.setItem('ayg_coupon', this.couponCode)
      } catch (e) {
        console.warn('Failed to save cart:', e)
      }
    },

    addItem(product, qty = 1, selectedOption = null) {
      const stock = product.stock !== undefined ? product.stock : 99
      const toastStore = useToastStore()

      if (stock <= 0 || qty <= 0) {
        if (toastStore && stock <= 0) {
          toastStore.show('هذا المنتج غير متوفر حالياً في المخزن', 'warning')
        }
        return false
      }

      const lineId = `${product.id}_${selectedOption?.id || 'default'}`
      const existing = this.items.find((i) => i.lineId === lineId)

      if (existing) {
        if (existing.qty >= stock) {
          if (toastStore) {
            toastStore.show(`الكمية المتاحة في المخزن ${stock} قطع فقط`, 'info')
          }
          return false
        }
        if (existing.qty + qty > stock) {
          existing.qty = stock
          if (toastStore) {
            toastStore.show(`الكمية المتاحة في المخزن ${stock} قطع فقط`, 'info')
          }
        } else {
          existing.qty += qty
        }
      } else {
        let initialQty = qty
        if (initialQty > stock) {
          initialQty = stock
          if (toastStore) {
            toastStore.show(`الكمية المتاحة في المخزن ${stock} قطع فقط`, 'info')
          }
        }
        if (initialQty <= 0) {
          return false
        }
        this.items.push({
          lineId,
          productId: product.id,
          qty: initialQty,
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
            fits: product.fits || [],
            stock
          }
        })
      }
      this.persist()
      return true
    },

    removeItem(lineId) {
      this.items = this.items.filter((i) => i.lineId !== lineId)
      this.persist()
    },

    updateQty(lineId, qty) {
      const item = this.items.find((i) => i.lineId === lineId)
      if (item) {
        const maxStock = item.product?.stock !== undefined ? item.product.stock : 99
        item.qty = Math.min(maxStock, Math.max(1, qty))
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
