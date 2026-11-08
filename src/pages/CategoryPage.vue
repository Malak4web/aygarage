<template>
  <div class="category-page">
    <Breadcrumbs :items="breadcrumbItems" />

    <!-- Error State with Retry (Item 2) -->
    <div v-if="error" class="in" style="padding: 40px 0;">
      <EmptyState
        icon="alert"
        title="حدث خطأ في تحميل المنتجات"
        :description="error"
        action-label="إعادة المحاولة"
        @action="fetchProducts"
      />
    </div>

    <template v-else>
      <div class="lhd in">
      <div>
        <h1>{{ categoryName }}</h1>
        <p>{{ totalCount }} منتج</p>
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
        @clear-all="clearAllFilters"
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
        <div v-else-if="products.length === 0" class="no-results">
          <EmptyState
            title="لا توجد منتجات مطابقة للبحث أو الفلترة"
            description="جرّب تقليل شروط الفلترة أو مسح الموديل لعرض كل المنتجات المتوفرة."
            action-label="مسح كل الفلاتر"
            @action="clearAllFilters"
          />
        </div>

        <!-- Products Grid -->
        <div v-else class="grid g4">
          <ProductCard
            v-for="product in products"
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
    </template>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { storeConfig } from '@/config/store.js'
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
const products = ref([])
const categoriesList = ref([])
const meta = ref(null)
const serverFacets = ref(null)

const filterOnlyFitting = ref(!!userStore.selectedBike)
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

const fetchProducts = async () => {
  loading.value = true
  error.value = ''
  try {
    const params = {
      category: route.params.category && route.params.category !== 'all' ? route.params.category : undefined,
      q: route.query.q || undefined,
      model: filterOnlyFitting.value && userStore.selectedBike ? userStore.selectedBike : undefined,
      brand: selectedBrand.value || undefined,
      viscosity: selectedViscosity.value || undefined,
      type: selectedOilType.value || undefined,
      minPrice: minPrice.value > 0 ? minPrice.value : undefined,
      maxPrice: maxPrice.value < 10000 ? maxPrice.value : undefined,
      inStock: inStockOnly.value ? 1 : undefined,
      onSale: onSaleOnly.value ? 1 : undefined,
      sort: sortBy.value,
      page: currentPage.value,
      perPage: itemsPerPage
    }

    const [prodsRes, catsRes] = await Promise.all([
      api.getProducts(params),
      categoriesList.value.length ? Promise.resolve(categoriesList.value) : api.getCategories()
    ])

    products.value = prodsRes
    meta.value = prodsRes.meta || { total: prodsRes.length, page: currentPage.value, perPage: itemsPerPage, lastPage: 1 }
    serverFacets.value = prodsRes.facets || { brands: {}, viscosities: {}, types: {}, models: {} }
    categoriesList.value = catsRes
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
  const cat = categoriesList.value.find((c) => c.slug === route.params.category)
  return cat ? cat.name : 'جميع المنتجات'
})

const breadcrumbItems = computed(() => [
  { label: 'الرئيسية', to: '/' },
  { label: categoryName.value }
])

const userBikeYear = computed(() => userStore.userBikeYear)

// Server-side derived facets
const viscosities = computed(() => {
  const map = serverFacets.value?.viscosities || {}
  return Object.entries(map).map(([name, count]) => ({ name, count }))
})

const brands = computed(() => {
  const map = serverFacets.value?.brands || {}
  return Object.entries(map).map(([name, count]) => ({ name, count }))
})

const oilTypes = computed(() => {
  const map = serverFacets.value?.types || {}
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

const clearAllFilters = () => {
  filterOnlyFitting.value = false
  selectedViscosity.value = ''
  selectedBrand.value = ''
  selectedOilType.value = ''
  inStockOnly.value = false
  onSaleOnly.value = false
  minPrice.value = 0
  maxPrice.value = 10000
}

const resetCategoryFilters = () => {
  filterOnlyFitting.value = !!userStore.selectedBike
  selectedViscosity.value = ''
  selectedBrand.value = ''
  selectedOilType.value = ''
  inStockOnly.value = false
  onSaleOnly.value = false
  minPrice.value = 0
  maxPrice.value = 10000
}

// Reset page & fetch when filters change
watch(
  [filterOnlyFitting, selectedViscosity, selectedBrand, selectedOilType, inStockOnly, onSaleOnly, sortBy, minPrice, maxPrice],
  () => {
    currentPage.value = 1
    fetchProducts()
  }
)

watch(
  () => currentPage.value,
  () => {
    fetchProducts()
  }
)

watch(
  () => [route.params.category, route.query.q],
  () => {
    resetCategoryFilters()
    currentPage.value = 1
    fetchProducts()
  }
)

watch(
  () => userStore.selectedBike,
  () => {
    if (filterOnlyFitting.value) {
      currentPage.value = 1
      fetchProducts()
    }
  }
)

const totalCount = computed(() => meta.value?.total ?? products.value.length)
const totalPages = computed(() => meta.value?.lastPage ?? 1)

// Dynamic pagination text from meta
const paginationText = computed(() => {
  const total = totalCount.value
  if (total === 0) return '0 منتج'
  const p = meta.value?.page || currentPage.value
  const pp = meta.value?.perPage || itemsPerPage
  const start = (p - 1) * pp + 1
  const end = Math.min(p * pp, total)
  return `عرض ${start}-${end} من ${total} منتج`
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
  inset-inline-start: 4px;
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
