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
    <div class="in">
      <FilterChips
        :chips="activeFilterChips"
        @remove="handleRemoveChip"
        @clear-all="resetFilters"
      />
    </div>

    <!-- Layout: Sidebar + Products Grid -->
    <div class="lay in">
      <!-- Filter Sidebar with Bottom Sheet on Mobile -->
      <FilterSidebar
        :is-open="mobileFilterOpen"
        title="فلاتر القسم"
        @close="mobileFilterOpen = false"
      >
        <!-- Bike model filter -->
        <div class="g">
          <h5>موديل الموتوسيكل</h5>
          <div class="sel-wrap">
            <select
              :value="userStore.selectedBike"
              @change="userStore.setSelectedBike($event.target.value)"
              class="model-select"
              aria-label="اختيار موديل الموتوسيكل"
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
          <h5>اللزوجة</h5>
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
          <h5>الماركة</h5>
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
          <h5>النوع</h5>
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

        <!-- Price Range Slider -->
        <div class="g">
          <h5>السعر</h5>
          <div class="price-inputs">
            <input
              type="number"
              v-model.number="minPrice"
              class="price-num-input"
              aria-label="الحد الأدنى للسعر"
            />
            <span>إلى</span>
            <input
              type="number"
              v-model.number="maxPrice"
              class="price-num-input"
              aria-label="الحد الأقصى للسعر"
            />
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
      </FilterSidebar>

      <!-- Products Grid Section -->
      <main class="listing-main">
        <div class="tb">
          <span>{{ paginationText }}</span>
          <SortSelect
            v-model="sortBy"
            :options="sortOptions"
            id="cat-sort-select"
          />
        </div>

        <!-- Loading Skeletons -->
        <div v-if="loading" class="grid g4">
          <ProductCardSkeleton v-for="i in 8" :key="i" />
        </div>

        <!-- Error with retry -->
        <div v-else-if="error" class="no-results">
          <EmptyState
            title="حدث خطأ في تحميل المنتجات"
            :description="error"
            action-label="إعادة المحاولة"
            @action="fetchProducts"
          />
        </div>

        <!-- Empty Results -->
        <div v-else-if="filteredProducts.length === 0" class="no-results">
          <EmptyState
            title="لا توجد منتجات مطابقة للبحث أو الفلترة"
            description="جرّب تقليل شروط الفلترة أو مسح الموديل لعرض كل المنتجات المتوفرة."
            action-label="مسح كل الفلاتر"
            @action="resetFilters"
          />
        </div>

        <!-- Products Grid -->
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
import { api } from '@/api/index.js'
import { useUserStore } from '@/stores/user.js'
import { doesFit } from '@/utils/fit.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import ProductCard from '@/components/common/ProductCard.vue'
import ProductCardSkeleton from '@/components/common/ProductCardSkeleton.vue'
import Pagination from '@/components/common/Pagination.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import FilterSidebar from '@/components/common/FilterSidebar.vue'
import FilterChips from '@/components/common/FilterChips.vue'
import SortSelect from '@/components/common/SortSelect.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const route = useRoute()
const userStore = useUserStore()

const loading = ref(true)
const error = ref('')
const allProducts = ref([])

const fetchProducts = async () => {
  loading.value = true
  error.value = ''
  try {
    allProducts.value = await api.getProducts()
  } catch (e) {
    error.value = 'تعذر الاتصال بقاعدة البيانات، يرجى المحاولة لاحقاً.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProducts()
})

// Item 23: Read ?model&year from Home finder
watch(
  () => [route.query.model, route.query.year],
  ([m, y]) => {
    if (m) {
      userStore.setSelectedBike(m, y)
    }
  },
  { immediate: true }
)

// Breadcrumbs
const categoryName = computed(() => {
  const cat = categories.find((c) => c.slug === route.params.category)
  return cat ? cat.name : 'جميع المنتجات'
})

const breadcrumbItems = computed(() => [
  { label: 'الرئيسية', to: '/' },
  { label: categoryName.value }
])

const userBikeYear = computed(() => userStore.userBikeYear)

// Filter states - empty defaults per Item 8
const filterOnlyFitting = ref(true)
const selectedViscosity = ref('')
const selectedBrand = ref('')
const selectedOilType = ref('')
const minPrice = ref(0)
const maxPrice = ref(10000)
const inStockOnly = ref(false)
const onSaleOnly = ref(false)
const sortBy = ref('bestseller')
const currentPage = ref(1)
const itemsPerPage = 12
const mobileFilterOpen = ref(false)

const sortOptions = [
  { value: 'bestseller', label: 'ترتيب: الأكثر مبيعاً' },
  { value: 'newest', label: 'ترتيب: الأحدث' },
  { value: 'price-asc', label: 'السعر: من الأقل للأعلى' },
  { value: 'price-desc', label: 'السعر: من الأعلى للأقل' },
  { value: 'rating', label: 'التقييم' }
]

