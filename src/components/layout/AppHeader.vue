<template>
  <header class="app-header">
    <div class="header-inner">
      <!-- Mobile back button on inner screens -->
      <button
        v-if="!isRoot"
        type="button"
        class="mobile-icon-btn back-btn"
        aria-label="رجوع"
        @click="goBack"
      >
        <AppIcon name="back" :size="24" :stroke-width="2" />
      </button>

      <!-- Brand Logo -->
      <RouterLink to="/" class="logo">
        <span class="logo-title">{{ storeConfig.name }}</span>
        <small class="logo-sub">{{ storeConfig.tagline }}</small>
      </RouterLink>

      <!-- Desktop Search Bar -->
      <div class="header-search">
        <SearchBar />
      </div>

      <!-- Action items -->
      <div class="header-actions">
        <!-- Bike selector (Desktop only) -->
        <div class="desktop-only">
          <BikeSelector />
        </div>

        <!-- User account (Desktop only) -->
        <RouterLink
          :to="userStore.isAuthenticated ? '/account' : '/login'"
          class="hi-link desktop-only"
        >
          <AppIcon name="user" :size="24" />
          <span>
            <small>{{ userStore.isAuthenticated ? (userStore.user?.name && !userStore.user.name.startsWith('عميل') ? ('أهلاً ' + userStore.user.name.trim().split(' ')[0]) : 'أهلاً') : 'تسجيل الدخول' }}</small>
            <b>حسابي</b>
          </span>
        </RouterLink>

        <!-- Favorites (Desktop only) -->
        <RouterLink to="/account?tab=favorites" class="hi-link desktop-only icon-only" aria-label="المفضلة">
          <AppIcon name="heart" :size="24" />
          <span v-if="favStore.count > 0" class="fav-badge">{{ favStore.count }}</span>
        </RouterLink>

        <!-- Mobile: current bike chip (opens the menu to switch) -->
        <button
          v-if="isRoot"
          type="button"
          class="bike-chip"
          aria-label="غيّر الموتوسيكل"
          @click="$emit('toggle-drawer')"
        >
          <AppIcon name="bike" :size="16" />
          <span>{{ userStore.selectedBike || 'موتوسيكلك' }}</span>
        </button>

        <!-- Mobile favorites -->
        <RouterLink v-if="isRoot" to="/account?tab=favorites" class="mobile-icon-btn mobile-fav" aria-label="المفضلة">
          <AppIcon name="heart" :size="22" />
          <span v-if="favStore.count > 0" class="fav-badge">{{ favStore.count }}</span>
        </RouterLink>

        <!-- Mobile search toggle icon button (inner screens only) -->
        <button
          v-if="!searchPinned"
          type="button"
          class="mobile-icon-btn mobile-search-toggle"
          @click="isMobileSearchOpen = !isMobileSearchOpen"
          :aria-expanded="isMobileSearchOpen"
          aria-label="البحث عن منتجات"
        >
          <AppIcon name="search" :size="24" />
        </button>

        <!-- Cart -->
        <RouterLink to="/cart" class="hi-link cart-link" :class="{ 'cart-mobile-hidden': hasTabBar }" aria-label="سلة المشتريات">
          <span class="cart-icon-wrap">
            <AppIcon name="cart" :size="26" />
            <span v-if="cartStore.count > 0" class="cart-badge">{{ cartStore.count }}</span>
          </span>
          <span class="desktop-only">
            <small>السلة</small>
            <b>{{ formattedTotal }} ج.م</b>
          </span>
        </RouterLink>

        <!-- Mobile hamburger -->
        <button
          type="button"
          class="mobile-icon-btn mobile-menu-btn"
          @click="$emit('toggle-drawer')"
          aria-label="فتح القائمة الجانبية"
        >
          <AppIcon name="menu" :size="24" />
        </button>
      </div>
    </div>

    <!-- Mobile Search Row: opens on tap (Item 30) -->
    <Transition name="expand">
      <div v-if="isMobileSearchOpen || searchPinned" class="mobile-search-row">
        <SearchBar />
      </div>
    </Transition>
  </header>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { useCartStore } from '@/stores/cart.js'
import { useUserStore } from '@/stores/user.js'
import { useFavoritesStore } from '@/stores/favorites.js'
import { useRouter } from 'vue-router'
import { showsTabBar, isRootScreen, keepsSearchOpen } from '@/utils/mobileShell.js'
import SearchBar from '@/components/layout/SearchBar.vue'
import BikeSelector from '@/components/layout/BikeSelector.vue'
import AppIcon from '@/components/common/AppIcon.vue'

defineEmits(['toggle-drawer'])

const route = useRoute()
const cartStore = useCartStore()
const userStore = useUserStore()
const favStore = useFavoritesStore()

const router = useRouter()
const isMobileSearchOpen = ref(false)
const isRoot = computed(() => isRootScreen(route))
const hasTabBar = computed(() => showsTabBar(route))
const searchPinned = computed(() => keepsSearchOpen(route))

