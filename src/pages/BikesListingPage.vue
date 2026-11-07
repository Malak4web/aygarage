<template>
  <div class="bikes-listing-page">
    <Breadcrumbs :items="breadcrumbItems" />

    <div class="lhd in">
      <div>
        <h1>موتوسيكلات مستعملة مفحوصة</h1>
        <p>{{ storeConfig.totalInspectedBikes }} موتوسيكل · كل واحد عليه تقرير فحص 120 نقطة من الجراج</p>
      </div>
      <div class="header-actions">
        <button type="button" class="btn o sm mobile-filter-btn" @click="mobileFilterOpen = true">
          <AppIcon name="filter" :size="16" />
          <span>فلترة ({{ activeFiltersCount }})</span>
        </button>
        <button type="button" class="btn o sm sell-bike-btn" @click="showSellModal = true">
          عندك موتوسيكل تبيعه؟
        </button>
      </div>
    </div>

    <!-- Active Filter Chips -->
    <div class="in">
      <FilterChips
        :chips="activeFilterChips"
        @remove="handleRemoveChip"
        @clear-all="resetFilters"
      />
    </div>

    <div class="lay in">
      <!-- Filter Sidebar (Desktop & Mobile Bottom Sheet) -->
      <FilterSidebar
        :is-open="mobileFilterOpen"
        title="فلاتر الموتوسيكلات"
        @close="mobileFilterOpen = false"
      >
        <!-- Model Family -->
        <div class="g">
          <h5>الموديل</h5>
          <label v-for="m in modelFamilies" :key="m.name" class="filter-label">
            <input
              type="checkbox"
              :value="m.name"
              v-model="selectedFamilies"
              class="cb-input"
            />
            <span class="cb" :class="{ on: selectedFamilies.includes(m.name) }"></span>
            <span>{{ m.name }}</span>
            <small>{{ m.count }}</small>
          </label>
        </div>

        <!-- Price Range -->
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

        <!-- Kilometers -->
        <div class="g">
          <h5>الكيلومترات</h5>
          <label v-for="km in kmOptions" :key="km.label" class="filter-label">
            <input
              type="radio"
              name="kmRange"
              :value="km.value"
              v-model="selectedKmRange"
              class="cb-input"
            />
            <span class="cb" :class="{ on: selectedKmRange === km.value }"></span>
            <span>{{ km.label }}</span>
            <small>{{ km.count }}</small>
          </label>
        </div>

        <!-- Inspection Score -->
        <div class="g">
          <h5>درجة الفحص</h5>
          <label v-for="sc in scoreOptions" :key="sc.label" class="filter-label">
            <input
              type="radio"
              name="scoreRange"
              :value="sc.min"
              v-model="selectedMinScore"
              class="cb-input"
            />
            <span class="cb" :class="{ on: selectedMinScore === sc.min }"></span>
            <span>{{ sc.label }}</span>
            <small>{{ sc.count }}</small>
          </label>
        </div>

        <!-- Year -->
        <div class="g">
          <h5>السنة</h5>
          <label v-for="yr in yearOptions" :key="yr.label" class="filter-label">
            <input
              type="checkbox"
              :value="yr.value"
              v-model="selectedYears"
              class="cb-input"
            />
            <span class="cb" :class="{ on: selectedYears.includes(yr.value) }"></span>
            <span>{{ yr.label }}</span>
            <small>{{ yr.count }}</small>
          </label>
        </div>
      </FilterSidebar>

      <!-- Bikes Grid Main -->
      <main class="bikes-main">
        <div class="tb">
          <span>{{ filteredBikes.length }} موتوسيكل متاح</span>
          <SortSelect
            v-model="sortBy"
            :options="sortOptions"
            id="bike-sort-select"
          />
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="grid g3">
          <BikeCardSkeleton v-for="i in 6" :key="i" />
        </div>

        <!-- Error state -->
        <div v-else-if="error">
          <EmptyState
            icon="bike"
            title="حدث خطأ في تحميل الموتوسيكلات"
            :description="error"
            action-label="إعادة المحاولة"
            @action="fetchBikes"
          />
        </div>

        <!-- Empty state -->
        <div v-else-if="filteredBikes.length === 0">
          <EmptyState
            icon="bike"
            title="لا توجد موتوسيكلات مطابقة"
            description="جرّب تعديل خيارات الفلترة أو الموديلات لعرض الموتوسيكلات المتاحة."
            action-label="إعادة ضبط الفلاتر"
            @action="resetFilters"
          />
        </div>

        <!-- Bikes Grid -->
        <div v-else class="grid g3">
          <BikeCard
            v-for="b in filteredBikes"
            :key="b.id"
            :bike="b"
          />
        </div>
      </main>
    </div>

    <!-- Sell Bike Modal Dialog (Item 29) -->
    <div v-if="showSellModal" class="modal-backdrop" @click="showSellModal = false">
      <div class="modal-card" ref="sellModalRef" @click.stop role="dialog" aria-modal="true" aria-label="بيع موتوسيكلك">
        <h3>بيع موتوسيكلك مع جراج أبو يوسف</h3>
        <p>هنفحص موتوسيكلك فحص 120 نقطة ونساعدك تبيعه بأفضل سعر وأسرع وقت بعمولة عادلة وبدون وجع دماغ.</p>
        <div class="sell-contacts">
          <a :href="`https://wa.me/${storeConfig.whatsapp}`" target="_blank" rel="noopener" class="btn full">
            تواصل معنا على الواتساب
          </a>
          <a :href="`tel:${storeConfig.phone}`" class="btn o full" style="margin-top: 10px;">
            اتصل بالجراج: {{ storeConfig.phone }}
          </a>
        </div>
        <button type="button" class="btn o sm close-sell-btn" @click="showSellModal = false">
          إغلاق
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { storeConfig } from '@/config/store.js'
import { api } from '@/api/index.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import BikeCard from '@/components/common/BikeCard.vue'
import BikeCardSkeleton from '@/components/common/BikeCardSkeleton.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import FilterSidebar from '@/components/common/FilterSidebar.vue'
import FilterChips from '@/components/common/FilterChips.vue'
import SortSelect from '@/components/common/SortSelect.vue'
import { useModalTrap } from '@/utils/modal.js'
import AppIcon from '@/components/common/AppIcon.vue'

