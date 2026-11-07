<template>
  <div class="category-page">
    <Breadcrumbs :items="breadcrumbItems" />

    <div class="lhd in">
      <div>
        <h1>{{ categoryName }}</h1>
        <p>{{ filteredProducts.length }} منتج</p>
      </div>

      <!-- Mobile filter trigger button -->
      <button type="button" class="btn o sm mobile-filter-btn" @click="mobileFilterOpen = true">
        <AppIcon name="filter" :size="16" />
        <span>فلترة ({{ activeFiltersCount }})</span>
      </button>
    </div>

    <!-- Active Filter Chips -->
    <div class="chips in">
      <button
        type="button"
        class="chip"
        :class="{ on: filterOnlyFitting }"
        @click="filterOnlyFitting = !filterOnlyFitting"
      >
        <AppIcon name="check" :size="14" :stroke-width="2.4" />
        <span>يركب على {{ userStore.selectedBike }}</span>
        <AppIcon name="x" :size="14" />
      </button>

      <button
        v-if="selectedViscosity"
        type="button"
        class="chip on"
        @click="selectedViscosity = ''"
      >
        <span>{{ selectedViscosity }}</span>
        <AppIcon name="x" :size="14" />
      </button>

      <button
        v-if="selectedBrand"
        type="button"
        class="chip on"
        @click="selectedBrand = ''"
      >
        <span>{{ selectedBrand }}</span>
        <AppIcon name="x" :size="14" />
      </button>

      <button
        v-if="selectedOilType"
        type="button"
        class="chip on"
        @click="selectedOilType = ''"
      >
        <span>{{ selectedOilType }}</span>
        <AppIcon name="x" :size="14" />
      </button>

      <button
        v-if="hasActiveFilters"
        type="button"
        class="chip clear-all"
        @click="resetFilters"
      >
        مسح الكل
      </button>
    </div>

    <!-- Layout: Sidebar + Products Grid -->
    <div class="lay in">
      <!-- Filter Sidebar (Desktop + Mobile Sheet) -->
      <div v-if="mobileFilterOpen" class="sheet-backdrop" @click="mobileFilterOpen = false"></div>
      <aside class="flt" :class="{ 'mobile-sheet': mobileFilterOpen }">
        <div class="sheet-header" v-if="mobileFilterOpen">
          <b>الفلاتر</b>
          <button type="button" class="btn-close" @click="mobileFilterOpen = false">
            <AppIcon name="x" :size="20" />
          </button>
        </div>

        <!-- Bike model filter -->
        <div class="g">
          <h5>موديل الموتوسيكل</h5>
          <div class="sel-wrap">
            <select
              :value="userStore.selectedBike"
              @change="userStore.setSelectedBike($event.target.value)"
              class="model-select"
            >
              <option v-for="m in storeConfig.models" :key="m" :value="m">
                {{ m }} · {{ userBikeYear }}
              </option>
            </select>
            <AppIcon name="down" :size="14" class="sel-icon" />
          </div>
        </div>

        <!-- Viscosity filter -->
        <div class="g" v-if="viscosities.length > 0">
          <h5>اللزوجة <AppIcon name="down" :size="14" /></h5>
          <label v-for="v in viscosities" :key="v.name" class="filter-label">
            <input
              type="radio"
              name="viscosity"
              :value="v.name"
              v-model="selectedViscosity"
              class="cb-input"
            />
            <span class="cb" :class="{ on: selectedViscosity === v.name }"></span>
            <span>{{ v.name }}</span>
            <small>{{ v.count }}</small>
          </label>
        </div>

        <!-- Brand filter -->
        <div class="g" v-if="brands.length > 0">
          <h5>الماركة <AppIcon name="down" :size="14" /></h5>
          <label v-for="b in brands" :key="b.name" class="filter-label">
            <input
              type="radio"
              name="brand"
              :value="b.name"
              v-model="selectedBrand"
              class="cb-input"
            />
            <span class="cb" :class="{ on: selectedBrand === b.name }"></span>
            <span>{{ b.name }}</span>
            <small>{{ b.count }}</small>
          </label>
        </div>

        <!-- Oil Type filter -->
        <div class="g" v-if="oilTypes.length > 0">
          <h5>النوع <AppIcon name="down" :size="14" /></h5>
          <label v-for="t in oilTypes" :key="t.name" class="filter-label">
            <input
              type="radio"
              name="oilType"
              :value="t.name"
              v-model="selectedOilType"
              class="cb-input"
            />
            <span class="cb" :class="{ on: selectedOilType === t.name }"></span>
            <span>{{ t.name }}</span>
            <small>{{ t.count }}</small>
          </label>
        </div>

        <!-- Price Range -->
        <div class="g">
          <h5>السعر</h5>
          <div class="range">
            <i></i>
          </div>
          <div class="rin">
            <div>{{ minPrice }}</div>
            <div>{{ maxPrice }}</div>
          </div>
        </div>

        <!-- Stock and On Sale Checkboxes -->
        <div class="g">
          <label class="filter-label">
            <input type="checkbox" v-model="inStockOnly" class="cb-input" />
            <span class="cb" :class="{ on: inStockOnly }"></span>
            <span>متوفر بس</span>
          </label>
          <label class="filter-label">
            <input type="checkbox" v-model="onSaleOnly" class="cb-input" />
            <span class="cb" :class="{ on: onSaleOnly }"></span>
            <span>عليه خصم</span>
          </label>
        </div>

        <div v-if="mobileFilterOpen" class="sheet-apply">
          <button type="button" class="btn full" @click="mobileFilterOpen = false">
            تطبيق ({{ filteredProducts.length }})
          </button>
        </div>
      </aside>

      <!-- Products Grid Section -->
      <main class="listing-main">
        <div class="tb">
          <span>عرض 1-{{ Math.min(12, filteredProducts.length) }} من {{ filteredProducts.length }} منتج</span>
          <div class="sort-wrap">
            <select v-model="sortBy" class="sort-select">
              <option value="bestseller">ترتيب: الأكثر مبيعاً</option>
              <option value="price-asc">السعر: من الأقل للأعلى</option>
              <option value="price-desc">السعر: من الأعلى للأقل</option>
              <option value="rating">التقييم</option>
            </select>
            <AppIcon name="down" :size="14" class="sort-icon" />
          </div>
        </div>

        <div v-if="filteredProducts.length === 0" class="no-results">
          <EmptyState
            title="لا توجد منتجات مطابقة للبحث أو الفلترة"
            description="جرّب تقليل شروط الفلترة أو مسح الموديل لعرض كل المنتجات المتوفرة."
            action-label="مسح كل الفلاتر"
            @action="resetFilters"
          />
        </div>

        <div v-else class="grid g4">
          <ProductCard
            v-for="product in paginatedProducts"
            :key="product.id"
            :product="product"
          />
        </div>

        <Pagination
          v-if="totalPages > 1"
          v-model:current-page="currentPage"
          :total-pages="totalPages"
        />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { categories } from '@/data/categories.js'
