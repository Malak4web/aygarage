<template>
  <div class="bikes-listing-page">
    <Breadcrumbs :items="breadcrumbItems" />

    <!-- Error state with retry -->
    <div v-if="error" class="in" style="padding: 40px 0;">
      <EmptyState
        icon="bike"
        title="حدث خطأ في تحميل الموتوسيكلات"
        :description="error"
        action-label="إعادة المحاولة"
        @action="fetchBikes"
      />
    </div>

    <template v-else>
      <div class="lhd in">
      <div>
        <h1>موتوسيكلات مستعملة مفحوصة</h1>
        <p>{{ totalBikesCount }} موتوسيكل معروض للبيع · فحصنا أكتر من {{ storeConfig.totalInspectedBikes }} موتوسيكل بتقرير 120 نقطة</p>
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
              :value="sc.value"
              v-model="selectedScoreRange"
              class="cb-input"
            />
            <span class="cb" :class="{ on: selectedScoreRange === sc.value }"></span>
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
          <span>{{ totalBikesCount }} موتوسيكل متاح</span>
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

        <!-- Empty state -->
        <div v-else-if="bikes.length === 0">
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
            v-for="b in bikes"
            :key="b.id"
            :bike="b"
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

    <!-- Sell Bike Modal Dialog (Item 29) -->
    <div v-if="showSellModal" class="modal-backdrop" @click="showSellModal = false">
      <div class="modal-card" ref="sellModalRef" @click.stop role="dialog" aria-modal="true" aria-label="بيع موتوسيكلك">
        <h3>بيع موتوسيكلك مع جراج أبو يوسف</h3>
        <p>هنفحص موتوسيكلك فحص 120 نقطة ونساعدك تبيعه بأفضل سعر وأسرع وقت بعمولة عادلة وبدون وجع دماغ.</p>
        <form @submit.prevent="handleSellSubmit" class="sell-form">
          <div class="fl">
            <div class="fld">
              <FormField id="sell-name" label="الاسم بالكامل" v-model="sellName" :error="sellErrors.name" placeholder="مثال: كريم يوسف" required />
            </div>
            <div class="fld">
              <FormField id="sell-phone" label="رقم الموبايل" type="tel" dir="ltr" v-model="sellPhone" :error="sellErrors.phone" placeholder="010 1234 5678" required />
            </div>
          </div>

          <div class="fl" style="margin-top: 12px;">
            <div class="fld">
              <label for="sell-model" class="fld-label">موديل الموتوسيكل</label>
              <select id="sell-model" v-model="sellModel" class="form-select-full">
                <option v-for="m in storeConfig.models" :key="m" :value="m">{{ m }}</option>
              </select>
            </div>
            <div class="fld">
              <label for="sell-year" class="fld-label">سنة الصنع</label>
              <select id="sell-year" v-model="sellYear" class="form-select-full">
                <option v-for="y in storeConfig.years" :key="y" :value="y">{{ y }}</option>
              </select>
            </div>
          </div>

          <div class="fld" style="margin-top: 12px;">
            <FormField id="sell-km" label="قراءة العداد (كم)" type="number" v-model="sellKm" :error="sellErrors.km" placeholder="مثال: 15000" required />
          </div>

          <div class="fld" style="margin-top: 12px;">
            <label for="sell-notes" class="fld-label">ملاحظات أو مواصفات إضافية (اختياري)</label>
            <textarea id="sell-notes" v-model="sellNotes" class="form-textarea-full" rows="2" placeholder="حالة الكاوتش، رخصة سارية، صيانات دورية..."></textarea>
          </div>

          <button type="submit" class="btn full" style="margin-top: 16px;" :disabled="isSubmittingSell">
            {{ isSubmittingSell ? 'جاري إرسال الطلب...' : 'إرسال طلب فحص وبيع' }}
          </button>
        </form>

        <div class="sell-divider">
          <span>أو تواصل مباشرة</span>
        </div>

        <div class="sell-contacts">
          <a :href="`https://wa.me/${storeConfig.whatsapp}`" target="_blank" rel="noopener" class="btn o full">
            تواصل معنا على الواتساب
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
import { ref, computed, watch, onMounted } from 'vue'
import { storeConfig } from '@/config/store.js'
import { api } from '@/api/index.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import BikeCard from '@/components/common/BikeCard.vue'
import BikeCardSkeleton from '@/components/common/BikeCardSkeleton.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import FilterSidebar from '@/components/common/FilterSidebar.vue'
import FilterChips from '@/components/common/FilterChips.vue'
import SortSelect from '@/components/common/SortSelect.vue'
import Pagination from '@/components/common/Pagination.vue'
import { useModalTrap } from '@/utils/modal.js'
import AppIcon from '@/components/common/AppIcon.vue'
import FormField from '@/components/common/FormField.vue'
import { useToastStore } from '@/stores/toast.js'
import { useUserStore } from '@/stores/user.js'

