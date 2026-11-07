<template>
  <div class="cart-page">
    <Breadcrumbs :items="breadcrumbItems" />

    <div v-if="cartStore.items.length === 0" class="in">
      <EmptyState
        icon="cart"
        title="سلة المشتريات فارغة"
        description="لم تقم بإضافة أي منتجات للسلة بعد. تصفح أقسام المتجر واختر قطع الغيار والزيوت المناسبة لموتوسيكلك."
        action-label="تصفح المتجر"
        action-to="/c/oils"
      />
    </div>

    <div v-else class="cart in">
      <!-- Cart Lines -->
      <div class="cart-items">
        <h1 class="cart-title">السلة ({{ cartStore.items.length }} منتجات)</h1>

        <!-- Free Delivery Banner -->
        <div class="free-del-banner" :class="{ 'free-achieved': cartStore.isFreeDelivery }">
          <AppIcon :name="cartStore.isFreeDelivery ? 'check' : 'truck'" :size="18" />
          <span v-if="cartStore.isFreeDelivery">
            طلبك مؤهل للتوصيل المجاني بالكامل!
          </span>
          <span v-else>
            فاضل {{ cartStore.freeDeliveryRemaining.toLocaleString('en') }} ج.م على التوصيل المجاني
          </span>
        </div>

        <!-- Lines -->
        <div class="lines-list">
          <div
            v-for="item in cartStore.items"
            :key="item.lineId"
            class="line"
          >
            <div class="im">
              <ProductIllustration
                :type="item.product.type"
                :color="item.product.color"
                :image="item.product.image"
                :width="70"
              />
            </div>

            <div class="line-info">
              <b>{{ item.product.name }}</b>
              <small>
                {{ item.product.brand }} ·
                <span v-if="doesFit(item.product, userStore.selectedBike) === true" class="line-fit">
                  يركب على {{ userStore.selectedBike }}
                </span>
                <span v-else-if="doesFit(item.product, userStore.selectedBike) === null" style="color: var(--mu)">
                  مقاسات متعددة
                </span>
                <span v-else style="color: var(--mu)">
                  مش متأكدين إنه يركب على موتوسيكلك
                </span>
              </small>
            </div>

            <div class="line-qty">
              <QtyStepper
                :model-value="item.qty"
                @update:model-value="cartStore.updateQty(item.lineId, $event)"
                height="40px"
              />
            </div>

            <b class="line-total">
              {{ (getItemPrice(item) * item.qty).toLocaleString('en') }} ج.م
            </b>

            <button
              type="button"
              class="line-remove"
              @click="cartStore.removeItem(item.lineId)"
              aria-label="حذف المنتج من السلة"
            >
              <AppIcon name="x" :size="18" />
            </button>
          </div>
        </div>

        <!-- Bottom Cart Actions -->
        <div class="cart-foot-actions">
          <RouterLink to="/" class="btn o sm">
            كمّل تسوّق
          </RouterLink>
          <span class="install-prompt">
            <AppIcon name="wrench" :size="18" />
            عايز نركّبهم في الجراج؟
            <RouterLink to="/booking" class="install-link">احجز تركيب</RouterLink>
          </span>
        </div>
      </div>

      <!-- Order Summary -->
      <aside class="sum">
        <h3>ملخص الطلب</h3>

        <div class="dl">
          <span>المنتجات ({{ cartStore.totalPieces }} قطع)</span>
          <b>{{ cartStore.subtotal.toLocaleString('en') }} ج.م</b>
        </div>

        <div v-if="cartStore.discount > 0" class="dl">
          <span>الخصم</span>
          <b class="discount-val">− {{ cartStore.discount.toLocaleString('en') }} ج.م</b>
        </div>

        <div class="dl">
          <span>التوصيل</span>
          <b v-if="cartStore.deliveryFee === 0" class="free-text">مجاناً</b>
          <b v-else>{{ cartStore.deliveryFee }} ج.م</b>
        </div>

        <!-- Coupon code -->
        <div class="coupon">
          <input
            type="text"
            v-model="couponInput"
            placeholder="كود الخصم (جرب ZONTES10)"
            class="coupon-input"
          />
          <button type="button" class="btn o sm coupon-btn" @click="applyCoupon">
            تطبيق
          </button>
        </div>
        <div v-if="cartStore.couponError" class="coupon-msg err">
          {{ cartStore.couponError }}
        </div>
        <div v-else-if="cartStore.couponCode" class="coupon-msg ok">
          تم تطبيق كود الخصم {{ cartStore.couponCode }} (10%)
        </div>

        <div class="dl tot">
          <span>الإجمالي</span>
          <b>{{ cartStore.total.toLocaleString('en') }} ج.م</b>
        </div>

        <RouterLink to="/checkout" class="btn full checkout-btn">
          إتمام الشراء
        </RouterLink>

        <div class="pay-notes">
          كاش عند الاستلام · كارت · فودافون كاش · InstaPay
        </div>
      </aside>
    </div>

    <!-- Companion Products Row: ممكن تحتاج كمان -->
    <ProductRow title="ممكن تحتاج كمان" :products="companionProducts" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { products } from '@/data/products.js'