import { products } from '@/data/products.js'
import { useUserStore } from '@/stores/user.js'
import { doesFit } from '@/utils/fit.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import ProductCard from '@/components/common/ProductCard.vue'
import Pagination from '@/components/common/Pagination.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const route = useRoute()
const userStore = useUserStore()

onMounted(() => {
  if (route.query.model) {
    userStore.setSelectedBike(route.query.model)
  }
})

// Breadcrumbs
const categoryName = computed(() => {
  const cat = categories.find((c) => c.slug === route.params.category)
  return cat ? cat.name : 'جميع المنتجات'
})

const breadcrumbItems = computed(() => [
  { label: 'الرئيسية', to: '/' },
  { label: categoryName.value }
])

const userBikeYear = computed(() => userStore.user?.savedBikes?.[0]?.year || 2023)

// Filter states - empty defaults per Item 8
const filterOnlyFitting = ref(true)
const selectedViscosity = ref('')
const selectedBrand = ref('')
const selectedOilType = ref('')
const minPrice = ref(500)
const maxPrice = ref(2500)
const inStockOnly = ref(false)
const onSaleOnly = ref(false)
const sortBy = ref('bestseller')
const currentPage = ref(1)
const itemsPerPage = 12
const mobileFilterOpen = ref(false)

// Products strictly in this category
const categoryProducts = computed(() => {
  if (route.params.category && route.params.category !== 'all') {
    return products.filter((p) => p.category === route.params.category)
  }
  return [...products]
})

