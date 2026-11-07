<template>
  <form class="search-bar" @submit.prevent="handleSearch">
    <div class="cat-select-wrap">
      <select v-model="selectedCategory" class="cat-select" aria-label="اختر القسم">
        <option value="">كل الأقسام</option>
        <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">
          {{ cat.name }}
        </option>
      </select>
      <AppIcon name="down" :size="14" class="down-icon" />
    </div>

    <input
      type="search"
      v-model="query"
      class="search-input"
      placeholder="ابحث عن زيت، فلتر، قطعة غيار أو رقم القطعة…"
      aria-label="ابحث في المتجر"
    />

    <button type="submit" class="search-submit" aria-label="تنفيذ البحث">
      <AppIcon name="search" :size="20" :stroke-width="2" />
    </button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { categories } from '@/data/categories.js'
import AppIcon from '@/components/common/AppIcon.vue'

const router = useRouter()
const query = ref('')
const selectedCategory = ref('')

const handleSearch = () => {
  if (selectedCategory.value) {
    router.push({
      path: `/c/${selectedCategory.value}`,
      query: query.value ? { q: query.value } : {}
    })
  } else {
    router.push({
      path: '/search',
      query: query.value ? { q: query.value } : {}
    })
  }
}
</script>

<style scoped>
.search-bar {
  flex: 1;
  display: flex;
  height: 48px;
  border: 1.5px solid var(--ink);
  border-radius: var(--radius-btn);
  overflow: hidden;
  background: #ffffff;
}

.cat-select-wrap {
  position: relative;
  display: flex;
  align-items: center;
  background: var(--sf);
  border-inline-end: 1px solid var(--ln);
  padding: 0 16px;
}

.cat-select {
  appearance: none;
  background: transparent;
  border: none;
  font-size: 14px;
  font-weight: 500;
  color: var(--ink);
  padding-inline-end: 20px;
  cursor: pointer;
}

.cat-select:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: 2px;
}

.down-icon {
  position: absolute;
  inset-inline-end: 12px;
  pointer-events: none;
  color: var(--mu);
}

.search-input {
  flex: 1;
  border: none;
  padding: 0 16px;
  font-size: 14px;
  color: var(--ink);
}

.search-input:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: -2px;
}

.search-input::placeholder {
  color: var(--mu);
}

.search-submit {
  width: 56px;
  background: var(--ink);
  color: #ffffff;
  display: grid;
  place-items: center;
  border: none;
  cursor: pointer;
  transition: opacity 0.15s ease;
}

.search-submit:hover {
  opacity: 0.9;
}

@media (max-width: 900px) {
  .cat-select-wrap {
    display: none;
  }
}
</style>
