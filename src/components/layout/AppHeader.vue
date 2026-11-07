<template>
  <header class="app-header">
    <div class="header-inner">
      <!-- Mobile hamburger -->
      <button
        type="button"
        class="mobile-menu-btn"
        @click="$emit('toggle-drawer')"
        aria-label="القائمة الجانبية"
      >
        <AppIcon name="menu" :size="24" />
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
        <!-- Bike selector -->
        <div class="desktop-only">
          <BikeSelector />
        </div>

        <!-- User account -->
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

        <!-- Favorites -->
        <RouterLink to="/account?tab=favorites" class="hi-link desktop-only icon-only" aria-label="المفضلة">
          <AppIcon name="heart" :size="24" />
          <span v-if="favStore.count > 0" class="fav-badge">{{ favStore.count }}</span>
        </RouterLink>

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
      </div>
    </div>

    <!-- Mobile Search Row -->
    <div class="mobile-search-row">
      <SearchBar />
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { useCartStore } from '@/stores/cart.js'
import { useUserStore } from '@/stores/user.js'
import { useFavoritesStore } from '@/stores/favorites.js'
import SearchBar from '@/components/layout/SearchBar.vue'
import BikeSelector from '@/components/layout/BikeSelector.vue'
import AppIcon from '@/components/common/AppIcon.vue'

defineEmits(['toggle-drawer'])

const cartStore = useCartStore()
const userStore = useUserStore()
const favStore = useFavoritesStore()

const formattedTotal = computed(() => {
  return cartStore.total.toLocaleString('en')
})
</script>

<style scoped>
.app-header {
  background: var(--bg);
  padding: 18px var(--page-pad);
  border-bottom: 1px solid var(--ln);
}

.header-inner {
  display: flex;
  align-items: center;
  gap: 28px;
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

.header-search {
  flex: 1;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

.hi-link {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  line-height: 1.3;
  white-space: nowrap;
  color: var(--ink);
  text-decoration: none;
  padding: 4px;
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

.mobile-menu-btn {
  display: none;
  padding: 6px;
  color: var(--ink);
}

.mobile-search-row {
  display: none;
  margin-top: 12px;
}

@media (max-width: 992px) {
  .desktop-only {
    display: none !important;
  }

  .mobile-menu-btn {
    display: block;
  }

  .header-inner {
    justify-content: space-between;
    gap: 16px;
  }

  .header-search {
    display: none;
  }

  .mobile-search-row {
    display: block;
  }
}
</style>
