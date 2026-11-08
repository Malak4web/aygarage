<template>
  <!-- Loading Skeleton -->
  <div v-if="loading" class="bdp-skeleton in">
    <div class="skeleton" style="height: 24px; width: 260px; margin-bottom: 24px;"></div>
    <div class="bdp">
      <div class="skeleton" style="width: 100%; height: 350px; border-radius: 12px;"></div>
      <div>
        <div class="skeleton" style="height: 20px; width: 140px; margin-bottom: 12px;"></div>
        <div class="skeleton" style="height: 36px; width: 75%; margin-bottom: 16px;"></div>
        <div class="skeleton" style="height: 16px; width: 50%; margin-bottom: 20px;"></div>
        <div class="skeleton" style="height: 48px; width: 100%; border-radius: 10px;"></div>
      </div>
    </div>
  </div>

  <!-- Error state with retry (Item 2) -->
  <div v-else-if="error" class="error-page in" style="padding: 40px 0;">
    <EmptyState
      icon="alert"
      title="تعذر الاتصال بالخادم"
      :description="error"
      action-label="إعادة المحاولة"
      @action="loadBikeData"
    />
  </div>

  <!-- Not found state (404 only) -->
  <div v-else-if="notFound || !bike" class="not-found-page in">
    <EmptyState
      icon="bike"
      title="الموتوسيكل غير موجود"
      description="لم نتمكن من العثور على هذا الموتوسيكل. ربما تم بيعه أو حذفه من القائمة."
      action-label="تصفح الموتوسيكلات المعروضة"
      action-to="/bikes"
    />
  </div>

  <!-- Bike detail page -->
  <div class="bike-detail-page" v-else>
    <Breadcrumbs :items="breadcrumbItems" />

    <div class="bdp in">
      <!-- Bike Gallery (Item 4) -->
      <div class="bike-gal">
        <div class="big">
          <img v-if="currentBikeImage" :src="currentBikeImage" :alt="bike.model" style="width: 100%; height: 100%; object-fit: cover;" />
          <BikeIllustration v-else :color="bike.col" :width="560" />
        </div>
        <div class="thr" v-if="bikeGalleryImages.length > 1">
          <button
            v-for="(_, i) in bikeGalleryImages"
            :key="i"
            type="button"
            class="thumb-btn"
            :class="{ on: activeThumb === i }"
            :aria-pressed="activeThumb === i"
            :aria-label="`صورة الموتوسيكل ${i + 1}`"
            @click="activeThumb = i"
          ></button>
        </div>
        <div class="thr" v-else-if="!bikeGalleryImages.length">
          <button
            v-for="(_, i) in 5"
            :key="i"
            type="button"
            class="thumb-btn"
            :class="{ on: activeThumb === i }"
            :aria-pressed="activeThumb === i"
            :aria-label="`صورة الموتوسيكل ${i + 1}`"
            @click="activeThumb = i"
          ></button>
        </div>
      </div>

      <!-- Bike Info & Booking -->
      <div class="bike-info">
        <span class="tg">{{ bike.inspectionScore }}/100 · فحص أبو يوسف</span>
        <h1 class="bike-title">{{ bike.model }} · {{ bike.year }}</h1>
        <div class="bike-meta-line">
          {{ formattedKm }} كم · {{ bike.owner || 'أول مالك' }} · رخصة سارية لحد {{ bike.licenseUntil }}
        </div>

        <div class="price">
          <b class="price-val">{{ formattedPrice }} <small>ج.م</small></b>
        </div>
        <div class="installment-line">
          أو تقسيط من {{ bike.installmentMonthly?.toLocaleString('en') || storeConfig.defaultInstallmentMonthly.toLocaleString('en') }} ج.م في الشهر
        </div>

        <div class="bike-actions">
          <RouterLink :to="`/booking?bike=${encodeURIComponent(getBikeModelCode(bike.model))}`" class="btn">
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
    <div class="in">
      <Tabs
        v-model="activeTab"
        :tabs="bikeTabs"
      />
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
          <b>{{ item.score }}%</b>
          <small>{{ item.note }}</small>
        </div>
        <div class="pdf-action">
          <button type="button" class="btn o sm" @click="handleDownloadReport">
            تحميل تقرير الفحص كاملاً (PDF)
          </button>
        </div>
      </div>

      <!-- Description Content -->
      <div v-else-if="activeTab === 'desc'" class="desc-tab">
        <p>{{ bike.notes }}</p>
      </div>

      <!-- Service History Content (Item 8) -->
      <div v-else-if="activeTab === 'history'" class="history-tab">
        <table class="history-table" v-if="bike.serviceHistory && bike.serviceHistory.length > 0">
          <thead>
            <tr>
              <th>التاريخ</th>
              <th>العداد</th>
              <th>الشغل</th>
              <th>الفني</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="h in bike.serviceHistory" :key="h.date">
              <td>{{ h.date }}</td>
              <td>{{ h.odo }}</td>
              <td>{{ h.service }}</td>
              <td>{{ h.tech }}</td>
            </tr>
          </tbody>
        </table>
        <div v-else class="empty-history" style="text-align: center; padding: 40px 16px; color: var(--mu);">
          <p>مفيش سجل صيانة مسجل للموتوسيكل ده</p>
        </div>
      </div>
    </div>

    <!-- Similar Bikes Row (Item 15) -->
    <ProductRow title="موتوسيكلات مشابهة" :columns="3">
      <BikeCard
        v-for="b in similarBikes"
        :key="b.id"
        :bike="b"
      />
    </ProductRow>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { api } from '@/api/index.js'