// Dynamically derived filter options
const viscosities = computed(() => {
  const map = {}
  categoryProducts.value.forEach((p) => {
    if (p.viscosity) {
      map[p.viscosity] = (map[p.viscosity] || 0) + 1
    }
  })
  return Object.entries(map).map(([name, count]) => ({ name, count }))
})

const brands = computed(() => {
  const map = {}
  categoryProducts.value.forEach((p) => {
    if (p.brand) {
      map[p.brand] = (map[p.brand] || 0) + 1
    }
  })
  return Object.entries(map).map(([name, count]) => ({ name, count }))
})

const oilTypes = computed(() => {
  const map = {}
  categoryProducts.value.forEach((p) => {
    if (p.oilType) {
      map[p.oilType] = (map[p.oilType] || 0) + 1
    }
  })
  return Object.entries(map).map(([name, count]) => ({ name, count }))
})

const hasActiveFilters = computed(() => {
  return (
    filterOnlyFitting.value ||
    Boolean(selectedViscosity.value) ||
    Boolean(selectedBrand.value) ||
    Boolean(selectedOilType.value) ||
    inStockOnly.value ||
    onSaleOnly.value
  )
})

const activeFiltersCount = computed(() => {
  let count = 0
  if (filterOnlyFitting.value) count++
  if (selectedViscosity.value) count++
  if (selectedBrand.value) count++
  if (selectedOilType.value) count++
  if (inStockOnly.value) count++
  if (onSaleOnly.value) count++
  return count
})

const resetFilters = () => {
  filterOnlyFitting.value = false
  selectedViscosity.value = ''
  selectedBrand.value = ''
  selectedOilType.value = ''
  inStockOnly.value = false
  onSaleOnly.value = false
}

// Reset filters on category route change
watch(
  () => route.params.category,
  () => {
    resetFilters()
    currentPage.value = 1
  }
)

// Reset page on filter changes
watch(
  [filterOnlyFitting, selectedViscosity, selectedBrand, selectedOilType, inStockOnly, onSaleOnly, sortBy],
  () => {
    currentPage.value = 1
  }
)

// Filtered and sorted products
const filteredProducts = computed(() => {
  let list = [...categoryProducts.value]

  // Filter query
  if (route.query.q) {
    const q = route.query.q.toLowerCase()
    list = list.filter(
      (p) =>
        p.name.toLowerCase().includes(q) ||
        p.brand.toLowerCase().includes(q) ||
        p.sku.toLowerCase().includes(q)
    )
  }

  // Filter model compatibility with doesFit
  if (filterOnlyFitting.value) {
    const currentBike = userStore.selectedBike
    list = list.filter((p) => doesFit(p, currentBike) !== false)
  }

  // Filter viscosity
  if (selectedViscosity.value) {
    list = list.filter((p) => p.viscosity === selectedViscosity.value)
  }

  // Filter brand
  if (selectedBrand.value) {
    list = list.filter((p) => p.brand.toLowerCase() === selectedBrand.value.toLowerCase())
  }

  // Filter oil type
  if (selectedOilType.value) {
    list = list.filter((p) => p.oilType === selectedOilType.value)
  }

  // Filter in-stock
  if (inStockOnly.value) {
    list = list.filter((p) => p.stock > 0)
  }

  // Filter on-sale
  if (onSaleOnly.value || route.query.offer === 'true') {
    list = list.filter((p) => p.oldPrice && p.oldPrice > p.price)
  }

  // Sorting
  if (sortBy.value === 'price-asc') {
    list.sort((a, b) => a.price - b.price)
  } else if (sortBy.value === 'price-desc') {
    list.sort((a, b) => b.price - a.price)
  } else if (sortBy.value === 'rating') {
    list.sort((a, b) => b.rating - a.rating)
  }

  return list
})

const totalPages = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage) || 1)

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredProducts.value.slice(start, start + itemsPerPage)
})
</script>

