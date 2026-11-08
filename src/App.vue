<template>
  <div class="store-app">
    <!-- Announcement Bar (shown on all pages except checkout) -->
    <AnnouncementBar v-if="!isCheckoutPage" />

    <!-- Standard Header -->
    <AppHeader v-if="!isCheckoutPage" @toggle-drawer="isDrawerOpen = true" />

    <!-- Minimal Checkout Header -->
    <header v-else class="checkout-header">
      <div class="checkout-header-inner">
        <RouterLink to="/" class="logo">
          <span class="logo-title">{{ storeConfig.name }}</span>
          <small class="logo-sub">{{ storeConfig.tagline }}</small>
        </RouterLink>
        <span class="header-sp"></span>
        <span class="secure-badge">
          <AppIcon name="shield" :size="18" />
          <span>دفع آمن</span>
        </span>
      </div>
    </header>

    <!-- Category Nav (hidden on checkout & order-success) -->
    <CategoryNav
      v-if="!isCheckoutPage && !isOrderSuccessPage"
      @open-drawer="isDrawerOpen = true"
    />

    <!-- Main router view -->
    <main class="main-content">
      <RouterView />
    </main>

    <!-- Mobile Drawer -->
    <MobileDrawer :is-open="isDrawerOpen" @close="isDrawerOpen = false" />

    <!-- Footer (hidden on checkout) -->
    <AppFooter v-if="!isCheckoutPage" />

    <!-- Global App Toast -->
    <AppToast />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, RouterLink, RouterView } from 'vue-router'
import { api } from '@/api'
import { storeConfig, updateStoreConfig } from '@/config/store.js'
import { useCartStore } from '@/stores/cart.js'
import { useUserStore } from '@/stores/user.js'
import { useFavoritesStore } from '@/stores/favorites.js'
import AnnouncementBar from '@/components/layout/AnnouncementBar.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import CategoryNav from '@/components/layout/CategoryNav.vue'
import MobileDrawer from '@/components/layout/MobileDrawer.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import AppToast from '@/components/common/AppToast.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const cartStore = useCartStore()
const userStore = useUserStore()
const favStore = useFavoritesStore()

onMounted(async () => {
  try {
    const live = await api.getSettings()
    if (live) updateStoreConfig(live)
  } catch (e) {
    // Keep fallback values in storeConfig
  }
  userStore.fetchUser()
  favStore.syncFavorites()
  cartStore.refreshCart()
})

const route = useRoute()
const isDrawerOpen = ref(false)

watch(
  () => route.fullPath,
  () => {
    isDrawerOpen.value = false
  }
)

const isCheckoutPage = computed(() => route.path === '/checkout')
const isOrderSuccessPage = computed(() => route.name === 'order-success')
</script>

<style scoped>
.store-app {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: var(--bg);
  color: var(--ink);
}

.main-content {
  flex: 1;
}

.checkout-header {
  border-bottom: 1px solid var(--ln);
  padding: 18px var(--page-pad);
  background: #ffffff;
}

.checkout-header-inner {
  display: flex;
  align-items: center;
  max-width: 1280px;
  margin: 0 auto;
}

.logo {
  font-weight: 800;
  font-size: 24px;
  line-height: 1;
  white-space: nowrap;
  color: var(--ink);
  text-decoration: none;
}

.logo-title {
  display: block;
}

.logo-sub {
  display: block;
  font-weight: 400;
  font-size: 11px;
  color: var(--mu);
  letter-spacing: 0.25em;
  margin-top: 5px;
}

.header-sp {
  flex: 1;
}

.secure-badge {
  display: flex;
  gap: 8px;
  align-items: center;
  font-size: 14px;
  color: var(--mu);
}
</style>