const breadcrumbItems = [
  { label: 'الرئيسية', to: '/' },
  { label: 'موتوسيكلات للبيع' }
]

const loading = ref(true)
const error = ref('')
const allBikes = ref([])

const fetchBikes = async () => {
  loading.value = true
  error.value = ''
  try {
    allBikes.value = await api.getBikes()
  } catch (e) {
    error.value = 'تعذر الاتصال بخادم المعروضات، يرجى المحاولة لاحقاً.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchBikes()
})

// Item 20: Defaults empty
const selectedFamilies = ref([])
const selectedKmRange = ref('')
const selectedMinScore = ref(0)
const selectedYears = ref([])
const minPrice = ref(0)
const maxPrice = ref(400000)
const sortBy = ref('newest')
const mobileFilterOpen = ref(false)
const showSellModal = ref(false)
const sellModalRef = ref(null)

useModalTrap(showSellModal, sellModalRef, () => {
  showSellModal.value = false
})

const sortOptions = [
  { value: 'newest', label: 'ترتيب: الأحدث' },
  { value: 'popular', label: 'ترتيب: الأكثر مبيعاً' },
  { value: 'score-desc', label: 'درجة الفحص: الأعلى' },
  { value: 'price-asc', label: 'السعر: من الأقل للأعلى' },
  { value: 'price-desc', label: 'السعر: من الأعلى للأقل' }
]

const modelFamilies = computed(() => {
  const families = ['ZT125', 'ZT310', 'ZT350', 'ZT703']
  return families.map((f) => ({
    name: f,
    count: allBikes.value.filter((b) => b.family === f).length
  }))
})

// Computed facet counts (Item 19)
const kmOptions = computed(() => [
  {
    label: 'أقل من 5,000 كم',
    value: 'under5k',
    count: allBikes.value.filter((b) => (Number(b.km) || 0) < 5000).length
  },
  {
    label: '5,000 – 20,000 كم',
    value: '5kTo20k',
    count: allBikes.value.filter((b) => {
      const k = Number(b.km) || 0
      return k >= 5000 && k <= 20000
    }).length
  },
  {
    label: 'أكتر من 20,000 كم',
    value: 'over20k',
    count: allBikes.value.filter((b) => (Number(b.km) || 0) > 20000).length
  }
])

const scoreOptions = computed(() => [
  {
    label: '90 وأكتر',
    min: 90,
    count: allBikes.value.filter((b) => (b.inspectionScore || 0) >= 90).length
  },
  {
    label: '80 – 89',
    min: 80,
    count: allBikes.value.filter((b) => {
      const s = b.inspectionScore || 0
      return s >= 80 && s < 90
    }).length
  }
])

const yearOptions = computed(() => [
  {
    label: '2024',
    value: 2024,
    count: allBikes.value.filter((b) => b.year === 2024).length
  },
  {
    label: '2023',
    value: 2023,
    count: allBikes.value.filter((b) => b.year === 2023).length
  },
  {
    label: '2022',
    value: 2022,
    count: allBikes.value.filter((b) => b.year === 2022).length
  },
  {
    label: '2021 وأقدم',
    value: 2021,
    count: allBikes.value.filter((b) => b.year <= 2021).length
  }
])

