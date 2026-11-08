<template>
  <div class="checkout-page">
    <!-- Steps Indicator -->
    <div class="steps in">
      <RouterLink to="/cart" class="step done">
        <i>✓</i>
        <span>السلة</span>
      </RouterLink>
      <hr />
      <span class="step on">
        <i>2</i>
        <span>التوصيل والدفع</span>
      </span>
      <hr />
      <span class="step">
        <i>3</i>
        <span>تأكيد الطلب</span>
      </span>
    </div>

    <!-- Main Checkout Grid -->
    <form class="cart in" @submit.prevent="handleConfirmOrder">
      <!-- Left Column: Forms -->
      <div class="checkout-forms">
        <!-- 1. Delivery Address -->
        <div class="box">
          <div class="box-head">
            <h3>بيانات التوصيل</h3>
            <button
              v-if="userStore.user?.address"
              type="button"
              class="saved-addr-link"
              @click="populateSavedAddress"
            >
              عنوان محفوظ
            </button>
          </div>

          <div class="fl">
            <div class="fld">
              <FormField
                id="co-name"
                label="الاسم بالكامل"
                v-model="customerName"
                required
              />
            </div>

            <div class="fld">
              <FormField
                id="co-phone"
                label="رقم الموبايل"
                type="tel"
                dir="ltr"
                v-model="customerPhone"
                required
              />
            </div>

            <div class="fld">
              <label for="co-gov">المحافظة</label>
              <div class="sel-input-wrap">
                <select id="co-gov" v-model="governorate" class="form-select">
                  <option v-for="gov in storeConfig.governorates" :key="gov.name" :value="gov.name">
                    {{ gov.name }}
                  </option>
                </select>
                <AppIcon name="down" :size="14" class="sel-arr" />
              </div>
            </div>

            <div class="fld">
              <label for="co-area">المنطقة</label>
              <div class="sel-input-wrap">
                <select id="co-area" v-model="area" class="form-select">
                  <option v-for="a in availableAreas" :key="a" :value="a">
                    {{ a }}
                  </option>
                </select>
                <AppIcon name="down" :size="14" class="sel-arr" />
              </div>
            </div>

            <div class="fld span-2">
              <FormField
                id="co-addr"
                label="العنوان بالتفصيل"
                v-model="addressDetails"
                :required="deliveryMethod !== 'pickup'"
              />
            </div>
          </div>
        </div>

        <!-- 2. Delivery Method (RadioCard Item 22) -->
        <div class="box">
          <h3>طريقة الاستلام</h3>
          <div class="rad-group">
            <RadioCard
              name="delivery"
              v-model="deliveryMethod"
              value="home"
              title="توصيل للبيت"
              :desc="storeConfig.deliveryEstimate"
              :badge="cartStore.isFreeDelivery ? 'مجاناً' : `${storeConfig.deliveryFee} ج.م`"
            />
            <RadioCard
              name="delivery"
              v-model="deliveryMethod"
              value="pickup"
              title="استلام من الجراج"
              :desc="storeConfig.pickupTimes"
              badge="مجاناً"
            />
          </div>
        </div>

        <!-- 3. Payment Method (RadioCard Item 22) -->
        <div class="box">
          <h3>طريقة الدفع</h3>
          <div class="rad-group">
            <RadioCard
              v-for="pm in filteredPaymentMethods"
              :key="pm.id"
              name="payment"
              v-model="paymentMethod"
              :value="pm.id"
              :title="pm.title"
              :desc="pm.desc"
            />
          </div>
        </div>
      </div>

      <!-- Right Column: Order Summary (Item 4) -->
      <aside class="sum">
        <OrderSummary :delivery-method="deliveryMethod">
          <template #items>
            <!-- Mini items list -->
            <div class="mini-list">
              <div
                v-for="(item, idx) in cartStore.items"
                :key="item.lineId"
                class="mini-wrap"
              >
                <div class="mini">
                  <template v-if="item.product">
                    <div class="im">
                      <ProductIllustration
                        :type="item.product?.type"
                        :color="item.product?.color"
                        :image="item.product?.image"
                        :width="40"
                        :alt="item.product?.name"
                      />
                      <span class="mini-qty">{{ item.qty }}</span>
                    </div>
                    <span class="mini-name">{{ item.product?.name }}</span>
                    <b class="mini-price">
                      {{ (((item.selectedOption?.price || item.product?.price || item.seenPrice || 0) * item.qty)).toLocaleString('en') }}
                    </b>
                  </template>
                  <template v-else>
                    <div class="im skeleton-box" style="width: 40px; height: 40px; border-radius: 6px; background: var(--sf);">
                      <span class="mini-qty">{{ item.qty }}</span>
                    </div>
                    <span class="mini-name" style="color: var(--mu);">جاري التحميل...</span>
                    <b class="mini-price">
                      {{ (((item.seenPrice || 0) * item.qty)).toLocaleString('en') }}
                    </b>
                  </template>
                </div>
                <!-- Line Stock Error & Action Button -->
                <div v-if="lineErrors[idx]" class="line-stock-error">
                  <span class="stock-err-msg">{{ lineErrors[idx].message }}</span>
                  <button
                    v-if="lineErrors[idx].availableStock > 0"
                    type="button"
                    class="btn-fix-stock"
                    @click="fixLineQty(item.lineId, idx, lineErrors[idx].availableStock)"
                  >
                    عدّل الكمية للمتاح ({{ lineErrors[idx].availableStock }})
                  </button>
                  <button
                    v-else
                    type="button"
                    class="btn-fix-stock remove"
                    @click="removeLineItem(item.lineId, idx)"
                  >
                    شيل من السلة
                  </button>
                </div>
              </div>
            </div>
          </template>

          <template #actions>
            <div v-if="submitError" class="submit-error">
              {{ submitError }}
            </div>

            <button
              type="submit"
              class="btn full confirm-btn"
              :disabled="isSubmitting || cartStore.items.length === 0"
            >
              {{ isSubmitting ? 'جاري التأكيد...' : 'تأكيد الطلب' }}
            </button>

            <div class="agree-text">
              بالضغط على تأكيد الطلب انت موافق على شروط البيع وسياسة الاسترجاع.
            </div>
          </template>
        </OrderSummary>
      </aside>
    </form>
    <div style="height: 48px"></div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { useCartStore } from '@/stores/cart.js'
