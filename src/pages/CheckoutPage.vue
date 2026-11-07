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
              type="button"
              class="saved-addr-link"
              @click="populateSavedAddress"
            >
              عنوان محفوظ
            </button>
          </div>

          <div class="fl">
            <div class="fld">
              <label>الاسم بالكامل</label>
              <input
                type="text"
                v-model="customerName"
                class="form-input"
                required
              />
            </div>

            <div class="fld">
              <label>رقم الموبايل</label>
              <input
                type="tel"
                v-model="customerPhone"
                dir="ltr"
                class="form-input ltr-input"
                required
              />
            </div>

            <div class="fld">
              <label>المحافظة</label>
              <div class="sel-input-wrap">
                <select v-model="governorate" class="form-select">
                  <option value="القاهرة">القاهرة</option>
                  <option value="الجيزة">الجيزة</option>
                  <option value="الإسكندرية">الإسكندرية</option>
                  <option value="الشرقية">الشرقية</option>
                </select>
                <AppIcon name="down" :size="14" class="sel-arr" />
              </div>
            </div>

            <div class="fld">
              <label>المنطقة</label>
              <div class="sel-input-wrap">
                <select v-model="area" class="form-select">
                  <option value="مدينة نصر">مدينة نصر</option>
                  <option value="مصر الجديدة">مصر الجديدة</option>
                  <option value="التجمع الخامس">التجمع الخامس</option>
                  <option value="المعادي">المعادي</option>
                </select>
                <AppIcon name="down" :size="14" class="sel-arr" />
              </div>
            </div>

            <div class="fld span-2">
              <label>العنوان بالتفصيل</label>
              <input
                type="text"
                v-model="addressDetails"
                class="form-input"
                required
              />
            </div>
          </div>
        </div>

        <!-- 2. Delivery Method -->
        <div class="box">
          <h3>طريقة الاستلام</h3>

          <label
            class="rad"
            :class="{ on: deliveryMethod === 'home' }"
          >
            <input
              type="radio"
              value="home"
              v-model="deliveryMethod"
              class="hidden-radio"
            />
            <i></i>
            <span class="rad-body">
              <b>توصيل للبيت</b>
              <small>{{ storeConfig.deliveryEstimate }}</small>
            </span>
            <span class="r">{{ cartStore.isFreeDelivery ? 'مجاناً' : `${storeConfig.deliveryFee} ج.م` }}</span>
          </label>

          <label
            class="rad"
            :class="{ on: deliveryMethod === 'pickup' }"
          >
            <input
              type="radio"
              value="pickup"
              v-model="deliveryMethod"
              class="hidden-radio"
            />
            <i></i>
            <span class="rad-body">
              <b>استلام من الجراج</b>
              <small>{{ storeConfig.pickupTimes }}</small>
            </span>
            <span class="r">مجاناً</span>
          </label>
        </div>

        <!-- 3. Payment Method -->
        <div class="box">
          <h3>طريقة الدفع</h3>

          <label
            v-for="pm in storeConfig.paymentMethods"
            :key="pm.id"
            class="rad"
            :class="{ on: paymentMethod === pm.id }"
          >
            <input
              type="radio"
              :value="pm.id"
              v-model="paymentMethod"
              class="hidden-radio"
            />
            <i></i>
            <span class="rad-body">
              <b>{{ pm.title }}</b>
              <small>{{ pm.desc }}</small>
            </span>
          </label>
        </div>
      </div>

      <!-- Right Column: Order Summary -->
      <aside class="sum">
        <h3>طلبك</h3>

        <!-- Mini items list -->
        <div class="mini-list">
          <div
            v-for="item in cartStore.items"
            :key="item.productId"
            class="mini"
          >
            <div class="im">
              <ProductIllustration
                :type="item.product.type"
                :color="item.product.color"
                :image="item.product.image"
                :width="40"
              />
              <span class="mini-qty">{{ item.qty }}</span>
            </div>
            <span class="mini-name">{{ item.product.name }}</span>
            <b class="mini-price">
              {{ ((item.selectedOption?.price || item.product.price) * item.qty).toLocaleString('en') }}
            </b>
          </div>
        </div>

        <div class="dl" style="margin-top: 14px">
          <span>المنتجات</span>
          <b>{{ cartStore.subtotal.toLocaleString('en') }} ج.م</b>
        </div>

        <div v-if="cartStore.discount > 0" class="dl">
          <span>الخصم</span>
          <b class="discount-val">− {{ cartStore.discount.toLocaleString('en') }} ج.م</b>
        </div>

        <div class="dl">
          <span>التوصيل</span>
          <b v-if="actualDeliveryFee === 0" class="free-text">مجاناً</b>
          <b v-else>{{ actualDeliveryFee }} ج.م</b>
        </div>

        <div class="dl tot">
          <span>الإجمالي</span>
          <b>{{ finalTotal.toLocaleString('en') }} ج.م</b>
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
      </aside>
    </form>
    <div style="height: 48px"></div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { useCartStore } from '@/stores/cart.js'
import { useUserStore } from '@/stores/user.js'
import { api } from '@/api/index.js'
import ProductIllustration from '@/components/common/ProductIllustration.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const router = useRouter()
const cartStore = useCartStore()
const userStore = useUserStore()

const customerName = ref(userStore.user?.name || 'كريم محمود')
const customerPhone = ref(userStore.user?.phone || '010 1234 5678')
const governorate = ref(userStore.user?.address?.governorate || 'القاهرة')
const area = ref(userStore.user?.address?.area || 'مدينة نصر')
const addressDetails = ref(userStore.user?.address?.street || '12 شارع عباس العقاد، الدور 3، شقة 7')

const deliveryMethod = ref('home')
const paymentMethod = ref('cod')
const isSubmitting = ref(false)

const actualDeliveryFee = computed(() => {
  if (deliveryMethod.value === 'pickup') return 0
  return cartStore.deliveryFee
})

const finalTotal = computed(() => {
  return Math.max(0, cartStore.subtotal - cartStore.discount + actualDeliveryFee.value)
})

const populateSavedAddress = () => {
  if (userStore.user?.address) {
    customerName.value = userStore.user.name
    customerPhone.value = userStore.user.phone
    governorate.value = userStore.user.address.governorate
    area.value = userStore.user.address.area
    addressDetails.value = userStore.user.address.street
  }
}

const handleConfirmOrder = async () => {
  if (isSubmitting.value) return
  isSubmitting.value = true

  try {
    const orderData = {
      items: [...cartStore.items],
      total: `${finalTotal.value.toLocaleString('en')} ج.م`,
      customer: {
        name: customerName.value,
        phone: customerPhone.value,
        governorate: governorate.value,
        area: area.value,
        address: addressDetails.value
      },
      deliveryMethod: deliveryMethod.value,
      paymentMethod: paymentMethod.value
    }

    const createdOrder = await api.createOrder(orderData)
    cartStore.clearCart()
    router.push(`/order/${createdOrder.id}`)
  } catch (error) {
    console.error('Failed to place order:', error)
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
  outline: none;
  display: flex;
  align-items: center;
}

.ltr-input {
  direction: ltr;
  text-align: right;
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
  outline: none;
  cursor: pointer;
  padding-left: 20px;
}

.sel-arr {
  position: absolute;
  left: 12px;
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

.mini {
  display: grid;
  grid-template-columns: 56px 1fr auto;
  gap: 12px;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid var(--ln);
  font-size: 13.5px;
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
  left: -6px;
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