// Products strictly in this category
const categoryProducts = computed(() => {
  if (route.params.category && route.params.category !== 'all') {
    return allProducts.value.filter((p) => p.category === route.params.category)
  }
  return [...allProducts.value]
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

const activeFilterChips = computed(() => {
  const list = []
  if (filterOnlyFitting.value) {
    list.push({ id: 'fitting', label: `يركب على ${userStore.selectedBike}` })
  }
  if (selectedViscosity.value) {
    list.push({ id: 'viscosity', label: selectedViscosity.value })
  }
  if (selectedBrand.value) {
    list.push({ id: 'brand', label: selectedBrand.value })
  }
  if (selectedOilType.value) {
    list.push({ id: 'oilType', label: selectedOilType.value })
  }
  if (inStockOnly.value) {
    list.push({ id: 'inStock', label: 'متوفر بس' })
  }
  if (onSaleOnly.value) {
    list.push({ id: 'onSale', label: 'عليه خصم' })
  }
  return list
})

const activeFiltersCount = computed(() => activeFilterChips.value.length)

const handleRemoveChip = (chipId) => {
  if (chipId === 'fitting') filterOnlyFitting.value = false
  if (chipId === 'viscosity') selectedViscosity.value = ''
  if (chipId === 'brand') selectedBrand.value = ''
  if (chipId === 'oilType') selectedOilType.value = ''
  if (chipId === 'inStock') inStockOnly.value = false
  if (chipId === 'onSale') onSaleOnly.value = false
}

const resetFilters = () => {
  filterOnlyFitting.value = false
  selectedViscosity.value = ''
  selectedBrand.value = ''
  selectedOilType.value = ''
  inStockOnly.value = false
  onSaleOnly.value = false
  minPrice.value = 0
  maxPrice.value = 10000
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
  [filterOnlyFitting, selectedViscosity, selectedBrand, selectedOilType, inStockOnly, onSaleOnly, sortBy, minPrice, maxPrice],
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
    list = list.filter((p) => p.brand === selectedBrand.value)
  }

  // Filter oil type
  if (selectedOilType.value) {
    list = list.filter((p) => p.oilType === selectedOilType.value)
  }

  // Price range
  if (minPrice.value > 0) {
    list = list.filter((p) => p.price >= minPrice.value)
  }
  if (maxPrice.value < 10000) {
    list = list.filter((p) => p.price <= maxPrice.value)
  }

  // Stock
  if (inStockOnly.value) {
    list = list.filter((p) => p.stock > 0)
  }

  // Discount
  if (onSaleOnly.value) {
    list = list.filter((p) => p.oldPrice && p.oldPrice > p.price)
  }

  // Item 21: Sorting
  if (sortBy.value === 'bestseller') {
    list.sort((a, b) => (b.soldCount || 0) - (a.soldCount || 0))
  } else if (sortBy.value === 'newest') {
    list.sort((a, b) => new Date(b.createdAt || 0) - new Date(a.createdAt || 0))
  } else if (sortBy.value === 'price-asc') {
    list.sort((a, b) => a.price - b.price)
  } else if (sortBy.value === 'price-desc') {
    list.sort((a, b) => b.price - a.price)
  } else if (sortBy.value === 'rating') {
    list.sort((a, b) => b.rating - a.rating)
  }

  return list
})

// Item 22: Dynamic pagination text
const paginationText = computed(() => {
  const total = filteredProducts.value.length
  if (total === 0) return '0 منتج'
  const start = (currentPage.value - 1) * itemsPerPage + 1
  const end = Math.min(currentPage.value * itemsPerPage, total)
  return `عرض ${start}-${end} من ${total} منتج`
})

const totalPages = computed(() => {
  return Math.ceil(filteredProducts.value.length / itemsPerPage)
})

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

@media (max-width: 900px) {
  .mobile-filter-btn {
    display: inline-flex;
  }
}

.lay {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 28px;
  padding-top: 12px;
}

@media (max-width: 900px) {
  .lay {
    grid-template-columns: 1fr;
  }
}

.g {
  padding: 16px;
  border-bottom: 1px solid var(--ln);
}

.g:last-child {
  border-bottom: none;
}

.g h5 {
  font-size: 14px;
  font-weight: 700;
  margin: 0 0 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.sel-wrap {
  position: relative;
}

.model-select {
  width: 100%;
  height: 40px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 12px 0 28px;
  font-size: 13px;
  background: #ffffff;
  color: var(--ink);
  appearance: none;
  cursor: pointer;
  outline: none;
}

:dir(ltr) .model-select {
  padding: 0 28px 0 12px;
}

.model-select:focus-visible {
  outline: 2px solid var(--ink);
}

.sel-icon {
  position: absolute;
  inset-inline-end: 10px;
  top: 13px;
  pointer-events: none;
  color: var(--mu);
}

.filter-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  margin-bottom: 8px;
  cursor: pointer;
  user-select: none;
}

.cb-input {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.cb {
  width: 16px;
  height: 16px;
  border: 1.5px solid var(--mu);
  border-radius: 4px;
  display: inline-block;
  flex-shrink: 0;
}

.filter-label:has(.cb-input:focus-visible) .cb {
  outline: 2px solid var(--ink);
  outline-offset: 2px;
}

.cb.on {
  background: var(--ink);
  border-color: var(--ink);
  position: relative;
}

.cb.on::after {
  content: '';
  position: absolute;
  top: 1px;
  left: 4px;
  width: 4px;
  height: 8px;
  border: solid #ffffff;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.filter-label small {
  margin-inline-start: auto;
  color: var(--mu);
}

.price-inputs {
  display: flex;
  align-items: center;
  gap: 8px;
}

.price-num-input {
  width: 100%;
  height: 36px;
  border: 1px solid var(--ln);
  border-radius: 6px;
  padding: 0 8px;
  font-size: 13px;
  font-family: inherit;
  outline: none;
}

.price-num-input:focus-visible {
  outline: 2px solid var(--ink);
}

.tb {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  color: var(--mu);
  margin-bottom: 16px;
}

.grid.g4 {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

@media (max-width: 1100px) {
  .grid.g4 {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 600px) {
  .grid.g4 {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
}

.no-results {
  padding: 40px 0;
}
</style>
