import { defineStore } from 'pinia'
import { storeConfig } from '../config/store.js'
import { useToastStore } from './toast.js'
import { api } from '../api/index.js'

export const useCartStore = defineStore('cart', {
  state: () => {
    let savedItems = []
    let savedCoupon = ''
    try {
      const version = localStorage.getItem('ayg_cart_v')
      const stored = localStorage.getItem('ayg_cart')

      // Item 18: Clear carts in the old format once
      if (stored && version !== '2') {
        localStorage.removeItem('ayg_cart')
        localStorage.setItem('ayg_cart_v', '2')
      } else if (stored && version === '2') {
        const parsed = JSON.parse(stored)
        if (Array.isArray(parsed)) {
          savedItems = parsed
            .filter((item) => item && (item.slug || item.productId))
            .map((item) => {
              const slug = item.slug || ''
              const optionCode = item.optionCode || null
              const seenPrice = Number(item.seenPrice) || 0
              return {
                slug,
                optionCode,
                qty: Math.max(1, Number(item.qty) || 1),
                lineId: `${slug}_${optionCode || 'default'}`,
                product: item.product || null,
                selectedOption: item.selectedOption || null,
                seenPrice
              }
            })
        }
      } else {
        localStorage.setItem('ayg_cart_v', '2')
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
      couponError: '',
      isRefreshing: false
    }
  },

  getters: {
    count: (state) => state.items.reduce((sum, item) => sum + item.qty, 0),
    totalPieces: (state) => state.items.reduce((sum, item) => sum + item.qty, 0),

    subtotal: (state) => {
      return state.items.reduce((sum, item) => {
        const price = item.selectedOption?.price || item.product?.price || item.seenPrice || 0
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
        // Item 18: Store only slug, option code and qty
        const compact = this.items.map((i) => ({
          slug: i.slug || i.product?.slug,
          optionCode: i.optionCode || i.selectedOption?.code || null,
          qty: i.qty,
          seenPrice: i.selectedOption?.price || i.product?.price || i.seenPrice || 0
        }))
        localStorage.setItem('ayg_cart', JSON.stringify(compact))
        localStorage.setItem('ayg_cart_v', '2')
        localStorage.setItem('ayg_coupon', this.couponCode)
      } catch (e) {
        console.warn('Failed to save cart:', e)
      }
    },

    async refreshCart() {
      if (this.isRefreshing || this.items.length === 0) return
      this.isRefreshing = true
      const toastStore = useToastStore()
      const newItems = []

      for (const item of this.items) {
        const slug = item.slug || item.product?.slug
        if (!slug) continue

        try {
          const freshProd = await api.getProductBySlug(slug)
          if (!freshProd || freshProd.stock <= 0) {
            if (toastStore) {
              toastStore.show(`نفدت كمية ${freshProd?.name || item.product?.name || slug} من المخزن`, 'warning')
            }
            continue // Out of stock: item removed from cart
          }

          let freshOpt = null
          if (item.optionCode && freshProd.options && freshProd.options.length > 0) {
            freshOpt = freshProd.options.find((o) => o.code === item.optionCode || o.id === item.optionCode) || freshProd.options[0]
          }

          const oldPrice = item.selectedOption?.price || item.product?.price
          const newPrice = freshOpt?.price || freshProd.price

          if (oldPrice && oldPrice !== newPrice && toastStore) {
            toastStore.show(`تغير سعر ${freshProd.name} إلى ${newPrice.toLocaleString('en')} ج.م`, 'info')
          }

          let qty = item.qty
          if (qty > freshProd.stock) {
            qty = freshProd.stock
            if (toastStore) {
              toastStore.show(`تم تعديل كمية ${freshProd.name} إلى المتاح بالمخزن (${freshProd.stock})`, 'info')
            }
          }

          newItems.push({
            slug: freshProd.slug,
            optionCode: freshOpt?.code || null,
            lineId: `${freshProd.slug}_${freshOpt?.code || 'default'}`,
            qty,
            product: freshProd,
            selectedOption: freshOpt
          })
        } catch (e) {
          // If product API error (e.g. 404), remove or keep existing
          if (e?.status === 404) {
            if (toastStore) {
              toastStore.show(`المنتج ${item.product?.name || slug} لم يعد متاحاً بالمتجر`, 'warning')
            }
          } else {
            newItems.push(item)
          }
        }
      }

      this.items = newItems
      this.persist()
      this.isRefreshing = false
    },

    addItem(product, qty = 1, selectedOption = null) {
      const stock = product.stock !== undefined ? product.stock : 99
      const toastStore = useToastStore()

      if (stock <= 0 || qty <= 0) {
        if (toastStore && stock <= 0) {
          toastStore.show('هذا المنتج غير متوفر حالياً في المخزن', 'warning')
        }
        return 'out'
      }

      const slug = product.slug
      const optionCode = selectedOption?.code || null
      const lineId = `${slug}_${optionCode || 'default'}`
      const existing = this.items.find((i) => i.lineId === lineId)

      if (existing) {
        if (existing.qty >= stock) {
          if (toastStore) {
            toastStore.show(`الكمية المتاحة في المخزن ${stock} قطع فقط`, 'info')
          }
          return 'clamped'
        }
        if (existing.qty + qty > stock) {
          existing.qty = stock
          if (toastStore) {
            toastStore.show(`الكمية المتاحة في المخزن ${stock} قطع فقط`, 'info')
          }
          this.persist()
          return 'clamped'
        } else {
          existing.qty += qty
          this.persist()
          return 'added'
        }
      } else {
        let initialQty = qty
        let status = 'added'
        if (initialQty > stock) {
          initialQty = stock
          status = 'clamped'
          if (toastStore) {
            toastStore.show(`الكمية المتاحة في المخزن ${stock} قطع فقط`, 'info')
          }
        }
        if (initialQty <= 0) {
          return 'out'
        }
        this.items.push({
          lineId,
          slug,
          optionCode,
          qty: initialQty,
          selectedOption,
          product,
          seenPrice: selectedOption?.price || product?.price || 0
        })
        this.persist()
        return status
      }
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