import { useUserStore } from '@/stores/user.js'
import { api } from '@/api/index.js'
import ProductIllustration from '@/components/common/ProductIllustration.vue'
import OrderSummary from '@/components/common/OrderSummary.vue'
import FormField from '@/components/common/FormField.vue'
import RadioCard from '@/components/common/RadioCard.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const router = useRouter()
const route = useRoute()
const cartStore = useCartStore()
const userStore = useUserStore()

onMounted(async () => {
  await cartStore.refreshCart()
  if (cartStore.items.length === 0) {
    router.replace('/cart')
  }
})

const customerName = ref(
  (userStore.user?.name && !userStore.user.name.startsWith('عميل'))
    ? userStore.user.name
    : ''
)
const customerPhone = ref(userStore.user?.phone || '')
const governorate = ref(userStore.user?.address?.governorate || 'القاهرة')
const area = ref(userStore.user?.address?.area || 'مدينة نصر')
const addressDetails = ref(userStore.user?.address?.street || '')

const availableAreas = computed(() => {
  const g = storeConfig.governorates.find((item) => item.name === governorate.value)
  return g ? g.areas : ['مدينة نصر', 'مصر الجديدة', 'المعادي']
})

watch(governorate, () => {
  if (availableAreas.value.length > 0 && !availableAreas.value.includes(area.value)) {
    area.value = availableAreas.value[0]
  }
})

const deliveryMethod = ref('home')
const paymentMethod = ref('cod')
const isSubmitting = ref(false)
const submitError = ref('')
const lineErrors = ref({})

const filteredPaymentMethods = computed(() => {
  if (deliveryMethod.value === 'home') {
    return (storeConfig.paymentMethods || []).filter((pm) => pm.id === 'cod')
  }
  return (storeConfig.paymentMethods || []).filter((pm) => pm.id === 'pickup')
})

watch(
  deliveryMethod,
  (val) => {
    if (val === 'home') paymentMethod.value = 'cod'
    else if (val === 'pickup') paymentMethod.value = 'pickup'
  },
  { immediate: true }
)