const activeFilterChips = computed(() => {
  const chips = []
  selectedFamilies.value.forEach((f) => {
    chips.push({ id: `family-${f}`, label: f })
  })
  if (selectedKmRange.value) {
    const k = kmOptions.value.find((opt) => opt.value === selectedKmRange.value)
    if (k) chips.push({ id: 'km', label: k.label })
  }
  if (selectedMinScore.value === 90) {
    chips.push({ id: 'score', label: 'فحص 90 وأكتر' })
  } else if (selectedMinScore.value === 80) {
    chips.push({ id: 'score', label: 'فحص 80 – 89' })
  }
  selectedYears.value.forEach((yr) => {
    chips.push({ id: `year-${yr}`, label: yr === 2021 ? '2021 وأقدم' : String(yr) })
  })
  if (minPrice.value > 0 || maxPrice.value < 400000) {
    chips.push({ id: 'price', label: `السعر: ${minPrice.value.toLocaleString('en')} - ${maxPrice.value.toLocaleString('en')}` })
  }
  return chips
})

const activeFiltersCount = computed(() => activeFilterChips.value.length)

const handleRemoveChip = (id) => {
  if (id.startsWith('family-')) {
    const f = id.replace('family-', '')
    selectedFamilies.value = selectedFamilies.value.filter((item) => item !== f)
  } else if (id === 'km') {
    selectedKmRange.value = ''
  } else if (id === 'score') {
    selectedMinScore.value = 0
  } else if (id.startsWith('year-')) {
    const yr = Number(id.replace('year-', ''))
    selectedYears.value = selectedYears.value.filter((item) => item !== yr)
  } else if (id === 'price') {
    minPrice.value = 0
    maxPrice.value = 400000
  }
}

const resetFilters = () => {
  selectedFamilies.value = []
  selectedKmRange.value = ''
  selectedMinScore.value = 0
  selectedYears.value = []
  minPrice.value = 0
  maxPrice.value = 400000
}

const filteredBikes = computed(() => {
  let list = [...allBikes.value]

  // Family
  if (selectedFamilies.value.length > 0) {
    list = list.filter((b) => selectedFamilies.value.includes(b.family))
  }

  // Price range
  if (minPrice.value > 0) {
    list = list.filter((b) => b.price >= minPrice.value)
  }
  if (maxPrice.value < 400000) {
    list = list.filter((b) => b.price <= maxPrice.value)
  }

  // KM range with numeric comparison
  if (selectedKmRange.value === 'under5k') {
    list = list.filter((b) => Number(b.km) < 5000)
  } else if (selectedKmRange.value === '5kTo20k') {
    list = list.filter((b) => Number(b.km) >= 5000 && Number(b.km) <= 20000)
  } else if (selectedKmRange.value === 'over20k') {
    list = list.filter((b) => Number(b.km) > 20000)
  }

  // Inspection Score
  if (selectedMinScore.value === 90) {
    list = list.filter((b) => b.inspectionScore >= 90)
  } else if (selectedMinScore.value === 80) {
    list = list.filter((b) => b.inspectionScore >= 80 && b.inspectionScore < 90)
  }

  // Year: "2021 وأقدم" means <= 2021
  if (selectedYears.value.length > 0) {
    list = list.filter((b) => {
      return selectedYears.value.some((yr) => {
        if (yr === 2021) return b.year <= 2021
        return b.year === yr
      })
    })
  }

  // Sorting
  if (sortBy.value === 'popular') {
    list.sort((a, b) => (b.soldCount || 0) - (a.soldCount || 0))
  } else if (sortBy.value === 'score-desc') {
    list.sort((a, b) => b.inspectionScore - a.inspectionScore)
  } else if (sortBy.value === 'price-asc') {
    list.sort((a, b) => a.price - b.price)
  } else if (sortBy.value === 'price-desc') {
    list.sort((a, b) => b.price - a.price)
  } else if (sortBy.value === 'newest') {
    list.sort((a, b) => new Date(b.createdAt || 0) - new Date(a.createdAt || 0))
  }

  return list
})
</script>

<style scoped>
.lhd {
  padding-top: 10px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 12px;
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

.header-actions {
  display: flex;
  gap: 8px;
  align-items: center;
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

.grid.g3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

@media (max-width: 1100px) {
  .grid.g3 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .grid.g3 {
    grid-template-columns: 1fr;
  }
}

/* Sell modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: grid;
  place-items: center;
  z-index: 999;
}

.modal-card {
  background: #ffffff;
  padding: 24px;
  border-radius: var(--radius-card);
  max-width: 440px;
  width: 90%;
}

.modal-card h3 {
  margin: 0 0 8px;
  font-size: 20px;
  font-weight: 800;
}

.modal-card p {
  color: var(--mu);
  font-size: 14px;
  line-height: 1.6;
  margin: 0 0 20px;
}

.close-sell-btn {
  width: 100%;
  margin-top: 12px;
}
</style>
