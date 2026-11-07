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
        <RouterLink to="/account" class="btn">
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
import { RouterLink } from 'vue-router'
import AppIcon from '@/components/common/AppIcon.vue'
</script>

<style scoped>
.order-success-page {
  padding-top: 40px;
  padding-bottom: 60px;
  display: flex;
  justify-content: center;
}

.success-card {
  max-width: 560px;
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

.order-perks-box {
  background: var(--sf);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 28px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  text-align: right;
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