import { useToastStore } from '@/stores/toast.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import BikeIllustration from '@/components/common/BikeIllustration.vue'
import BikeCard from '@/components/common/BikeCard.vue'
import ProductRow from '@/components/common/ProductRow.vue'
import Tabs from '@/components/common/Tabs.vue'
import EmptyState from '@/components/common/EmptyState.vue'

const route = useRoute()
const toastStore = useToastStore()

const loading = ref(true)
const error = ref('')
const notFound = ref(false)
const bike = ref(null)
const similarBikes = ref([])
const activeThumb = ref(0)
const activeTab = ref('inspection')

const bikeTabs = [
  { id: 'inspection', label: 'تقرير الفحص' },
  { id: 'desc', label: 'الوصف' },
  { id: 'history', label: 'سجل الصيانة' }
]

const bikeGalleryImages = computed(() => {
  if (bike.value?.images && bike.value.images.length > 0) {
    return bike.value.images
  }
  if (bike.value?.image) {
    return [bike.value.image]
  }
  return []
})

const currentBikeImage = computed(() => {
  if (bikeGalleryImages.value.length > 0) {
    return bikeGalleryImages.value[activeThumb.value] || bikeGalleryImages.value[0]
  }
  return bike.value?.image || null
})

let bikeRequestId = 0

const loadBikeData = async () => {
  const currentReq = ++bikeRequestId
  loading.value = true
  error.value = ''
  notFound.value = false
  try {
    const slug = route.params.slug
    const b = await api.getBikeBySlug(slug)
    if (currentReq !== bikeRequestId) return
    bike.value = b
    similarBikes.value = b.similar || []
  } catch (e) {
    if (currentReq !== bikeRequestId) return
    bike.value = null
    if (e?.status === 404) {
      notFound.value = true
    } else {
      error.value = 'تعذر التحميل، اتأكد من النت وحاول تاني'
    }
  } finally {
    if (currentReq === bikeRequestId) {
      loading.value = false
    }
  }
}

watch(
  () => route.params.slug,
  () => {
    loadBikeData()
  },
  { immediate: true }
)

const getBikeModelCode = (modelName) => {
  if (!modelName) return 'ZT350-T'
  const matched = storeConfig.models.find((m) => modelName.includes(m))
  return matched || modelName
}

const formattedPrice = computed(() => {
  if (!bike.value) return ''
  return typeof bike.value.price === 'number'
    ? bike.value.price.toLocaleString('en')
    : bike.value.price
})

