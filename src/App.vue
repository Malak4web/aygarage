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
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, RouterLink, RouterView } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import AnnouncementBar from '@/components/layout/AnnouncementBar.vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import CategoryNav from '@/components/layout/CategoryNav.vue'
import MobileDrawer from '@/components/layout/MobileDrawer.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const route = useRoute()
const isDrawerOpen = ref(false)

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