const breadcrumbItems = [
  { label: 'الرئيسية', to: '/' },
  { label: 'موتوسيكلات للبيع' }
]

const loading = ref(true)
const error = ref('')
const bikes = ref([])
const meta = ref(null)
const facets = ref(null)

const selectedFamilies = ref([])
const selectedKmRange = ref('')
const selectedScoreRange = ref('')
const selectedYears = ref([])
const minPrice = ref(0)
const maxPrice = ref(400000)
const sortBy = ref('newest')
const currentPage = ref(1)
const itemsPerPage = 12
const mobileFilterOpen = ref(false)
const showSellModal = ref(false)
const sellModalRef = ref(null)
let fetchRequestId = 0

const toastStore = useToastStore()
const userStore = useUserStore()

const sellName = ref(userStore.user?.name || '')
const sellPhone = ref(userStore.user?.phone || '')
const sellModel = ref(storeConfig.models[0] || 'ZT350-T')
const sellYear = ref(2023)
const sellKm = ref('')
const sellNotes = ref('')
const isSubmittingSell = ref(false)
const sellErrors = ref({})

const handleSellSubmit = async () => {
  if (isSubmittingSell.value) return
  isSubmittingSell.value = true
  sellErrors.value = {}
  try {
    const res = await api.createSellRequest({
      name: sellName.value,
      phone: sellPhone.value,
      bikeModel: sellModel.value,
      year: Number(sellYear.value),
      km: Number(sellKm.value) || 0,
      notes: sellNotes.value || undefined
    })
    toastStore.show(res.message || 'تم استلام طلب بيع موتوسيكلك بنجاح!', 'success')
    showSellModal.value = false
    sellKm.value = ''
    sellNotes.value = ''
  } catch (err) {
    if (err.errors) sellErrors.value = err.errors
    toastStore.show(err.message || 'تعذر إرسال طلب بيع الموتوسيكل', 'error')
  } finally {
    isSubmittingSell.value = false
  }
}

watch(showSellModal, (open) => {
  if (open) {
    if (userStore.user?.name && !sellName.value) sellName.value = userStore.user.name
    if (userStore.user?.phone && !sellPhone.value) sellPhone.value = userStore.user.phone
  }
})

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

const fetchBikes = async () => {
  const currentReq = ++fetchRequestId
  loading.value = true
  error.value = ''
  try {
    const params = {
      family: selectedFamilies.value.length > 0 ? selectedFamilies.value : undefined,
      kmRange: selectedKmRange.value || undefined,
      minScore: selectedScoreRange.value === '90+' ? 90 : (selectedScoreRange.value === '80-89' ? 80 : undefined),
      maxScore: selectedScoreRange.value === '80-89' ? 89 : undefined,
      year: selectedYears.value.length > 0 ? selectedYears.value : undefined,
      minPrice: minPrice.value > 0 ? minPrice.value : undefined,
      maxPrice: maxPrice.value < 400000 ? maxPrice.value : undefined,
      sort: sortBy.value,
      page: currentPage.value,
      perPage: itemsPerPage
    }
    const res = await api.getBikes(params)
    if (currentReq !== fetchRequestId) return
    bikes.value = res
    meta.value = res.meta || { total: res.length, page: currentPage.value, perPage: itemsPerPage, lastPage: 1 }
    facets.value = res.facets || { families: {}, kmRanges: {}, scores: {}, years: {} }
  } catch (e) {
    if (currentReq !== fetchRequestId) return
    error.value = 'تعذر التحميل، اتأكد من النت وحاول تاني'
  } finally {
    if (currentReq === fetchRequestId) {
      loading.value = false
    }
  }
}

