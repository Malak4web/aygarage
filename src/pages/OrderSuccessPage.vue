<template>
  <div class="order-success-page in">
    <div class="success-card">
      <div class="icon-circle">
        <AppIcon name="check" :size="36" :stroke-width="2.5" />
      </div>

      <h1>تم استلام طلبك بنجاح!</h1>
      <p class="order-num">رقم الطلب: <b>#{{ $route.params.id }}</b></p>
      <p class="order-msg">
        شكراً لثقتك في جراج أبو يوسف. هيوصلك رسالة تأكيد بتفاصيل الشحن وميعاد التوصيل على الواتساب.
      </p>

      <!-- Order Details if loaded -->
      <div v-if="order" class="order-details-box">
        <h3>تفاصيل الطلب</h3>
        <div class="order-items-list">
          <div v-for="item in order.items" :key="item.lineId || item.productId" class="order-item-row">
            <div class="order-item-info">
              <b>{{ item.product.name }}</b>
              <small>{{ item.qty }} × {{ (item.selectedOption?.price || item.product.price).toLocaleString('en') }} ج.م</small>
            </div>
            <span class="order-item-total">
              {{ ((item.selectedOption?.price || item.product.price) * item.qty).toLocaleString('en') }} ج.م
            </span>
          </div>
        </div>

        <div class="order-summary-breakdown">
          <div class="row">
            <span>المنتجات</span>
            <b>{{ order.subtotalNum?.toLocaleString('en') || 0 }} ج.م</b>
          </div>
          <div v-if="order.discountNum" class="row">
            <span>الخصم</span>
            <b class="disc">− {{ order.discountNum.toLocaleString('en') }} ج.م</b>
          </div>
          <div class="row">
            <span>التوصيل</span>
            <b>{{ order.deliveryFeeNum === 0 ? 'مجاناً' : `${order.deliveryFeeNum} ج.م` }}</b>
          </div>
          <div class="row total-row">
            <span>الإجمالي</span>
            <b>{{ order.total }}</b>
          </div>
        </div>

        <div class="delivery-info">
          <div>طريقة الاستلام: <b>{{ order.deliveryMethod === 'pickup' ? 'استلام من الجراج' : 'توصيل للبيت' }}</b></div>
          <div v-if="order.customer?.address">العنوان: <b>{{ order.customer.address }}، {{ order.customer.area }}</b></div>
        </div>
      </div>

      <div class="order-perks-box">
        <div class="perk-row">
          <AppIcon name="truck" :size="20" />
          <span>التوصيل المتوقع: <b>خلال 2-4 أيام عمل</b></span>
        </div>
        <div class="perk-row">
          <AppIcon name="shield" :size="20" />
          <span>ضمان أصالة القطع واسترجاع 14 يوم</span>
        </div>
      </div>

      <div class="success-actions">
        <RouterLink to="/account?tab=orders" class="btn">
          تابع طلبك في حسابي
        </RouterLink>
        <RouterLink to="/" class="btn o">
          العودة للرئيسية
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { api } from '@/api/index.js'
import AppIcon from '@/components/common/AppIcon.vue'

const route = useRoute()
const order = ref(null)

onMounted(async () => {
  try {
    order.value = await api.getOrder(route.params.id)
  } catch (e) {
    console.warn('Could not fetch order:', e)
  }
})
</script>

<style scoped>
.order-success-page {
  padding-top: 40px;
  padding-bottom: 60px;
  display: flex;
  justify-content: center;
}

.success-card {
  max-width: 580px;
  width: 100%;
  background: #ffffff;
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  padding: 40px 32px;
  text-align: center;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
}

.icon-circle {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: var(--oksoft);
  color: var(--ok);
  display: grid;
  place-items: center;
  margin: 0 auto 20px;
}

.success-card h1 {
  font-size: 26px;
  font-weight: 800;
  margin: 0 0 8px;
}

.order-num {
  font-size: 16px;
  color: var(--mu);
  margin: 0 0 12px;
}

.order-num b {
  color: var(--ink);
}

.order-msg {
  font-size: 14.5px;
  color: var(--mu);
  line-height: 1.7;
  margin: 0 0 24px;
}

.order-details-box {
  background: var(--sf);
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 20px;
  margin-bottom: 24px;
  text-align: start;
}

.order-details-box h3 {
  margin: 0 0 14px;
  font-size: 16px;
  border-bottom: 1px solid var(--ln);
  padding-bottom: 8px;
}

.order-items-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 14px;
}

.order-item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13.5px;
  padding-bottom: 8px;
  border-bottom: 1px dashed var(--ln);
}

.order-item-info b {
  display: block;
}

.order-item-info small {
  color: var(--mu);
}

.order-item-total {
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.order-summary-breakdown {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 14px;
  border-bottom: 1px solid var(--ln);
  padding-bottom: 12px;
  margin-bottom: 12px;
}

.order-summary-breakdown .row {
  display: flex;
  justify-content: space-between;
}

.order-summary-breakdown .disc {
  color: var(--sale);
}

.total-row {
  font-size: 16px;
  font-weight: 800;
  margin-top: 4px;
  padding-top: 6px;
  border-top: 1px solid var(--ln);
}

.delivery-info {
  font-size: 13px;
  color: var(--mu);
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.delivery-info b {
  color: var(--ink);
}

.order-perks-box {
  background: var(--sf);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 28px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  text-align: start;
}

.perk-row {
  display: flex;
  gap: 10px;
  align-items: center;
  font-size: 13.5px;
}

.success-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

.success-actions .btn {
  flex: 1;
  min-width: 180px;
}
</style>
