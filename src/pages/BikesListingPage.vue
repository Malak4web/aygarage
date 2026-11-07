<template>
  <div class="bikes-listing-page">
    <Breadcrumbs :items="breadcrumbItems" />

    <div class="lhd in">
      <div>
        <h1>موتوسيكلات مستعملة مفحوصة</h1>
        <p>22 موتوسيكل · كل واحد عليه تقرير فحص 120 نقطة من الجراج</p>
      </div>
      <button type="button" class="btn o sm sell-bike-btn" @click="handleSellBike">
        عندك موتوسيكل تبيعه؟
      </button>
    </div>

    <div class="lay in">
      <!-- Filter Sidebar -->
      <aside class="flt">
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
          <div class="range">
            <i></i>
          </div>
          <div class="rin">
            <div>90,000</div>
            <div>270,000</div>
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
      </aside>

      <!-- Bikes Grid Main -->
      <main class="bikes-main">
        <div class="tb">
          <span>{{ filteredBikes.length }} موتوسيكل</span>
          <div class="sort-wrap">
            <select v-model="sortBy" class="sort-select">
              <option value="newest">ترتيب: الأحدث</option>
              <option value="score-desc">درجة الفحص: الأعلى</option>
              <option value="price-asc">السعر: من الأقل</option>
              <option value="price-desc">السعر: من الأعلى</option>
            </select>
            <AppIcon name="down" :size="14" class="sort-icon" />
          </div>
        </div>

        <div v-if="filteredBikes.length === 0">
          <EmptyState
            icon="bike"
            title="لا توجد موتوسيكلات مطابقة"
            description="جرّب تعديل خيارات الفلترة أو الموديلات لعرض الموتوسيكلات المتاحة."
            action-label="إعادة ضبط الفلاتر"
            @action="resetFilters"
          />
        </div>

        <div v-else class="grid g3">
          <BikeCard
            v-for="b in filteredBikes"
            :key="b.id"
            :bike="b"
          />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { bikes } from '@/data/bikes.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import BikeCard from '@/components/common/BikeCard.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const breadcrumbItems = [
  { label: 'الرئيسية', to: '/' },
  { label: 'موتوسيكلات للبيع' }
]

const selectedFamilies = ref(['ZT350'])
const selectedKmRange = ref('')
const selectedMinScore = ref(90)
const selectedYears = ref([])
const sortBy = ref('newest')

const modelFamilies = [
  { name: 'ZT125', count: 4 },
  { name: 'ZT310', count: 7 },
  { name: 'ZT350', count: 9 },
  { name: 'ZT703', count: 2 }
]

const kmOptions = [
  { label: 'أقل من 5,000', value: 'under5k', count: 4 },
  { label: '5,000 – 20,000', value: '5kTo20k', count: 12 },
  { label: 'أكتر من 20,000', value: 'over20k', count: 6 }
]

const scoreOptions = [
  { label: '90 وأكتر', min: 90, count: 8 },
  { label: '80 – 89', min: 80, count: 9 }
]

const yearOptions = [
  { label: '2024', value: 2024, count: 5 },
  { label: '2023', value: 2023, count: 9 },
  { label: '2022', value: 2022, count: 5 },
  { label: '2021 وأقدم', value: 2021, count: 3 }
]

const resetFilters = () => {
  selectedFamilies.value = []
  selectedKmRange.value = ''
  selectedMinScore.value = 0
  selectedYears.value = []
}

const filteredBikes = computed(() => {
  let list = [...bikes]

  if (selectedFamilies.value.length > 0) {
    list = list.filter((b) => selectedFamilies.value.includes(b.family))
  }

  if (selectedMinScore.value > 0) {
    list = list.filter((b) => b.inspectionScore >= selectedMinScore.value)
  }

  if (selectedYears.value.length > 0) {
    list = list.filter((b) => selectedYears.value.includes(b.year))
  }

  if (sortBy.value === 'score-desc') {
    list.sort((a, b) => b.inspectionScore - a.inspectionScore)
  } else if (sortBy.value === 'price-asc') {
    list.sort((a, b) => a.price - b.price)
  } else if (sortBy.value === 'price-desc') {
    list.sort((a, b) => b.price - a.price)
  }

  return list
})

const handleSellBike = () => {
  alert('تواصل مع أبو يوسف مباشرة على الواتساب 010 0000 0000 لفحص وتثمين موتوسيكلك.')
}
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

.grid.g3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

@media (max-width: 900px) {
  .lay {
    grid-template-columns: 1fr;
  }
  .flt {
    display: none;
  }
  .grid.g3 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .grid.g3 {
    grid-template-columns: 1fr;
  }
}
</style>