onMounted(() => {
  fetchBikes()
})

const totalBikesCount = computed(() => meta.value?.total ?? bikes.value.length)
const totalPages = computed(() => meta.value?.lastPage ?? 1)

// Server-derived facet options (Item 9 & 19)
const modelFamilies = computed(() => {
  const families = ['ZT125', 'ZT310', 'ZT350', 'ZT703']
  const map = facets.value?.families || {}
  return families.map((f) => ({
    name: f,
    count: map[f] || 0
  }))
})

const kmOptions = computed(() => [
  {
    label: 'أقل من 5,000 كم',
    value: 'under5k',
    count: facets.value?.kmRanges?.under5k || 0
  },
  {
    label: '5,000 – 20,000 كم',
    value: '5kTo20k',
    count: facets.value?.kmRanges?.['5kTo20k'] || 0
  },
  {
    label: 'أكتر من 20,000 كم',
    value: 'over20k',
    count: facets.value?.kmRanges?.over20k || 0
  }
])

const scoreOptions = computed(() => [
  {
    label: '90 وأكتر',
    value: '90+',
    count: facets.value?.scores?.['90+'] || 0
  },
  {
    label: '80 – 89',
    value: '80-89',
    count: facets.value?.scores?.['80-89'] || 0
  }
])

const yearOptions = computed(() => {
  const map = facets.value?.years || {}
  const keys = Object.keys(map).map((k) => parseInt(k, 10)).filter((k) => !isNaN(k))
  if (keys.length === 0) {
    return [
      { label: '2024', value: 2024, count: 0 },
      { label: '2023', value: 2023, count: 0 },
      { label: '2022', value: 2022, count: 0 },
      { label: '2021 وأقدم', value: 2021, count: 0 }
    ]
  }
  keys.sort((a, b) => b - a)
  return keys.map((y) => ({
    label: y <= 2021 ? `${y} وأقدم` : String(y),
    value: y,
    count: map[String(y)] || 0
  }))
})

const activeFilterChips = computed(() => {
  const chips = []
  selectedFamilies.value.forEach((f) => {
    chips.push({ id: `family-${f}`, label: f })
  })
  if (selectedKmRange.value) {
    const k = kmOptions.value.find((opt) => opt.value === selectedKmRange.value)
    if (k) chips.push({ id: 'km', label: k.label })
  }
  if (selectedScoreRange.value === '90+') {
    chips.push({ id: 'score', label: 'فحص 90 وأكتر' })
  } else if (selectedScoreRange.value === '80-89') {
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
    selectedScoreRange.value = ''
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
  selectedScoreRange.value = ''
  selectedYears.value = []
  minPrice.value = 0
  maxPrice.value = 400000
}

let isResettingPage = false
const onFilterChanged = () => {
  if (currentPage.value !== 1) {
    isResettingPage = true
    currentPage.value = 1
    isResettingPage = false
  }
  fetchBikes()
}

let priceDebounceTimer = null
watch([minPrice, maxPrice], () => {
  clearTimeout(priceDebounceTimer)
  priceDebounceTimer = setTimeout(() => {
    onFilterChanged()
  }, 400)
})

watch(
  [selectedFamilies, selectedKmRange, selectedScoreRange, selectedYears, sortBy],
  () => {
    onFilterChanged()
  },
  { deep: true }
)

watch(
  () => currentPage.value,
  () => {
    if (!isResettingPage) {
      fetchBikes()
    }
  }
)
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
  max-width: 480px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
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
  margin: 0 0 16px;
}

.sell-form .fl {
  display: flex;
  gap: 12px;
}

.sell-form .fld {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.fld-label {
  font-size: 13.5px;
  font-weight: 600;
  color: var(--tx);
}

.form-select-full,
.form-textarea-full {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-sm);
  background: #ffffff;
  font-size: 14px;
  font-family: inherit;
  color: var(--tx);
}

.form-textarea-full {
  resize: vertical;
}

.sell-divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 16px 0;
  color: var(--mu);
  font-size: 13px;
}

.sell-divider::before,
.sell-divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid var(--ln);
}

.sell-divider span {
  padding: 0 10px;
}

.close-sell-btn {
  width: 100%;
  margin-top: 10px;
}
</style>
