<template>
  <div v-if="!bike" class="not-found-page in">
    <EmptyState
      icon="bike"
      title="الموتوسيكل غير موجود"
      description="لم نتمكن من العثور على هذا الموتوسيكل. ربما تم بيعه أو حذفه من القائمة."
      action-label="تصفح الموتوسيكلات المعروضة"
      action-to="/bikes"
    />
  </div>
  <div class="bike-detail-page" v-else>
    <Breadcrumbs :items="breadcrumbItems" />

    <div class="bdp in">
      <!-- Bike Gallery -->
      <div class="bike-gal">
        <div class="big">
          <BikeIllustration :color="bike.col" :width="560" />
        </div>
        <div class="thr">
          <div
            v-for="(_, i) in 5"
            :key="i"
            :class="{ on: activeThumb === i }"
            @click="activeThumb = i"
          ></div>
        </div>
      </div>

      <!-- Bike Info & Booking -->
      <div class="bike-info">
        <span class="tg">{{ bike.inspectionScore }}/100 · فحص أبو يوسف</span>
        <h1 class="bike-title">{{ bike.model }} · {{ bike.year }}</h1>
        <div class="bike-meta-line">
          {{ bike.km }} كم · {{ bike.owner || 'أول مالك' }} · رخصة سارية لحد {{ bike.licenseUntil }}
        </div>

        <div class="price">
          <b class="price-val">{{ formattedPrice }} <small>ج.م</small></b>
        </div>
        <div class="installment-line">
          أو تقسيط من {{ bike.installmentMonthly?.toLocaleString('en') || '10,200' }} ج.م في الشهر
        </div>

        <div class="bike-actions">
          <RouterLink :to="`/booking?bike=${encodeURIComponent(bike.model)}`" class="btn">
            احجز معاينة في الجراج
          </RouterLink>
          <a :href="`https://wa.me/${storeConfig.whatsapp}`" target="_blank" rel="noopener" class="btn o">
            كلّمنا واتساب
          </a>
        </div>

        <table class="spec">
          <tbody>
            <tr v-for="(val, key) in bike.specs" :key="key">
              <td>{{ key }}</td>
              <td>{{ val }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tabs: Inspection report, Description, Service History -->
    <div class="tabs in">
      <button
        type="button"
        class="tab-btn"
        :class="{ on: activeTab === 'inspection' }"
        @click="activeTab = 'inspection'"
      >
        تقرير الفحص
      </button>
      <button
        type="button"
        class="tab-btn"
        :class="{ on: activeTab === 'desc' }"
        @click="activeTab = 'desc'"
      >
        الوصف
      </button>
      <button
        type="button"
        class="tab-btn"
        :class="{ on: activeTab === 'history' }"
        @click="activeTab = 'history'"
      >
        سجل الصيانة
      </button>
    </div>

    <!-- Tab Content -->
    <div class="tabc in">
      <!-- Inspection Bars List -->
      <div class="rep" v-if="activeTab === 'inspection'">
        <div v-for="item in bike.inspection" :key="item.section" class="r">
          <span>{{ item.section }}</span>
          <div class="pb">
            <i :class="{ w: item.warning }" :style="{ width: `${item.score}%` }"></i>
          </div>
          <span class="tg" :class="{ w: item.warning }" style="text-align: center">
            {{ item.warning ? 'ملاحظة' : 'سليم' }}
          </span>
        </div>
      </div>

      <div class="desc-tab" v-else-if="activeTab === 'desc'">
        <p>{{ bike.notes }}</p>
      </div>

      <div class="history-tab" v-else-if="activeTab === 'history'">
        <table class="history-table">
          <thead>
            <tr>
              <th>التاريخ</th>
              <th>العداد</th>
              <th>الشغل</th>
              <th>الفني</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="h in (bike.serviceHistory || defaultHistory)" :key="h.date">
              <td>{{ h.date }}</td>
              <td>{{ h.odo }}</td>
              <td>{{ h.service }}</td>
              <td>{{ h.tech }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Abu Yousef Notes Box -->
      <div class="box notes-box">
        <h3>ملاحظات أبو يوسف</h3>
        <p class="notes-text">
          {{ bike.notes }}
        </p>
        <button type="button" class="btn o sm dl-report-btn" @click="handleDownloadReport">
          تحميل التقرير PDF
        </button>
      </div>
    </div>

    <!-- Similar Bikes -->
    <ProductRow title="موتوسيكلات مشابهة" columns="3">
      <BikeCard
        v-for="b in similarBikes"
        :key="b.id"
        :bike="b"
      />
    </ProductRow>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { bikes } from '@/data/bikes.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import BikeIllustration from '@/components/common/BikeIllustration.vue'
import BikeCard from '@/components/common/BikeCard.vue'
import ProductRow from '@/components/common/ProductRow.vue'
import EmptyState from '@/components/common/EmptyState.vue'

const route = useRoute()

const bike = computed(() => {
  return bikes.find((b) => b.slug === route.params.slug) || null
})

const activeThumb = ref(0)
const activeTab = ref('inspection')

const formattedPrice = computed(() => {
  if (!bike.value) return ''
  return typeof bike.value.price === 'number'
    ? bike.value.price.toLocaleString('en')
    : bike.value.price
})

const breadcrumbItems = computed(() => [
  { label: 'الرئيسية', to: '/' },
  { label: 'موتوسيكلات للبيع', to: '/bikes' },
  { label: `${bike.value.model} ${bike.value.year}` }
])

const similarBikes = computed(() => {
  return bikes.filter((b) => b.id !== bike.value.id).slice(0, 3)
})

const defaultHistory = [
  { date: '12 أكتوبر 2026', odo: '12,000 كم', service: 'تغيير زيت + فلتر', tech: 'أبو يوسف' },
  { date: '5 يوليو 2026', odo: '9,300 كم', service: 'تيل فرامل أمامي وخلفي', tech: 'محمد' },
  { date: '20 مارس 2026', odo: '6,000 كم', service: 'صيانة شاملة', tech: 'أبو يوسف' }
]

const handleDownloadReport = () => {
  alert(`جاري تحميل تقرير الفحص المعتمد لموتوسيكل ${bike.value.model} (120 نقطة) بصيغة PDF.`)
}
</script>

<style scoped>
.bdp {
  display: grid;
  grid-template-columns: 1.25fr 1fr;
  gap: 40px;
  padding-top: 18px;
}

.bike-gal .big {
  aspect-ratio: 16 / 10;
  border-radius: var(--radius-box);
  background: var(--sf);
  display: grid;
  place-items: center;
}

.thr {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 10px;
  margin-top: 10px;
}

.thr div {
  aspect-ratio: 4 / 3;
  border-radius: 10px;
  background: var(--sf);
  cursor: pointer;
  transition: box-shadow 0.15s ease;
}

.thr div.on {
  box-shadow: 0 0 0 2px var(--ink);
}

.bike-title {
  font-size: 30px;
  font-weight: 800;
  margin: 10px 0 2px;
}

.bike-meta-line {
  color: var(--mu);
  font-size: 14px;
}

.price {
  margin-top: 12px;
}

.price-val {
  font-size: 32px;
  font-weight: 800;
}

.price-val small {
  font-size: 14px;
  font-weight: 400;
}

.installment-line {
  font-size: 14px;
  color: var(--mu);
  margin-top: -4px;
}

.bike-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin: 20px 0;
}

table.spec {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

table.spec td {
  padding: 11px 0;
  border-bottom: 1px solid var(--ln);
}

table.spec td:first-child {
  color: var(--mu);
  width: 40%;
}

.tabs {
  display: flex;
  gap: 26px;
  border-bottom: 1px solid var(--ln);
  margin-top: 44px;
}

.tab-btn {
  padding: 12px 0;
  font-size: 15px;
  color: var(--mu);
  background: none;
  border: none;
  cursor: pointer;
}

.tab-btn.on {
  color: var(--ink);
  font-weight: 700;
  box-shadow: inset 0 -2px 0 var(--ink);
}

.tabc {
  display: grid;
  grid-template-columns: 1.3fr 1fr;
  gap: 44px;
  padding-top: 22px;
}

.rep .r {
  display: grid;
  grid-template-columns: 140px 1fr 70px;
  gap: 14px;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid var(--ln);
  font-size: 14px;
}

.pb {
  height: 6px;
  border-radius: 6px;
  background: var(--sf2);
  overflow: hidden;
}

.pb i {
  display: block;
  height: 100%;
  background: var(--ok);
}

.pb i.w {
  background: #d88a12;
}

.notes-box {
  margin: 0;
  align-self: flex-start;
}

.notes-text {
  margin: 0;
  color: var(--mu);
  line-height: 1.8;
  font-size: 14.5px;
}

.dl-report-btn {
  margin-top: 14px;
}

.history-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.history-table th {
  text-align: right;
  color: var(--mu);
  font-weight: 400;
  padding: 8px 0;
  border-bottom: 1px solid var(--ln);
}

.history-table td {
  padding: 10px 0;
  border-bottom: 1px solid var(--ln);
}

@media (max-width: 900px) {
  .bdp {
    grid-template-columns: 1fr;
  }
  .tabc {
    grid-template-columns: 1fr;
  }
}
</style>
