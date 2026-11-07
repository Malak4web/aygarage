<template>
  <div v-if="isOpen" class="drawer-backdrop" @click="$emit('close')"></div>
  <aside class="mobile-drawer" :class="{ open: isOpen }" aria-label="القائمة المتنقلة">
    <div class="drawer-header">
      <div class="drawer-user" v-if="userStore.isAuthenticated">
        <AppIcon name="user" :size="24" />
        <div>
          <b>{{ userStore.user?.name || 'كريم محمود' }}</b>
          <small>{{ userStore.user?.phone || '010 1234 5678' }}</small>
        </div>
      </div>
      <div class="drawer-user" v-else>
        <RouterLink to="/login" class="btn sm full" @click="$emit('close')">
          تسجيل الدخول / حساب جديد
        </RouterLink>
      </div>

      <button type="button" class="close-btn" @click="$emit('close')" aria-label="إغلاق">
        <AppIcon name="x" :size="22" />
      </button>
    </div>

    <!-- Active bike switcher -->
    <div class="drawer-bike-section">
      <small>موتوسيكلك الحالي:</small>
      <div class="bike-pills">
        <button
          v-for="model in storeConfig.models"
          :key="model"
          type="button"
          class="bike-pill"
          :class="{ active: userStore.selectedBike === model }"
          @click="userStore.setSelectedBike(model)"
        >
          {{ model }}
        </button>
      </div>
    </div>

    <div class="drawer-nav">
      <div class="nav-group-title">الأقسام</div>
      <RouterLink
        v-for="cat in categories"
        :key="cat.slug"
        :to="`/c/${cat.slug}`"
        class="drawer-link"
        @click="$emit('close')"
      >
        <span>{{ cat.name }}</span>
        <small>{{ cat.count }} منتج</small>
      </RouterLink>

      <div class="nav-group-title">خدمات الجراج</div>
      <RouterLink to="/bikes" class="drawer-link" @click="$emit('close')">
        <span>موتوسيكلات مستعملة مفحوصة</span>
      </RouterLink>
      <RouterLink to="/booking" class="drawer-link" @click="$emit('close')">
        <span>حجز ميعاد صيانة</span>
      </RouterLink>
      <RouterLink to="/c/oils?offer=true" class="drawer-link highlight" @click="$emit('close')">
        <span>عروض وتخفيضات الأسبوع</span>
      </RouterLink>

      <div class="nav-group-title">الحساب والطلبات</div>
      <RouterLink to="/account" class="drawer-link" @click="$emit('close')">
        <span>لوحة التحكم وحسابي</span>
      </RouterLink>
      <RouterLink to="/cart" class="drawer-link" @click="$emit('close')">
        <span>سلة المشتريات ({{ cartStore.count }})</span>
      </RouterLink>
    </div>

    <div class="drawer-footer">
      <div>واتساب: {{ storeConfig.phone }}</div>
      <small>استلام مجاني من مقر الجراج</small>
    </div>
  </aside>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { categories } from '@/data/categories.js'
import { useUserStore } from '@/stores/user.js'
import { useCartStore } from '@/stores/cart.js'
import AppIcon from '@/components/common/AppIcon.vue'

defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

defineEmits(['close'])

const userStore = useUserStore()
const cartStore = useCartStore()
</script>

<style scoped>
.drawer-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  z-index: 998;
}

.mobile-drawer {
  position: fixed;
  top: 0;
  bottom: 0;
  inset-inline-start: 0;
  width: 320px;
  max-width: 85vw;
  background: #ffffff;
  z-index: 999;
  transform: translateX(100%);
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

[dir="rtl"] .mobile-drawer {
  transform: translateX(100%);
}

.mobile-drawer.open {
  transform: translateX(0);
}

.drawer-header {
  padding: 16px;
  border-bottom: 1px solid var(--ln);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.drawer-user {
  display: flex;
  align-items: center;
  gap: 10px;
}

.drawer-user b {
  display: block;
  font-size: 15px;
}

.drawer-user small {
  display: block;
  color: var(--mu);
}

.close-btn {
  padding: 6px;
  color: var(--ink);
}

.drawer-bike-section {
  padding: 12px 16px;
  background: var(--sf);
  border-bottom: 1px solid var(--ln);
}

.drawer-bike-section small {
  display: block;
  margin-bottom: 6px;
  color: var(--mu);
}

.bike-pills {
  display: flex;
  gap: 6px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.bike-pill {
  padding: 4px 10px;
  border-radius: var(--radius-pill);
  background: #ffffff;
  border: 1px solid var(--ln);
  font-size: 12px;
  white-space: nowrap;
}

.bike-pill.active {
  background: var(--ink);
  color: #ffffff;
  border-color: var(--ink);
  font-weight: 700;
}

.drawer-nav {
  flex: 1;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-group-title {
  font-size: 12px;
  font-weight: 700;
  color: var(--mu);
  margin: 12px 0 4px;
  text-transform: uppercase;
}

.drawer-link {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 14.5px;
  color: var(--ink);
}

.drawer-link:hover {
  background: var(--sf);
}

.drawer-link.highlight {
  color: var(--sale);
  font-weight: 700;
}

.drawer-link small {
  color: var(--mu);
}

.drawer-footer {
  padding: 16px;
  border-top: 1px solid var(--ln);
  background: var(--sf);
  font-size: 13px;
  color: var(--mu);
}
</style>
