<template>
  <nav class="catnav-wrap">
    <div class="catnav-inner">
      <button
        type="button"
        class="cat-link all-btn"
        @click="$emit('open-drawer')"
      >
        <AppIcon name="menu" :size="18" />
        <span>كل الأقسام</span>
      </button>

      <div class="nav-links">
        <RouterLink
          v-for="cat in categories"
          :key="cat.slug"
          :to="`/c/${cat.slug}`"
          class="cat-link"
          :class="{ on: isActiveCategory(cat.slug) }"
        >
          {{ cat.name }}
        </RouterLink>
      </div>

      <span class="sp"></span>

      <RouterLink
        to="/bikes"
        class="cat-link"
        :class="{ on: $route.path.startsWith('/bikes') }"
      >
        موتوسيكلات للبيع
      </RouterLink>

      <RouterLink
        to="/booking"
        class="cat-link"
        :class="{ on: $route.path === '/booking' }"
      >
        حجز صيانة
      </RouterLink>

      <RouterLink
        to="/c/oils?offer=true"
        class="cat-link hl"
      >
        العروض
      </RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { useRoute } from 'vue-router'
import { categories } from '@/data/categories.js'
import AppIcon from '@/components/common/AppIcon.vue'

defineEmits(['open-drawer'])

const route = useRoute()

const isActiveCategory = (slug) => {
  return route.path === `/c/${slug}`
}
</script>

<style scoped>
.catnav-wrap {
  border-bottom: 1px solid var(--ln);
  background: #ffffff;
  overflow-x: auto;
  scrollbar-width: none;
}

.catnav-wrap::-webkit-scrollbar {
  display: none;
}

.catnav-inner {
  display: flex;
  gap: 4px;
  align-items: center;
  padding: 0 var(--page-pad);
  height: 50px;
  font-size: 14.5px;
  max-width: 1280px;
  margin: 0 auto;
  white-space: nowrap;
}

.nav-links {
  display: flex;
  height: 100%;
}

.cat-link {
  padding: 0 14px;
  height: 100%;
  display: flex;
  align-items: center;
  color: var(--ink);
  text-decoration: none;
  font-weight: 400;
  border-bottom: 2px solid transparent;
  transition: all 0.15s ease;
}

.cat-link:hover {
  background: var(--sf);
}

.cat-link.all-btn {
  background: var(--ink);
  color: #ffffff;
  gap: 8px;
  font-weight: 700;
  margin-inline-end: 8px;
  border-radius: 6px;
  height: 38px;
}

.cat-link.all-btn:hover {
  opacity: 0.9;
}

.cat-link.on {
  border-bottom-color: var(--ink);
  font-weight: 700;
}

.sp {
  flex: 1;
}

.cat-link.hl {
  color: var(--sale);
  font-weight: 700;
}

@media (max-width: 992px) {
  .catnav-inner {
    padding: 0 16px;
  }
}
</style>
