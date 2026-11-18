<template>
  <nav class="tabbar" aria-label="التنقل الرئيسي">
    <RouterLink to="/" class="tab" :class="{ on: route.path === '/' }">
      <AppIcon name="home" :size="23" :stroke-width="route.path === '/' ? 2.2 : 1.7" />
      <span>الرئيسية</span>
    </RouterLink>

    <button type="button" class="tab" :class="{ on: isShopping }" @click="$emit('open-drawer')">
      <AppIcon name="grid" :size="22" :stroke-width="isShopping ? 2.2 : 1.7" />
      <span>الأقسام</span>
    </button>

    <RouterLink to="/booking" class="tab tab-main" :class="{ on: route.path === '/booking' }">
      <span class="tab-main-icon">
        <AppIcon name="wrench" :size="22" :stroke-width="2" />
      </span>
      <span>احجز صيانة</span>
    </RouterLink>

    <RouterLink to="/cart" class="tab" :class="{ on: route.path === '/cart' }">
      <span class="icon-wrap">
        <AppIcon name="cart" :size="23" :stroke-width="route.path === '/cart' ? 2.2 : 1.7" />
        <Transition name="pop">
          <span v-if="cartStore.count > 0" :key="cartStore.count" class="badge">{{ cartStore.count }}</span>
        </Transition>
      </span>
      <span>السلة</span>
    </RouterLink>

    <RouterLink
      :to="userStore.isAuthenticated ? '/account' : '/login'"
      class="tab"
      :class="{ on: route.path === '/account' || route.path === '/login' }"
    >
      <AppIcon name="user" :size="23" :stroke-width="route.path === '/account' ? 2.2 : 1.7" />
      <span>حسابي</span>
    </RouterLink>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { useCartStore } from '@/stores/cart.js'
import { useUserStore } from '@/stores/user.js'
import AppIcon from '@/components/common/AppIcon.vue'

defineEmits(['open-drawer'])

const route = useRoute()
const cartStore = useCartStore()
const userStore = useUserStore()

const isShopping = computed(() => route.path.startsWith('/c/') || route.path === '/search' || route.path.startsWith('/bikes'))
</script>

<style scoped>
.tabbar {
  display: none;
}

@media (max-width: 768px) {
  .tabbar {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    position: fixed;
    inset-inline: 0;
    bottom: 0;
    z-index: 980;
    height: calc(var(--tabbar-h) + env(safe-area-inset-bottom));
    padding-bottom: env(safe-area-inset-bottom);
    background: rgba(255, 255, 255, 0.92);
    -webkit-backdrop-filter: saturate(180%) blur(18px);
    backdrop-filter: saturate(180%) blur(18px);
    border-top: 1px solid rgba(0, 0, 0, 0.07);
  }

  .tab {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    color: #8a8a8a;
    font-family: var(--f-ui);
    font-size: 10.5px;
    font-weight: 700;
    text-decoration: none;
    -webkit-tap-highlight-color: transparent;
    transition: color 0.15s ease, transform 0.12s ease;
  }

  .tab:active {
    transform: scale(0.92);
  }

  .tab.on {
    color: var(--ink);
  }

  .icon-wrap {
    position: relative;
    display: grid;
  }

  .badge {
    position: absolute;
    top: -5px;
    inset-inline-end: -9px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 999px;
    background: var(--sale);
    color: #ffffff;
    font-size: 10.5px;
    font-weight: 800;
    line-height: 18px;
    text-align: center;
    box-shadow: 0 0 0 2px #ffffff;
  }

  /* Raised center action */
  .tab-main {
    color: var(--ink);
  }

  .tab-main-icon {
    width: 46px;
    height: 46px;
    margin-top: -22px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    background: var(--ink);
    color: #ffffff;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22), 0 0 0 4px #ffffff;
    transition: transform 0.15s ease;
  }

  .tab-main.on .tab-main-icon {
    background: var(--sale);
  }

  .pop-enter-active {
    animation: pop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
  }

  @keyframes pop {
    from { transform: scale(0.4); }
    to { transform: scale(1); }
  }
}

@media (prefers-reduced-motion: reduce) {
  .pop-enter-active { animation: none; }
}
</style>