const formattedKm = computed(() => {
  if (!bike.value) return ''
  return typeof bike.value.km === 'number'
    ? bike.value.km.toLocaleString('en')
    : bike.value.km
})

const breadcrumbItems = computed(() => [
  { label: 'الرئيسية', to: '/' },
  { label: 'موتوسيكلات للبيع', to: '/bikes' },
  { label: `${bike.value?.model || ''} ${bike.value?.year || ''}` }
])

const handleDownloadReport = () => {
  toastStore.show(`جاري تحميل تقرير الفحص المعتمد لموتوسيكل ${bike.value?.model || ''} (120 نقطة) بصيغة PDF.`, 'info')
}
</script>

<style scoped>
.bdp-skeleton {
  padding-top: 30px;
}

.bdp {
  display: grid;
  grid-template-columns: 1.25fr 1fr;
  gap: 40px;
  padding-top: 18px;
}

.bike-gal .big {
  aspect-ratio: 16/10;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  display: grid;
  place-items: center;
  background: #ffffff;
  overflow: hidden;
}

.thr {
  display: flex;
  gap: 10px;
  margin-top: 14px;
}

.thumb-btn {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: var(--ln);
  border: none;
  cursor: pointer;
  padding: 0;
  transition: all 0.2s ease;
}

.thumb-btn.on {
  background: var(--ink);
  transform: scale(1.15);
}

.thumb-btn:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: 2px;
}

.bike-info {
  display: flex;
  flex-direction: column;
}

.tg {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 999px;
  background: var(--oksoft);
  color: var(--ok);
  font-size: 13px;
  font-weight: 700;
  align-self: flex-start;
  margin-bottom: 8px;
}

.bike-title {
  font-size: 32px;
  font-weight: 800;
  margin: 0 0 8px;
  line-height: 1.2;
}

.bike-meta-line {
  color: var(--mu);
  font-size: 14px;
  margin-bottom: 18px;
}

.price {
  margin-bottom: 6px;
}

.price-val {
  font-size: 28px;
  font-weight: 800;
}

.price-val small {
  font-size: 14px;
  font-weight: 400;
}

.installment-line {
  font-size: 14px;
  color: var(--mu);
  margin-bottom: 24px;
}

.bike-actions {
  display: flex;
  gap: 12px;
  margin-bottom: 28px;
}

.spec {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
  border-top: 1px solid var(--ln);
}

.spec td {
  padding: 10px 14px;
  border-bottom: 1px solid var(--ln);
}

.spec td:first-child {
  color: var(--mu);
  width: 35%;
  font-weight: 700;
}

.tabc {
  padding-bottom: 40px;
}

.rep {
  display: flex;
  flex-direction: column;
  gap: 14px;
  max-width: 720px;
}

.rep .r {
  display: grid;
  grid-template-columns: 140px 1fr 50px 180px;
  align-items: center;
  gap: 16px;
  font-size: 14px;
}

.pb {
  height: 8px;
  background: var(--sf);
  border-radius: 4px;
  overflow: hidden;
}

.pb i {
  display: block;
  height: 100%;
  background: var(--ok);
  border-radius: 4px;
}

.pb i.w {
  background: var(--star);
}

.pdf-action {
  margin-top: 20px;
}

.desc-tab p {
  line-height: 1.8;
  font-size: 15px;
  max-width: 680px;
}

.history-table {
  width: 100%;
  max-width: 720px;
  border-collapse: collapse;
  font-size: 14px;
}

.history-table th,
.history-table td {
  padding: 10px 14px;
  text-align: start;
  border-bottom: 1px solid var(--ln);
}

.history-table th {
  font-weight: 700;
  color: var(--mu);
}

@media (max-width: 900px) {
  .bdp {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  .rep .r {
    grid-template-columns: 100px 1fr 40px;
  }
  .rep .r small {
    grid-column: 1 / -1;
    color: var(--mu);
    margin-top: -6px;
  }
}
</style>
