<template>
  <div v-if="!product" class="not-found-page in">
    <EmptyState
      icon="box"
      title="المنتج غير موجود"
      description="لم نتمكن من العثور على هذا المنتج. ربما تم نقله أو حذفه من المتجر."
      action-label="تصفح المتجر"
      action-to="/c/oils"
    />
  </div>
  <div class="product-page" v-else>
    <Breadcrumbs :items="breadcrumbItems" />

    <div class="pdp in">
      <!-- Gallery: Thumbs + Big image -->
      <div class="gal">
        <div class="th">
          <button
            v-for="(_, idx) in 4"
            :key="idx"
            type="button"
            class="thumb-btn"
            :class="{ on: activeThumb === idx }"
            @click="activeThumb = idx"
          >
            <ProductIllustration
              :type="product.type"
              :color="idx === 1 ? '#7d1a14' : product.color"
              :image="product.image"
              :width="50"
            />
          </button>
        </div>
        <div class="big">
          <ProductIllustration
            :type="product.type"
            :color="activeThumb === 1 ? '#7d1a14' : product.color"
            :image="product.image"
            :width="360"
          />
        </div>
      </div>

      <!-- Buy Box -->
      <div class="buy-box">
        <div class="meta-line">
          {{ product.brand }} · كود {{ product.sku }}
        </div>
        <h1>{{ product.name }}</h1>

        <div class="rate-line">
          <RatingStars :rating="product.rating" />
          <a href="#reviews" class="reviews-count" @click.prevent="activeTab = 'reviews'">
            {{ product.reviewCount }} تقييم
          </a>
        </div>

        <div class="price">
          <PriceTag
            :price="currentPrice"
            :old-price="currentOldPrice"
            size="large"
            :show-savings="true"
          />
        </div>

        <!-- Fit Box -->
        <div class="fitbox" :class="{ ok: fitResult === true }">
          <AppIcon v-if="fitResult === true" name="check" :size="22" :stroke-width="2.2" />
          <AppIcon v-else name="shield" :size="22" :stroke-width="1.5" />
          <div>
            <b v-if="fitResult === true">يركب على موتوسيكلك {{ userStore.selectedBike }} · {{ userBikeYear }}</b>
            <b v-else-if="fitResult === null">مقاسات متعددة</b>
            <b v-else>مش متأكدين إنه يركب على موتوسيكلك، اسألنا</b>
            <small v-if="fitResult === true && product.fitNote">{{ product.fitNote }}</small>
            <small v-else-if="fitResult === false">اسأل الدعم أو راجع كتالوج الصيانة</small>
          </div>
          <button type="button" class="change-bike-btn" @click="showBikePicker = true">
            غيّر
          </button>
        </div>

        <!-- Options (Size / Kit) -->
        <div v-if="product.options && product.options.length" class="opt">
          <h5>الحجم</h5>
          <div class="os">
            <button
              v-for="opt in product.options"
              :key="opt.id"
              type="button"
              class="option-pill"
              :class="{ on: selectedOption?.id === opt.id }"
              @click="selectedOption = opt"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>

        <!-- Buy Actions -->
        <div class="buy">
          <QtyStepper
            v-model="quantity"
            :max="product.stock"
            :min="product.stock > 0 ? 1 : 0"
          />
          <button
            type="button"
            class="btn"
            @click="handleAddToCart"
            :disabled="product.stock === 0"
          >
            <AppIcon name="cart" :size="18" />
            <span>{{ product.stock === 0 ? 'غير متوفر' : 'أضف للسلة' }}</span>
          </button>
          <button
            type="button"
            class="btn o"
            @click="handleBuyNow"
            :disabled="product.stock === 0"
          >
            اشتري دلوقتي
          </button>
        </div>

        <!-- Stock Notice -->
        <div class="stock-line" :class="{ out: product.stock === 0 }">
          {{ product.stock > 0 ? `متوفر · ${product.stock} قطعة في المخزن` : 'غير متوفر' }}
        </div>

        <!-- Delivery & Service Perks -->
        <div class="perks">
          <div>
            <AppIcon name="truck" :size="22" :stroke-width="1.5" />
            <span>
              {{ storeConfig.deliveryEstimate }}
              <small>· توصيل {{ storeConfig.deliveryFee }} ج.م، ومجاني فوق {{ storeConfig.freeDeliveryThreshold.toLocaleString('en') }}</small>
            </span>
          </div>
          <div>
            <AppIcon name="pin" :size="22" :stroke-width="1.5" />
            <span>
              استلام من الجراج
              <small>· {{ storeConfig.pickupTimes }}</small>
            </span>
          </div>
          <div>
            <AppIcon name="ret" :size="22" :stroke-width="1.5" />
            <span>
              استرجاع خلال {{ storeConfig.returnDays }} يوم
              <small>· لو العبوة ما اتفتحتش</small>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Sticky Mobile Buy Bar -->
    <div class="mobile-sticky-buy">
      <div class="mobile-sticky-info">
        <span class="mobile-sticky-price">{{ (currentPrice * quantity).toLocaleString('en') }} ج.م</span>
        <small>{{ quantity }} قطع</small>
      </div>
      <button
        type="button"
        class="btn sm"
        @click="handleAddToCart"
        :disabled="product.stock === 0"
      >
        <AppIcon name="cart" :size="16" />
        <span>{{ product.stock === 0 ? 'غير متوفر' : 'أضف للسلة' }}</span>
      </button>
      <button
        type="button"
        class="btn o sm"
        @click="handleBuyNow"
        :disabled="product.stock === 0"
      >
        شراء
      </button>
    </div>

    <!-- Tabs: Description, Specs, Reviews, Compatibility -->
    <div class="tabs in">
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
        :class="{ on: activeTab === 'specs' }"
        @click="activeTab = 'specs'"
      >
        المواصفات
      </button>
      <button
        type="button"
        class="tab-btn"
        :class="{ on: activeTab === 'reviews' }"
        @click="activeTab = 'reviews'"
      >
        التقييمات ({{ product.reviewCount }})
      </button>
      <button
        type="button"
        class="tab-btn"
        :class="{ on: activeTab === 'fit' }"
        @click="activeTab = 'fit'"
      >
        الموديلات المتوافقة
      </button>
    </div>

    <!-- Tab Content -->
    <div class="tabc in">
      <div class="tab-main">
        <div v-if="activeTab === 'desc'">
          <p class="desc-text">{{ product.description }}</p>
        </div>

        <div v-else-if="activeTab === 'specs'">
          <table class="spec">
            <tbody>
              <tr v-for="(val, key) in product.specs" :key="key">
                <td>{{ key }}</td>
                <td>{{ val }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else-if="activeTab === 'reviews'">
          <div class="reviews-list">
            <div class="review-item">
              <div class="review-author">
                <b>أحمد إبراهيم</b>
                <RatingStars :rating="5" />
              </div>
              <p>زيت ممتاز جداً صوته ناعم مع محرك الـ 350 ونقلات الجير بقت سلسة جداً بدون أي تكة.</p>
            </div>
            <div class="review-item">
              <div class="review-author">
                <b>محمد فتحي</b>
                <RatingStars :rating="5" />
              </div>
              <p>التوصيل كان سريع جداً واستلمت القطع أصلية بالباركود من جراج أبو يوسف.</p>
            </div>
          </div>
        </div>

        <div v-else-if="activeTab === 'fit'">
          <p>هذا المنتج متوافق مع الموديلات التالية:</p>
          <ul class="compat-list">
            <li v-for="m in (product.fits?.length ? product.fits : storeConfig.models)" :key="m">
              {{ m }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Specs Sidebar Table -->
      <div class="tab-side" v-if="activeTab === 'desc'">
        <table class="spec">
          <tbody>
            <tr v-for="(val, key) in product.specs" :key="key">
              <td>{{ key }}</td>
              <td>{{ val }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Frequently Bought Together Row -->
    <ProductRow title="بيتشترى معاه" :products="companionProducts" />

    <!-- Bike Selector Modal -->
    <div v-if="showBikePicker" class="modal-backdrop" @click="showBikePicker = false">
      <div class="modal-card" @click.stop>
        <h3>اختر موديل الموتوسيكل</h3>
        <p>حدد موتوسيكلك لتأكيد توافق قطع الغيار</p>
        <div class="modal-bikes-grid">
          <button
            v-for="m in storeConfig.models"
            :key="m"
            type="button"
            class="modal-bike-btn"
            :class="{ active: userStore.selectedBike === m }"
            @click="selectBike(m)"
          >
            {{ m }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { products } from '@/data/products.js'
import { useUserStore } from '@/stores/user.js'
import { useCartStore } from '@/stores/cart.js'
import { doesFit } from '@/utils/fit.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import ProductIllustration from '@/components/common/ProductIllustration.vue'
import RatingStars from '@/components/common/RatingStars.vue'
import PriceTag from '@/components/common/PriceTag.vue'
import QtyStepper from '@/components/common/QtyStepper.vue'
import ProductCard from '@/components/common/ProductCard.vue'
import ProductRow from '@/components/common/ProductRow.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const cartStore = useCartStore()

const product = computed(() => {
  return products.find((p) => p.slug === route.params.slug) || null
})

const activeThumb = ref(0)
const activeTab = ref('desc')
const quantity = ref(1)
const showBikePicker = ref(false)
const selectedOption = ref(null)

watch(
  () => route.params.slug,
  () => {
    activeTab.value = 'desc'
    activeThumb.value = 0
    quantity.value = 1
    selectedOption.value = product.value?.options?.[0] ?? null
  },
  { immediate: true }
)

const currentPrice = computed(() => {
  return selectedOption.value?.price || product.value?.price || 0
})

const currentOldPrice = computed(() => {
  return selectedOption.value?.oldPrice || (!selectedOption.value ? product.value?.oldPrice : 0) || 0
})

const fitResult = computed(() => {
  return doesFit(product.value, userStore.selectedBike)
})

const userBikeYear = computed(() => {
  return userStore.user?.savedBikes?.[0]?.year || 2023
})

const breadcrumbItems = computed(() => {
  if (!product.value) return [{ label: 'الرئيسية', to: '/' }, { label: 'المنتج' }]
  return [
    { label: 'الرئيسية', to: '/' },
    { label: 'زيوت', to: '/c/oils' },
    { label: 'زيوت المحرك', to: '/c/oils' },
    { label: product.value.name }
  ]
})

const companionProducts = computed(() => {
  if (!product.value) return []
  return products.filter((p) => p.id !== product.value.id).slice(0, 5)
})

const selectBike = (model) => {
  userStore.setSelectedBike(model)
  showBikePicker.value = false
}

const handleAddToCart = () => {
  if (!product.value || product.value.stock === 0) return
  cartStore.addItem(product.value, quantity.value, selectedOption.value)
}

const handleBuyNow = () => {
  if (!product.value || product.value.stock === 0) return
  cartStore.addItem(product.value, quantity.value, selectedOption.value)
  router.push('/checkout')
}
</script>

<style scoped>
.pdp {
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  gap: 44px;
  padding-top: 18px;
}

.gal {
  display: grid;
  grid-template-columns: 84px 1fr;
  gap: 12px;
}

.th {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.thumb-btn {
  aspect-ratio: 1;
  border-radius: 10px;
  background: var(--sf);
  display: grid;
  place-items: center;
  border: none;
  cursor: pointer;
  padding: 6px;
  transition: box-shadow 0.15s ease;
}

.thumb-btn.on {
  box-shadow: 0 0 0 2px var(--ink);
}

.big {
  aspect-ratio: 1;
  border-radius: var(--radius-box);
  background: var(--sf);
  display: grid;
  place-items: center;
}

.meta-line {
  font-size: 14px;
  color: var(--mu);
}

.buy-box h1 {
  font-size: 28px;
  font-weight: 800;
  margin: 4px 0 8px;
  line-height: 1.4;
}

.rate-line {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
}

.reviews-count {
  text-decoration: underline;
  color: var(--ink);
}

.price {
  margin: 12px 0;
}

.fitbox {
  border: 1px solid var(--ln);
  border-radius: 12px;
  padding: 14px 16px;
  display: flex;
  gap: 12px;
  align-items: center;
  margin: 16px 0;
  background: var(--sf);
}

.fitbox.ok {
  background: var(--oksoft);
  border-color: #cfe6d9;
}

.fitbox b {
  display: block;
  font-size: 14.5px;
}

.fitbox small {
  color: var(--mu);
  font-size: 13px;
}

.change-bike-btn {
  margin-inline-start: auto;
  font-size: 13px;
  color: var(--ink);
  text-decoration: underline;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
}

.opt h5 {
  margin: 0 0 8px;
  font-size: 14px;
}

.opt .os {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.option-pill {
  height: 40px;
  padding: 0 16px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  display: grid;
  place-items: center;
  font-size: 14px;
  background: #ffffff;
  cursor: pointer;
}

.option-pill.on {
  border: 2px solid var(--ink);
  font-weight: 700;
}

.buy {
  display: grid;
  grid-template-columns: 130px 1fr 1fr;
  gap: 10px;
}

.stock-line {
  font-size: 14px;
  color: var(--ok);
  margin-top: 10px;
  font-weight: 700;
}

.stock-line.out {
  color: var(--sale);
}

.perks {
  border-top: 1px solid var(--ln);
  margin-top: 20px;
  padding-top: 16px;
  display: grid;
  gap: 10px;
  font-size: 14px;
}

.perks div {
  display: flex;
  gap: 10px;
  align-items: center;
}

.perks small {
  color: var(--mu);
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
  position: relative;
  font-weight: 500;
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

.desc-text {
  margin-top: 0;
  line-height: 1.9;
  font-size: 15px;
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

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.review-item {
  border-bottom: 1px solid var(--ln);
  padding-bottom: 14px;
}

.review-author {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 6px;
}

.compat-list {
  list-style: none;
  padding: 0;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.compat-list li {
  padding: 6px 14px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-pill);
  font-size: 13px;
  background: var(--sf);
}

/* Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: grid;
  place-items: center;
  z-index: 1000;
  padding: 16px;
}

.modal-card {
  background: #ffffff;
  border-radius: var(--radius-box);
  padding: 24px;
  width: 100%;
  max-width: 420px;
}

.modal-bikes-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  margin-top: 16px;
}

.modal-bike-btn {
  padding: 10px;
  border: 1px solid var(--ln);
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  background: #ffffff;
}

.modal-bike-btn.active {
  background: var(--ink);
  color: #ffffff;
}

.mobile-sticky-buy {
  display: none;
}

/* Responsive */
@media (max-width: 900px) {
  .pdp {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  .tabc {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 600px) {
  .gal {
    grid-template-columns: 1fr;
  }
  .gal .th {
    flex-direction: row;
    order: 2;
  }
  .buy {
    grid-template-columns: 1fr;
  }
  .mobile-sticky-buy {
    display: flex;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #ffffff;
    border-top: 1px solid var(--ln);
    padding: 10px 16px;
    z-index: 990;
    align-items: center;
    gap: 10px;
    box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
  }
  .mobile-sticky-info {
    display: flex;
    flex-direction: column;
    margin-inline-end: auto;
  }
  .mobile-sticky-price {
    font-size: 16px;
    font-weight: 800;
  }
}
</style>