const fixLineQty = (lineId, idx, available) => {
  cartStore.updateQty(lineId, available)
  delete lineErrors.value[idx]
}

const removeLineItem = (lineId, idx) => {
  cartStore.removeItem(lineId)
  delete lineErrors.value[idx]
  if (cartStore.items.length === 0) {
    router.replace('/cart')
  }
}

const populateSavedAddress = () => {
  if (userStore.user?.address) {
    customerName.value = userStore.user.name || ''
    customerPhone.value = userStore.user.phone || ''
    governorate.value = userStore.user.address.governorate || 'القاهرة'
    area.value = userStore.user.address.area || 'مدينة نصر'
    addressDetails.value = userStore.user.address.street || ''
  }
}

watch(
  () => userStore.user,
  (u) => {
    if (u) {
      if (!customerName.value && u.name && !u.name.startsWith('عميل')) {
        customerName.value = u.name
      }
      if (!customerPhone.value && u.phone) {
        customerPhone.value = u.phone
      }
      if (u.address) {
        if (u.address.governorate) governorate.value = u.address.governorate
        if (u.address.area) area.value = u.address.area
        if (!addressDetails.value && u.address.street) addressDetails.value = u.address.street
      }
    }
  },
  { immediate: true }
)

const handleConfirmOrder = async () => {
  if (cartStore.items.length === 0) {
    return router.replace('/cart')
  }

  // Check login requirement per Item 3
  if (!userStore.isAuthenticated) {
    return router.push({
      path: '/login',
      query: { redirect: route.fullPath }
    })
  }

  if (isSubmitting.value) return
  isSubmitting.value = true
  submitError.value = ''
  lineErrors.value = {}

  try {
    const orderData = {
      items: cartStore.items.map((i) => ({
        productId: i.product?.id || i.slug,
        slug: i.slug || i.product?.slug,
        optionCode: i.optionCode || i.selectedOption?.code || null,
        qty: i.qty
      })),
      name: customerName.value,
      phone: customerPhone.value,
      governorate: governorate.value,
      area: area.value,
      street: deliveryMethod.value === 'pickup' ? (addressDetails.value || 'استلام من مقر الجراج') : addressDetails.value,
      deliveryMethod: deliveryMethod.value,
      paymentMethod: paymentMethod.value,
      couponCode: cartStore.couponCode || null
    }

    const createdOrder = await api.createOrder(orderData)
    if (typeof userStore.addOrder === 'function') {
      userStore.addOrder(createdOrder)
    }
    cartStore.clearCart()
    router.push(`/order/${createdOrder.number || createdOrder.id}`)
  } catch (error) {
    if (error?.status === 422 && error.errors) {
      const newErrors = {}
      for (const [key, msg] of Object.entries(error.errors)) {
        const match = key.match(/^items\.(\d+)/)
        if (match) {
          const idx = parseInt(match[1])
          const strMsg = Array.isArray(msg) ? msg[0] : String(msg)
          const availMatch = strMsg.match(/المتاح:\s*(\d+)/)
          const availableStock = availMatch ? parseInt(availMatch[1]) : 0
          newErrors[idx] = {
            message: strMsg,
            availableStock
          }
        }
      }
      lineErrors.value = newErrors
      if (Object.keys(newErrors).length === 0) {
        submitError.value = error.message
      } else {
        submitError.value = ''
      }
    } else {
      submitError.value = error?.message || 'حدث خطأ أثناء تأكيد الطلب، يرجى المحاولة مرة أخرى.'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.steps {
  display: flex;
  gap: 10px;
  align-items: center;
  padding-top: 18px;
  font-size: 14px;
  color: var(--mu);
}

.step {
  display: flex;
  gap: 8px;
  align-items: center;
  text-decoration: none;
  color: var(--mu);
}

.step i {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  border: 1.5px solid #c8c8c8;
  display: grid;
  place-items: center;
  font-style: normal;
  font-size: 12px;
}

.step.done i {
  background: var(--ink);
  border-color: var(--ink);
  color: #ffffff;
}

.step.on {
  color: var(--ink);
  font-weight: 700;
}

.step.on i {
  border-color: var(--ink);
}

.steps hr {
  flex: 0 0 60px;
  border: 0;
  border-top: 1px solid var(--ln);
}

.cart {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 28px;
  padding-top: 18px;
}

.box {
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  padding: 22px;
  margin-bottom: 16px;
  background: #ffffff;
}

.box-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.box h3 {
  margin: 0;
  font-size: 17px;
}

.saved-addr-link {
  font-size: 14px;
  font-weight: 400;
  text-decoration: underline;
  color: var(--ink);
  background: none;
  border: none;
  cursor: pointer;
}

.fl {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.fld {
  display: flex;
  flex-direction: column;
}

.fld.span-2 {
  grid-column: span 2;
}

.fld label {
  display: block;
  font-size: 13px;
  color: var(--mu);
  margin-bottom: 4px;
}

.form-input, .sel-input-wrap {
  height: 46px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 12px;
  font-size: 14.5px;
  background: #ffffff;
  color: var(--ink);
  display: flex;
  align-items: center;
}

.form-input:focus-visible,
.sel-input-wrap:focus-within {
  outline: 2px solid var(--ink);
  outline-offset: 2px;
}

.ltr-input {
  direction: ltr;
  text-align: end;
}

.sel-input-wrap {
  position: relative;
  padding: 0 12px;
}

.form-select {
  width: 100%;
  appearance: none;
  border: none;
  background: transparent;
  font-size: 14.5px;
  color: var(--ink);
  cursor: pointer;
  padding-inline-end: 20px;
}

.form-select:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: 2px;
}

.sel-arr {
  position: absolute;
  inset-inline-end: 12px;
  pointer-events: none;
  color: var(--mu);
}

/* Radio Cards */
.rad {
  display: flex;
  gap: 12px;
  align-items: center;
  border: 1px solid var(--ln);
  border-radius: 12px;
  padding: 14px 16px;
  margin-bottom: 10px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.rad.on {
  border: 2px solid var(--ink);
}

.hidden-radio {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.rad i {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 1.5px solid #bdbdbd;
  flex: none;
  transition: all 0.15s ease;
}

.rad.on i {
  border: 6px solid var(--ink);
  background: #ffffff;
}

.rad-body b {
  display: block;
  font-size: 14.5px;
}

.rad-body small {
  color: var(--mu);
  font-size: 13px;
}

.rad .r {
  margin-inline-start: auto;
  font-weight: 700;
}

/* Summary Box */
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

.mini-list {
  display: flex;
  flex-direction: column;
}

.mini-wrap {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 6px 0;
  border-bottom: 1px solid var(--ln);
}

.mini {
  display: grid;
  grid-template-columns: 56px 1fr auto;
  gap: 12px;
  align-items: center;
  font-size: 13.5px;
}

.line-stock-error {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  background: #fdf2f2;
  border: 1px solid #f8b4b4;
  color: #c81e1e;
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 12px;
}

.stock-err-msg {
  font-weight: 600;
  flex: 1;
}

.btn-fix-stock {
  background: #ffffff;
  border: 1px solid #c81e1e;
  color: #c81e1e;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
}

.btn-fix-stock:hover {
  background: #c81e1e;
  color: #ffffff;
}

.mini .im {
  aspect-ratio: 1;
  border-radius: 8px;
  background: var(--sf);
  display: grid;
  place-items: center;
  position: relative;
}

.mini-qty {
  position: absolute;
  top: -6px;
  inset-inline-end: -6px;
  background: var(--mu);
  color: #ffffff;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 11px;
  display: grid;
  place-items: center;
  font-weight: 700;
}

.mini-name {
  line-height: 1.4;
}

.mini-price {
  font-variant-numeric: tabular-nums;
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

.confirm-btn {
  height: 52px;
  margin-top: 14px;
}

.submit-error {
  color: var(--sale);
  font-size: 13.5px;
  margin-top: 10px;
  text-align: center;
}

.agree-text {
  font-size: 12.5px;
  color: var(--mu);
  margin-top: 10px;
  text-align: center;
  line-height: 1.5;
}

@media (max-width: 900px) {
  .cart {
    grid-template-columns: 1fr;
  }
  .fl {
    grid-template-columns: 1fr;
  }
  .fld.span-2 {
    grid-column: span 1;
  }
}
</style>