import { useCartStore } from '@/stores/cart.js'
import { useUserStore } from '@/stores/user.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import QtyStepper from '@/components/common/QtyStepper.vue'
import ProductIllustration from '@/components/common/ProductIllustration.vue'
import ProductCard from '@/components/common/ProductCard.vue'
import ProductRow from '@/components/common/ProductRow.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import AppIcon from '@/components/common/AppIcon.vue'
import { doesFit } from '@/utils/fit.js'

const cartStore = useCartStore()
const userStore = useUserStore()
const couponInput = ref('')

const breadcrumbItems = [
  { label: 'الرئيسية', to: '/' },
  { label: 'السلة' }
]

const getItemPrice = (item) => {
  return item.selectedOption?.price || item.product.price
}

const applyCoupon = () => {
  if (couponInput.value.trim()) {
    cartStore.applyCoupon(couponInput.value)
  }
}

const companionProducts = computed(() => {
  return [products[5], products[4], products[7], products[3], products[6]]
})
</script>

<style scoped>
.cart {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 28px;
  padding-top: 18px;
}

.cart-title {
  font-size: 28px;
  margin: 0 0 4px;
}

.free-del-banner {
  background: var(--oksoft);
  color: var(--ok);
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 14px;
  margin: 12px 0 6px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 700;
}

.lines-list {
  display: flex;
  flex-direction: column;
}

.line {
  display: grid;
  grid-template-columns: 100px 1fr 130px 110px 30px;
  gap: 18px;
  align-items: center;
  padding: 18px 0;
  border-bottom: 1px solid var(--ln);
  position: relative;
}

.line .im {
  aspect-ratio: 1;
  border-radius: 10px;
  background: var(--sf);
  display: grid;
  place-items: center;
}

.line-info b {
  display: block;
  font-size: 15px;
  line-height: 1.45;
}

.line-info small {
  color: var(--mu);
  font-size: 13px;
}

.line-fit {
  color: var(--ok);
  font-weight: 700;
}

.line-total {
  font-size: 16px;
  text-align: left;
  font-variant-numeric: tabular-nums;
}

.line-remove {
  background: none;
  border: none;
  color: var(--mu);
  cursor: pointer;
  display: grid;
  place-items: center;
  padding: 4px;
}

.line-remove:hover {
  color: var(--sale);
}

.cart-foot-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 16px;
  flex-wrap: wrap;
  gap: 12px;
}

.install-prompt {
  font-size: 14px;
  color: var(--mu);
  display: flex;
  gap: 8px;
  align-items: center;
}

.install-link {
  color: var(--ink);
  text-decoration: underline;
  font-weight: 700;
}

/* Order Summary Box */
.sum {
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  padding: 22px;
  align-self: flex-start;
  background: #ffffff;
}

.sum h3 {
  margin: 0 0 14px;
  font-size: 18px;
}

.dl {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  font-size: 14.5px;
}

.dl span {
  color: var(--mu);
}

.discount-val {
  color: var(--sale);
}

.free-text {
  color: var(--ok);
}

.dl.tot {
  border-top: 1px solid var(--ln);
  margin-top: 8px;
  padding-top: 14px;
  font-size: 17px;
}

.dl.tot b {
  font-size: 22px;
}

.coupon {
  display: flex;
  gap: 8px;
  margin: 14px 0;
}

.coupon-input {
  flex: 1;
  height: 44px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 12px;
  font-size: 14px;
  color: var(--ink);
  outline: none;
}

.coupon-btn {
  height: 44px;
}

.coupon-msg {
  font-size: 13px;
  margin-top: -6px;
  margin-bottom: 8px;
}

.coupon-msg.err {
  color: var(--sale);
}

.coupon-msg.ok {
  color: var(--ok);
}

.checkout-btn {
  height: 52px;
  margin-top: 10px;
}

.pay-notes {
  text-align: center;
  font-size: 13px;
  color: var(--mu);
  margin-top: 10px;
}

/* Responsive */
@media (max-width: 900px) {
  .cart {
    grid-template-columns: 1fr;
  }
  .line {
    grid-template-columns: 80px 1fr auto;
    grid-template-rows: auto auto;
    gap: 12px;
  }
  .line-qty {
    grid-column: 2;
  }
  .line-total {
    grid-column: 3;
    text-align: right;
  }
  .line-remove {
    position: absolute;
    top: 10px;
    left: 10px;
  }
}
</style>
