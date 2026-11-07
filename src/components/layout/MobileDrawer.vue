<template>
  <div v-if="isOpen" class="drawer-container">
    <div class="drawer-backdrop" @click="$emit('close')"></div>
    <aside
      class="mobile-drawer"
      ref="drawerRef"
      role="dialog"
      aria-modal="true"
      aria-label="القائمة الرئيسية"
    >
      <div class="drawer-header">
        <div class="drawer-user" v-if="userStore.isAuthenticated">
          <AppIcon name="user" :size="24" />
          <div>
            <b>{{ userStore.user?.name || 'حسابي' }}</b>
            <small>{{ userStore.user?.phone || '' }}</small>
          </div>
        </div>
        <div class="drawer-user" v-else>
          <RouterLink to="/login" class="btn sm full" @click="$emit('close')">
            تسجيل الدخول / حساب جديد
          </RouterLink>
        </div>

        <button type="button" class="close-btn" @click="$emit('close')" aria-label="إغلاق القائمة">
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
  </div>
</template>

<script setup>
import { ref, toRef, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { api } from '@/api/index.js'
import { useUserStore } from '@/stores/user.js'
import { useCartStore } from '@/stores/cart.js'
import { useModalTrap } from '@/utils/modal.js'
import AppIcon from '@/components/common/AppIcon.vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const userStore = useUserStore()
const cartStore = useCartStore()
const drawerRef = ref(null)
const categories = ref([])

onMounted(async () => {
  try {
    categories.value = await api.getCategories()
  } catch (e) {}
})

const isOpenRef = toRef(props, 'isOpen')
useModalTrap(isOpenRef, drawerRef, () => emit('close'))
</script>

<style scoped>
.drawer-container {
  position: fixed;
  inset: 0;
  z-index: 1000;
}

.drawer-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(2px);
  z-index: 1001;
}

.mobile-drawer {
  position: fixed;
  top: 0;
  bottom: 0;
  inset-inline-start: 0;
  width: 82%;
  max-width: 340px;
  background: #ffffff;
  z-index: 1002;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
  box-shadow: 0 0 24px rgba(0, 0, 0, 0.2);
}

.drawer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--ln);
}

.drawer-user {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
}

.drawer-user b {
  display: block;
  font-size: 14px;
}

.drawer-user small {
  color: var(--mu);
  font-size: 12px;
}

.close-btn {
  background: none;
  border: none;
  padding: 4px;
  cursor: pointer;
  color: var(--mu);
}

.drawer-bike-section {
  padding: 14px 20px;
  background: var(--sf);
  border-bottom: 1px solid var(--ln);
}

.drawer-bike-section small {
  display: block;
  font-weight: 700;
  color: var(--mu);
  margin-bottom: 8px;
}

.bike-pills {
  display: flex;
  gap: 6px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.bike-pill {
  padding: 4px 10px;
  border-radius: 999px;
  border: 1px solid var(--ln);
  background: #ffffff;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
  cursor: pointer;
}

.bike-pill.active {
  background: var(--ink);
  color: #ffffff;
  border-color: var(--ink);
}

.drawer-nav {
  padding: 16px 0;
  flex: 1;
}

.nav-group-title {
  padding: 8px 20px;
  font-size: 11.5px;
  font-weight: 800;
  color: var(--mu);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.drawer-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 11px 20px;
  color: var(--ink);
  text-decoration: none;
  font-size: 14.5px;
  font-weight: 500;
  border-bottom: 1px solid rgba(0, 0, 0, 0.03);
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
  font-size: 12px;
}

.drawer-footer {
  padding: 16px 20px;
  background: var(--sf);
  border-top: 1px solid var(--ln);
  font-size: 13px;
  color: var(--mu);
}

.drawer-footer div {
  font-weight: 700;
  color: var(--ink);
  margin-bottom: 2px;
}
</style>
