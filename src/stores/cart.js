import { defineStore } from 'pinia'
import { storeConfig } from '../config/store.js'
import { useToastStore } from './toast.js'
import { api } from '../api/index.js'

export const useCartStore = defineStore('cart', {
  state: () => {
    let savedItems = []
    let savedCoupon = ''
    let savedAppliedCoupon = null
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
      const storedAppliedCoupon = localStorage.getItem('ayg_applied_coupon')
      if (storedAppliedCoupon) {
        try {
          savedAppliedCoupon = JSON.parse(storedAppliedCoupon)
        } catch (_) {}
      }
    } catch (e) {
      console.warn('Failed to parse cart from storage:', e)
    }

    return {
      items: savedItems,
      couponCode: savedCoupon,
      appliedCoupon: savedAppliedCoupon || null,
      couponError: '',
      isRefreshing: false,
      isRefreshError: false,
      alerts: []
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
      if (!state.couponCode) return 0
      if (state.appliedCoupon) {
        const minSub = state.appliedCoupon.min_subtotal || state.appliedCoupon.minSubtotal || 0
        if (minSub > 0 && this.subtotal < minSub) {
          return 0
        }
        if (state.appliedCoupon.type === 'percent') {
          return Math.round(this.subtotal * (state.appliedCoupon.value / 100))
        }
        return Math.min(state.appliedCoupon.value, this.subtotal)
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
        if (this.appliedCoupon) {
          localStorage.setItem('ayg_applied_coupon', JSON.stringify(this.appliedCoupon))
        } else {
          localStorage.removeItem('ayg_applied_coupon')
        }
      } catch (e) {
        console.warn('Failed to save cart:', e)
      }
    },

    addAlert(text, type = 'info') {
      if (!this.alerts.some((a) => a.text === text)) {
        this.alerts.push({ id: Date.now() + Math.random(), text, type })
      }
    },

    dismissAlert(index) {
      this.alerts.splice(index, 1)
    },

    clearAlerts() {
      this.alerts = []
    },

    async refreshCart() {
      if (this.isRefreshing || this.items.length === 0) return
      this.isRefreshing = true
      this.isRefreshError = false

      try {
        const slugs = [...new Set(this.items.map((i) => i.slug || i.product?.slug).filter(Boolean))]
        if (slugs.length === 0) return

        // Item 7: One request per cart line (GET /products?slugs[]=...&perPage=50)
        const res = await api.getProducts({ slugs, perPage: 50 })
        const productsList = Array.isArray(res) ? res : (res.data || [])
        const productMap = new Map(productsList.map((p) => [p.slug, p]))

        const linesToRemove = new Set()

        for (const item of this.items) {
          const slug = item.slug || item.product?.slug
          const freshProd = productMap.get(slug)

          // Item 4: Out of stock or missing product
          if (!freshProd || freshProd.stock <= 0) {
            linesToRemove.add(item.lineId)
            const pName = freshProd?.name || item.product?.name || slug
            this.addAlert(`${pName} نفد من المخزن واتشال من السلة`, 'warning')
            continue
          }

          let freshOpt = null
          if (item.optionCode) {
            if (freshProd.options && freshProd.options.length > 0) {
              freshOpt = freshProd.options.find((o) => o.code === item.optionCode || o.id === item.optionCode)
              // Item 6: A missing option code should NOT silently become options[0]
              if (!freshOpt) {
                linesToRemove.add(item.lineId)
                this.addAlert(`الخيار المحدد لـ ${freshProd.name} لم يعد متاحاً واتشال من السلة`, 'warning')
                continue
              }
            } else {
              linesToRemove.add(item.lineId)
              this.addAlert(`الخيار المحدد لـ ${freshProd.name} لم يعد متاحاً واتشال من السلة`, 'warning')
              continue
            }
          }

          // Item 3 & 4: Compare against seenPrice
          const oldPrice = item.seenPrice || item.selectedOption?.price || item.product?.price
          const newPrice = freshOpt?.price || freshProd.price

          if (oldPrice && oldPrice !== newPrice) {
            this.addAlert(`تغير سعر ${freshProd.name} من ${oldPrice.toLocaleString('en')} إلى ${newPrice.toLocaleString('en')} ج.م`, 'info')
            item.seenPrice = newPrice
          } else if (!item.seenPrice) {
            item.seenPrice = newPrice
          }

          // Adjust qty if exceeds current stock
          if (item.qty > freshProd.stock) {
            item.qty = freshProd.stock
            this.addAlert(`تم تعديل كمية ${freshProd.name} إلى المتاح بالمخزن (${freshProd.stock})`, 'info')
          }

          // Item 5: Update line in-place so edits made during refresh are preserved
          item.product = freshProd
          item.selectedOption = freshOpt
        }

        if (linesToRemove.size > 0) {
          this.items = this.items.filter((i) => !linesToRemove.has(i.lineId))
        }

        this.checkCouponMinSubtotal()
        this.persist()
      } catch (e) {
        this.isRefreshError = true
        console.warn('Cart refresh failed:', e)
      } finally {
        // Item 5: Reset isRefreshing in finally
        this.isRefreshing = false
      }
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
      this.checkCouponMinSubtotal()
      this.persist()
    },

    removeBySlug(slug) {
      this.items = this.items.filter((i) => i.slug !== slug && i.product?.slug !== slug)
      this.checkCouponMinSubtotal()
      this.persist()
    },

    updateQty(lineId, qty) {
      const item = this.items.find((i) => i.lineId === lineId)
      if (item) {
        const maxStock = item.product?.stock !== undefined ? item.product.stock : 99
        item.qty = Math.min(maxStock, Math.max(1, qty))
        this.checkCouponMinSubtotal()
        this.persist()
      }
    },

    checkCouponMinSubtotal() {
      if (this.appliedCoupon) {
        const minSub = this.appliedCoupon.min_subtotal || this.appliedCoupon.minSubtotal || 0
        if (minSub > 0 && this.subtotal < minSub) {
          this.couponCode = ''
          this.appliedCoupon = null
          this.couponError = `الكوبون محتاج طلب بـ ${minSub} ج.م على الأقل`
        }
      }
    },

    clearCart() {
      this.items = []
      this.couponCode = ''
      this.appliedCoupon = null
      this.couponError = ''
      this.persist()
    },

    async applyCoupon(code) {
      const clean = (code || '').trim()
      if (!clean) {
        this.couponCode = ''
        this.appliedCoupon = null
        this.couponError = 'يرجى إدخال كود الخصم'
        this.persist()
        return false
      }
      try {
        const res = await api.checkCoupon(clean, this.subtotal)
        this.couponCode = res.code || clean.toUpperCase()
        this.appliedCoupon = res
        this.couponError = ''
        this.persist()
        return true
      } catch (err) {
        this.couponCode = ''
        this.appliedCoupon = null
        this.couponError = err?.message || 'كود الخصم غير صحيح'
        this.persist()
        return false
      }
    },

    removeCoupon() {
      this.couponCode = ''
      this.appliedCoupon = null
      this.couponError = ''
      this.persist()
    }
  }
})
