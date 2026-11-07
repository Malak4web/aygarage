import { defineStore } from 'pinia'
import { storeConfig } from '../config/store.js'

export const useCartStore = defineStore('cart', {
  state: () => {
    let savedItems = []
    try {
      const stored = localStorage.getItem('ayg_cart')
      if (stored) {
        savedItems = JSON.parse(stored)
      }
    } catch (e) {
      console.warn('Failed to parse cart from storage:', e)
    }

    // Default items to match design mockup if cart is initially empty
    if (!savedItems || savedItems.length === 0) {
      savedItems = [
        {
          productId: 'p1',
          qty: 3,
          product: {
            id: 'p1',
            slug: 'motul-7100-10w40',
            name: 'زيت موتول 7100 4T 10W-40 تخليقي 100%، لتر',
            brand: 'Motul',
            type: 'oil',
            color: '#b3261e',
            price: 1450,
            oldPrice: 1650
          }
        },
        {
          productId: 'p2',
          qty: 1,
          product: {
            id: 'p2',
            slug: 'zontes-oil-filter-zt350',
            name: 'فلتر زيت أصلي Zontes ZT350',
            brand: 'Zontes',
            type: 'filter',
            price: 320
          }
        },
        {
          productId: 'p3',
          qty: 1,
          product: {
            id: 'p3',
            slug: 'brembo-front-brake-pads',
            name: 'تيل فرامل أمامي Brembo سيراميك',
            brand: 'Brembo',
            type: 'brake',
            price: 680,
            oldPrice: 790
          }
        }
      ]
    }

    return {
      items: savedItems,
      couponCode: '',
      couponDiscount: 710
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

    discount: (state) => {
      // If mock discount is active
      return state.couponDiscount || 0
    },

    isFreeDelivery: (state) => {
      const subtotal = state.items.reduce((sum, item) => {
        const price = item.selectedOption?.price || item.product.price
        return sum + price * item.qty
      }, 0)
      return subtotal >= storeConfig.freeDeliveryThreshold
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
      } catch (e) {
        console.warn('Failed to save cart:', e)
      }
    },

    addItem(product, qty = 1, selectedOption = null) {
      const existing = this.items.find(
        (i) => i.productId === product.id && (!selectedOption || i.selectedOption?.id === selectedOption?.id)
      )

      if (existing) {
        existing.qty += qty
      } else {
        this.items.push({
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
            image: product.image
          }
        })
      }
      this.persist()
    },

    removeItem(productId) {
      this.items = this.items.filter((i) => i.productId !== productId)
      this.persist()
    },

    updateQty(productId, qty) {
      const item = this.items.find((i) => i.productId === productId)
      if (item) {
        item.qty = Math.max(1, qty)
        this.persist()
      }
    },

    clearCart() {
      this.items = []
      this.persist()
    },

    applyCoupon(code) {
      this.couponCode = code
      if (code.trim().toUpperCase() === 'ZONTES10') {
        this.couponDiscount = Math.round(this.subtotal * 0.1)
      } else {
        this.couponDiscount = 710
      }
    }
  }
})
