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

        <!-- Cart Alerts / Notifications Box (Review 3 Item 4) -->
        <div v-if="cartStore.alerts && cartStore.alerts.length > 0" class="cart-alerts">
          <div
            v-for="(alert, idx) in cartStore.alerts"
            :key="alert.id || idx"
            class="cart-alert-item"
            :class="alert.type || 'info'"
          >
            <AppIcon :name="alert.type === 'warning' ? 'alert-triangle' : 'info'" :size="18" />
            <span class="alert-text">{{ alert.text }}</span>
            <button
              type="button"
              class="alert-close"
              @click="cartStore.dismissAlert(idx)"
              aria-label="إغلاق التنبيه"
            >
              <AppIcon name="x" :size="14" />
            </button>
          </div>
        </div>

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

        <!-- Refresh Error Banner -->
        <div v-if="cartStore.isRefreshError" class="cart-refresh-banner">
          <div class="refresh-err-msg">
            <AppIcon name="alert-triangle" :size="20" />
            <span>تعذر تحديث السلة. تأكد من اتصال الإنترنت أو حالة الخادم.</span>
          </div>
          <button type="button" class="btn o sm" @click="cartStore.refreshCart()">
            إعادة المحاولة
          </button>
        </div>

        <!-- Lines -->
        <div class="lines-list">
          <template v-if="cartStore.isRefreshing && !hasLoadedItems">
            <div v-for="i in Math.max(1, cartStore.items.length)" :key="i" class="line skeleton-line-row">
              <div class="im skeleton-box"></div>
              <div class="line-info">
                <div class="skeleton-bar title-bar"></div>
                <div class="skeleton-bar sub-bar"></div>
              </div>
            </div>
          </template>

          <template v-else>
            <div
              v-for="item in cartStore.items"
              :key="item.lineId"
              class="line"
            >
              <template v-if="item.product">
                <div class="im">
                  <ProductIllustration
                    :type="item.product?.type"
                    :color="item.product?.color"
                    :image="item.product?.image"
                    :width="70"
                    :alt="item.product?.name"
                  />
                </div>

                <div class="line-info">
                  <b>{{ item.product?.name }}</b>
                  <small>
                    {{ item.product?.brand }} ·
                    <span v-if="doesFit(item.product, userStore.selectedBike) === true" class="line-fit">
                      يركب على {{ userStore.selectedBike }}
                    </span>
                    <span v-else-if="doesFit(item.product, userStore.selectedBike) === null" style="color: var(--mu)">
                      مقاسات متعددة
                    </span>
                    <span v-else style="color: var(--mu)">
                      مش متأكدين إنه يركب على موتوسيكلك، اسألنا
                    </span>
                  </small>
                </div>

                <div class="line-qty">
                  <QtyStepper
                    :model-value="item.qty"
                    :max="item.product?.stock !== undefined ? item.product.stock : 99"
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
              </template>

              <template v-else>
                <div class="im fallback-im">
                  <AppIcon name="box" :size="32" />
                </div>
                <div class="line-info">
                  <b>{{ item.product?.name || item.name || 'منتج' }}</b>
                  <small style="color: var(--sale);">
                    تعذر تحديث تفاصيل المنتج
                  </small>
                </div>
                <div class="line-qty">
                  <span>{{ item.qty }}x</span>
                </div>
                <b class="line-total">
                  {{ ((item.seenPrice || 0) * item.qty).toLocaleString('en') }} ج.م
                </b>
                <button
                  type="button"
                  class="line-remove"
                  @click="cartStore.removeItem(item.lineId)"
                  aria-label="حذف المنتج من السلة"
                >
                  <AppIcon name="x" :size="18" />
                </button>
              </template>
            </div>
          </template>
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

      <!-- Order Summary Component (Item 28) -->
      <aside class="sum-wrap">
        <OrderSummary :delivery-method="'home'">
          <template #actions>
            <!-- Coupon code -->
            <div class="coupon">
              <label for="cart-coupon-input" class="visually-hidden">كود الخصم</label>
              <input
                id="cart-coupon-input"
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

            <RouterLink to="/checkout" class="btn full checkout-btn">
              إتمام الشراء
            </RouterLink>

            <div class="pay-notes">
              <span v-for="(pm, idx) in storeConfig.paymentMethods" :key="pm.id">
                {{ pm.title }}<template v-if="idx < storeConfig.paymentMethods.length - 1"> · </template>
              </span>
            </div>
          </template>
        </OrderSummary>
      </aside>
    </div>

    <!-- Companion Products Row: ممكن تحتاج كمان -->
    <ProductRow title="ممكن تحتاج كمان" :products="companionProducts" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { api } from '@/api/index.js'
