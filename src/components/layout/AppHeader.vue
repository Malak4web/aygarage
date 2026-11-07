<template>
  <header class="app-header">
    <div class="header-inner">
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
            <small>{{ userStore.isAuthenticated ? 'أهلاً ' + (userStore.user?.name?.split(' ')[0] || 'كريم') : 'تسجيل الدخول' }}</small>
            <b>حسابي</b>
          </span>
        </RouterLink>

        <!-- Favorites (Desktop only) -->
        <RouterLink to="/account?tab=favorites" class="hi-link desktop-only icon-only" aria-label="المفضلة">
          <AppIcon name="heart" :size="24" />
          <span v-if="favStore.count > 0" class="fav-badge">{{ favStore.count }}</span>
        </RouterLink>

        <!-- Mobile search toggle icon button -->
        <button
          type="button"
          class="mobile-icon-btn mobile-search-toggle"
          @click="isMobileSearchOpen = !isMobileSearchOpen"
          :aria-expanded="isMobileSearchOpen"
          aria-label="البحث عن منتجات"
        >
          <AppIcon name="search" :size="24" />
        </button>

        <!-- Cart -->
        <RouterLink to="/cart" class="hi-link cart-link" aria-label="سلة المشتريات">
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
      <div v-if="isMobileSearchOpen" class="mobile-search-row">
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
import SearchBar from '@/components/layout/SearchBar.vue'
import BikeSelector from '@/components/layout/BikeSelector.vue'
import AppIcon from '@/components/common/AppIcon.vue'

defineEmits(['toggle-drawer'])

const route = useRoute()
const cartStore = useCartStore()
const userStore = useUserStore()
const favStore = useFavoritesStore()

const isMobileSearchOpen = ref(false)

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
</style>