const goBack = () => {
  if (window.history.state?.back) router.back()
  else router.push('/')
}

// Close mobile search on route change
watch(
  () => route.fullPath,
  () => {
    isMobileSearchOpen.value = false
  }
)

const formattedTotal = computed(() => {
  return cartStore.total.toLocaleString('en')
})
</script>

<style scoped>
.app-header {
  background: var(--bg);
  padding: 16px var(--page-pad);
  border-bottom: 1px solid var(--ln);
  position: relative;
}

.header-inner {
  display: flex;
  align-items: center;
  gap: 28px;
  max-width: var(--max-w);
  margin: 0 auto;
}

.logo {
  text-decoration: none;
  color: var(--ink);
  white-space: nowrap;
  line-height: 1;
}

.logo-title {
  display: block;
  font-size: 24px;
  font-weight: 800;
  letter-spacing: -0.5px;
}

.logo-sub {
  display: block;
  font-size: 11px;
  color: var(--mu);
  letter-spacing: 0.25em;
  margin-top: 5px;
  font-weight: 400;
}

.header-search {
  flex: 1;
  max-width: 600px;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-inline-start: auto;
}

.hi-link {
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  color: var(--ink);
  padding: 6px 10px;
  border-radius: 8px;
  position: relative;
  transition: background 0.15s ease;
}

.hi-link:hover {
  background: var(--sf);
}

.hi-link small {
  display: block;
  color: var(--mu);
  font-size: 12px;
}

.hi-link b {
  display: block;
  font-size: 14px;
}

.cart-icon-wrap {
  position: relative;
  display: inline-block;
}

.cart-badge, .fav-badge {
  position: absolute;
  top: -6px;
  inset-inline-start: -8px;
  background: var(--sale);
  color: #ffffff;
  font-size: 11px;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-weight: 700;
}

.mobile-icon-btn {
  display: none;
  background: none;
  border: none;
  padding: 6px;
  color: var(--ink);
  cursor: pointer;
  border-radius: 6px;
}

.mobile-icon-btn:hover {
  background: var(--sf);
}

.mobile-icon-btn:focus-visible {
  outline: 2px solid var(--ink);
}

.mobile-search-row {
  max-width: var(--max-w);
  margin: 12px auto 0;
  padding-top: 4px;
}

.expand-enter-active,
.expand-leave-active {
  transition: all 0.2s ease;
}

.expand-enter-from,
.expand-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

@media (max-width: 992px) {
  .desktop-only {
    display: none !important;
  }

  .mobile-icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .header-inner {
    justify-content: space-between;
    gap: 12px;
  }

  .header-search {
    display: none;
  }

  .header-actions {
    gap: 8px;
  }

  .hi-link {
    padding: 6px;
  }
}

.bike-chip {
  display: none;
}

/* The pinned search row is for phones/tablets; desktop has the inline search */
@media (min-width: 993px) {
  .mobile-search-row {
    display: none;
  }
}

.mobile-fav {
  position: relative;
}

/* ---------- Phone: app-style top bar ---------- */
@media (max-width: 768px) {
  .app-header {
    position: sticky;
    top: 0;
    z-index: 970;
    padding: 10px var(--page-pad) 12px;
    padding-top: calc(10px + env(safe-area-inset-top));
    background: rgba(255, 255, 255, 0.9);
    -webkit-backdrop-filter: saturate(180%) blur(18px);
    backdrop-filter: saturate(180%) blur(18px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  }

  .header-inner {
    gap: 6px;
  }

  .back-btn {
    margin-inline-end: 2px;
    margin-inline-start: -6px;
  }

  .logo-title {
    font-size: 20px;
  }

  .logo-sub {
    font-size: 8.5px;
    margin-top: 3px;
  }

  .header-actions {
    gap: 4px;
  }

  .mobile-menu-btn,
  .cart-mobile-hidden {
    display: none !important;
  }

  .bike-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 34px;
    padding: 0 12px;
    border-radius: 999px;
    background: var(--sf);
    color: var(--ink);
    font-family: var(--f-ui);
    font-size: 12.5px;
    font-weight: 800;
    white-space: nowrap;
    -webkit-tap-highlight-color: transparent;
  }

  .bike-chip:active {
    background: var(--sf2);
  }

  .mobile-icon-btn {
    width: 40px;
    height: 40px;
    border-radius: 12px;
  }

  .mobile-search-row {
    margin-top: 10px;
    padding-top: 0;
  }

  .mobile-search-row :deep(.search-bar) {
    height: 44px;
    border-radius: 14px;
    background: var(--sf);
    border-color: transparent;
  }

  .mobile-search-row :deep(.search-input) {
    background: transparent;
    font-size: 16px;
  }
}

@media (max-width: 360px) {
  .logo-sub {
    display: none;
  }
  .bike-chip span {
    display: none;
  }
  .bike-chip {
    width: 36px;
    padding: 0;
    justify-content: center;
  }
}
</style>