<style scoped>
.lhd {
  padding-top: 10px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.lhd h1 {
  font-size: 30px;
  font-weight: 800;
  margin: 0;
}

.lhd p {
  margin: 2px 0 0;
  color: var(--mu);
  font-size: 14px;
}

.mobile-filter-btn {
  display: none;
}

.chips {
  display: flex;
  gap: 8px;
  padding-top: 16px;
  flex-wrap: wrap;
}

.chip {
  height: 36px;
  padding: 0 14px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-pill);
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13.5px;
  background: #ffffff;
  color: var(--ink);
  cursor: pointer;
}

.chip.on {
  background: var(--ink);
  color: #ffffff;
  border-color: var(--ink);
}

.chip.clear-all {
  border: 0;
  text-decoration: underline;
  background: transparent;
  color: var(--ink);
}

.lay {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 28px;
  padding-top: 20px;
}

.flt {
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  align-self: flex-start;
  background: #ffffff;
}

.flt .g {
  padding: 16px 18px;
  border-bottom: 1px solid var(--ln);
}

.flt .g:last-child {
  border: 0;
}

.flt h5 {
  margin: 0 0 10px;
  font-size: 14.5px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.sel-wrap {
  position: relative;
  height: 46px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  display: flex;
  align-items: center;
  padding: 0 14px;
  background: #ffffff;
}

.model-select {
  width: 100%;
  appearance: none;
  border: none;
  background: transparent;
  font-size: 14px;
  color: var(--ink);
  outline: none;
  cursor: pointer;
  padding-left: 20px;
}

.sel-icon {
  position: absolute;
  left: 12px;
  pointer-events: none;
  color: var(--mu);
}

.filter-label {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 4px 0;
  font-size: 14px;
  cursor: pointer;
}

.filter-label small {
  margin-inline-start: auto;
  color: var(--mu);
  font-size: 12px;
}

.cb-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.cb {
  width: 18px;
  height: 18px;
  border-radius: 5px;
  border: 1.5px solid #bdbdbd;
  flex: none;
  position: relative;
  display: inline-block;
  background: #ffffff;
  transition: all 0.15s ease;
}

.cb.on {
  background: var(--ink);
  border-color: var(--ink);
}

.cb.on::after {
  content: "";
  position: absolute;
  inset: 2px 5px 5px 5px;
  border: solid #ffffff;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.range {
  height: 4px;
  background: var(--sf2);
  border-radius: 4px;
  position: relative;
  margin: 14px 6px 12px;
}

.range i {
  position: absolute;
  right: 10%;
  left: 45%;
  top: 0;
  bottom: 0;
  background: var(--ink);
}

.range i::before,
.range i::after {
  content: "";
  position: absolute;
  top: -6px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #ffffff;
  box-shadow: 0 0 0 2px var(--ink);
}

.range i::before {
  right: -8px;
}

.range i::after {
  left: -8px;
}

.rin {
  display: flex;
  gap: 8px;
}

.rin div {
  flex: 1;
  height: 38px;
  border: 1px solid var(--ln);
  border-radius: 8px;
  display: flex;
  align-items: center;
  padding: 0 10px;
  font-size: 13px;
  background: #ffffff;
}

.tb {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
  font-size: 14px;
  color: var(--mu);
}

.sort-wrap {
  position: relative;
  height: 40px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  display: flex;
  align-items: center;
  padding: 0 14px;
  background: #ffffff;
}

.sort-select {
  appearance: none;
  border: none;
  background: transparent;
  font-size: 14px;
  color: var(--ink);
  outline: none;
  cursor: pointer;
  padding-left: 24px;
}

.sort-icon {
  position: absolute;
  left: 10px;
  pointer-events: none;
  color: var(--mu);
}

.grid.g4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

/* Responsive */
@media (max-width: 1024px) {
  .grid.g4 {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .lay {
    grid-template-columns: 1fr;
  }

  .mobile-filter-btn {
    display: inline-flex;
  }

  .flt {
    display: none;
  }

  .flt.mobile-sheet {
    display: block;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    max-height: 85vh;
    background: #ffffff;
    border-radius: 20px 20px 0 0;
    z-index: 1000;
    overflow-y: auto;
    box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.2);
    border: none;
  }

  .sheet-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 999;
  }

  .sheet-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid var(--ln);
    font-size: 17px;
  }

  .sheet-apply {
    padding: 16px 20px;
    border-top: 1px solid var(--ln);
    position: sticky;
    bottom: 0;
    background: #ffffff;
  }

  .grid.g4 {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
