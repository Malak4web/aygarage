<template>
  <div class="search-page">
    <Breadcrumbs :items="breadcrumbItems" />

    <div class="lhd in">
      <div>
        <h1>نتائج البحث {{ searchQuery ? `عن "${searchQuery}"` : '' }}</h1>
        <p>{{ results.length }} منتج متطابق</p>
      </div>
    </div>

    <div class="in" style="padding-top: 20px; padding-bottom: 48px;">
      <div v-if="loading" class="grid">
        <ProductCardSkeleton v-for="i in 5" :key="i" />
      </div>

      <div v-else-if="results.length === 0">
        <EmptyState
          icon="search"
          :title="`لا توجد نتائج بحث لـ '${searchQuery}'`"
          description="تأكد من كتابة الكلمات بشكل صحيح، أو ابحث باسم الماركة (مثل Brembo, Motul, Zontes) أو رقم القطعة."
          action-label="تصفح جميع الأقسام"
          action-to="/c/oils"
        />
      </div>

      <div v-else class="grid">
        <ProductCard
          v-for="p in results"
          :key="p.id"
          :product="p"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '@/api/index.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import ProductCard from '@/components/common/ProductCard.vue'
import ProductCardSkeleton from '@/components/common/ProductCardSkeleton.vue'
import EmptyState from '@/components/common/EmptyState.vue'

const route = useRoute()
const loading = ref(true)
const allProducts = ref([])

onMounted(async () => {
  try {
    allProducts.value = await api.getProducts()
  } catch (e) {
    allProducts.value = []
  } finally {
    loading.value = false
  }
})

const searchQuery = computed(() => (route.query.q || '').trim())

const breadcrumbItems = computed(() => [
  { label: 'الرئيسية', to: '/' },
  { label: 'البحث' }
])

const results = computed(() => {
  if (!searchQuery.value) return allProducts.value
  const q = searchQuery.value.toLowerCase()
  return allProducts.value.filter((p) => {
    return (
      p.name.toLowerCase().includes(q) ||
      p.brand.toLowerCase().includes(q) ||
      p.sku.toLowerCase().includes(q) ||
      (p.description && p.description.toLowerCase().includes(q)) ||
      (p.fits && p.fits.some((m) => m.toLowerCase().includes(q)))
    )
  })
})
</script>

<style scoped>
.lhd {
  padding-top: 10px;
}

.lhd h1 {
  font-size: 28px;
  font-weight: 800;
  margin: 0;
}

.lhd p {
  margin: 4px 0 0;
  color: var(--mu);
  font-size: 14px;
}

.grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
}

@media (max-width: 1100px) {
  .grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
