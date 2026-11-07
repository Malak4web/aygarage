<template>
  <div class="order-summary-box">
    <h3>ملخص الطلب</h3>

    <div class="sum-row">
      <span>مجموع المنتجات</span>
      <b>{{ cartStore.subtotal.toLocaleString('en') }} ج.م</b>
    </div>

    <div v-if="cartStore.discount > 0" class="sum-row discount">
      <span>الخصم ({{ cartStore.couponCode }})</span>
      <b>-{{ cartStore.discount.toLocaleString('en') }} ج.م</b>
    </div>

    <div class="sum-row">
      <span>التوصيل</span>
      <b v-if="effectiveDeliveryFee === 0" class="free-text">مجاني</b>
      <b v-else>{{ effectiveDeliveryFee.toLocaleString('en') }} ج.م</b>
    </div>

    <!-- Free delivery hint -->
    <div v-if="deliveryMethod !== 'pickup' && !cartStore.isFreeDelivery" class="delivery-hint">
      فاضل <b>{{ cartStore.remainingForFreeDelivery.toLocaleString('en') }} ج.م</b> على التوصيل المجاني
    </div>

    <div class="sum-divider"></div>

    <div class="sum-row total">
      <b>الإجمالي</b>
      <b class="total-price">{{ computedTotal.toLocaleString('en') }} <small>ج.م</small></b>
    </div>

    <slot name="actions" :total="computedTotal" />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCartStore } from '@/stores/cart.js'

const props = defineProps({
  deliveryMethod: {
    type: String,
    default: 'home' // 'home' or 'pickup'
  }
})

const cartStore = useCartStore()

const effectiveDeliveryFee = computed(() => {
  if (props.deliveryMethod === 'pickup') return 0
  return cartStore.deliveryFee
})

const computedTotal = computed(() => {
  return Math.max(0, cartStore.subtotal - cartStore.discount + effectiveDeliveryFee.value)
})
</script>

<style scoped>
.order-summary-box {
  background: #ffffff;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 24px;
}

.order-summary-box h3 {
  font-size: 18px;
  font-weight: 800;
  margin: 0 0 16px;
}

.sum-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  margin-bottom: 12px;
  color: var(--mu);
}

.sum-row b {
  color: var(--ink);
  font-variant-numeric: tabular-nums;
}

.sum-row.discount {
  color: var(--sale);
}

.sum-row.discount b {
  color: var(--sale);
}

.free-text {
  color: var(--ok) !important;
  font-weight: 700;
}

.delivery-hint {
  background: var(--sf);
  padding: 8px 12px;
  border-radius: 6px;
  font-size: 12px;
  color: var(--ink);
  margin-bottom: 14px;
}

.sum-divider {
  height: 1px;
  background: var(--ln);
  margin: 16px 0;
}

.sum-row.total {
  font-size: 16px;
  color: var(--ink);
  margin-bottom: 20px;
}

.total-price {
  font-size: 24px;
  font-weight: 800;
}

.total-price small {
  font-size: 13px;
  font-weight: 400;
}
</style>