import { useCartStore } from '@/stores/cart.js'
import { useUserStore } from '@/stores/user.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import QtyStepper from '@/components/common/QtyStepper.vue'
import ProductIllustration from '@/components/common/ProductIllustration.vue'
import ProductRow from '@/components/common/ProductRow.vue'
import OrderSummary from '@/components/common/OrderSummary.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import AppIcon from '@/components/common/AppIcon.vue'
import { doesFit } from '@/utils/fit.js'

const cartStore = useCartStore()
const userStore = useUserStore()
const couponInput = ref('')
const allProducts = ref([])

onMounted(async () => {
  try {
    allProducts.value = await api.getProducts({ bestSeller: true, perPage: 5 })
  } catch (e) {}
  await cartStore.refreshCart()
})

const breadcrumbItems = [
  { label: 'الرئيسية', to: '/' },
  { label: 'السلة' }
]

const hasLoadedItems = computed(() => {
  return cartStore.items.some((i) => !!i.product)
})

const getItemPrice = (item) => {
  return item.selectedOption?.price || item.product?.price || item.seenPrice || 0
}

const applyCoupon = () => {
  if (couponInput.value.trim()) {
    cartStore.applyCoupon(couponInput.value)
  }
}

const companionProducts = computed(() => {
  return allProducts.value.slice(0, 5)
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

.cart-alerts {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin: 12px 0 6px;
}

.cart-alert-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  line-height: 1.4;
}

.cart-alert-item.info {
  background: var(--sf);
  color: var(--ink);
  border: 1px solid var(--ln);
}

.cart-alert-item.warning {
  background: #fdf2f2;
  color: #9e2b25;
  border: 1px solid #f8d7da;
}

.alert-text {
  flex: 1;
}

.alert-close {
  background: none;
  border: none;
  color: inherit;
  opacity: 0.6;
  cursor: pointer;
  display: grid;
  place-items: center;
  padding: 2px;
}

.alert-close:hover {
  opacity: 1;
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

.skeleton-line-row {
  opacity: 0.7;
}

.skeleton-box {
  background: linear-gradient(90deg, var(--sf) 25%, var(--sf2) 50%, var(--sf) 75%);
  background-size: 200% 100%;
  animation: skel-shimmer 1.5s infinite;
}

.skeleton-bar {
  background: linear-gradient(90deg, var(--sf) 25%, var(--sf2) 50%, var(--sf) 75%);
  background-size: 200% 100%;
  animation: skel-shimmer 1.5s infinite;
  border-radius: 4px;
}

.title-bar {
  width: 60%;
  height: 18px;
  margin-bottom: 8px;
}

.sub-bar {
  width: 40%;
  height: 13px;
}

@keyframes skel-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
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
  text-align: end;
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

.cart-refresh-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  background: var(--sf);
  border: 1px solid var(--sale);
  border-radius: var(--radius-card);
  padding: 12px 16px;
  margin-bottom: 16px;
}

.refresh-err-msg {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  font-weight: 500;
  color: var(--sale);
}

.fallback-im {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--sf);
  border-radius: 8px;
  color: var(--mu);
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
}

.coupon-input:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: 2px;
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
    position: relative;
  }
  .line-qty {
    grid-column: 2;
  }
  .line-total {
    grid-column: 3;
    text-align: end;
  }
  .line-remove {
    position: absolute;
    top: 10px;
    inset-inline-end: 10px;
  }
}
</style>
