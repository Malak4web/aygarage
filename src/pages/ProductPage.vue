<template>
  <!-- Loading skeleton state -->
  <div v-if="loading" class="pdp-skeleton in">
    <div class="skeleton" style="height: 24px; width: 220px; margin-bottom: 24px;"></div>
    <div class="pdp">
      <div class="gal">
        <div class="skeleton" style="width: 100%; aspect-ratio: 1; border-radius: 12px;"></div>
      </div>
      <div class="buy-box">
        <div class="skeleton" style="height: 14px; width: 140px; margin-bottom: 12px;"></div>
        <div class="skeleton" style="height: 32px; width: 80%; margin-bottom: 16px;"></div>
        <div class="skeleton" style="height: 20px; width: 180px; margin-bottom: 24px;"></div>
        <div class="skeleton" style="height: 48px; width: 100%; border-radius: 10px; margin-bottom: 16px;"></div>
        <div class="skeleton" style="height: 46px; width: 100%; border-radius: 10px;"></div>
      </div>
    </div>
  </div>

  <!-- Not found state -->
  <div v-else-if="!product" class="not-found-page in">
    <EmptyState
      icon="box"
      title="المنتج غير موجود"
      description="لم نتمكن من العثور على هذا المنتج. ربما تم نقله أو حذفه من المتجر."
      action-label="تصفح المتجر"
      action-to="/c/oils"
    />
  </div>

  <!-- Product page content -->
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
            :aria-label="`صورة المنتج ${idx + 1}`"
          >
            <ProductIllustration
              :type="product.type"
              :color="idx === 1 ? '#7d1a14' : product.color"
              :image="product.image"
              :width="50"
              :alt="product.name"
            />
          </button>
        </div>
        <div class="big">
          <ProductIllustration
            :type="product.type"
            :color="activeThumb === 1 ? '#7d1a14' : product.color"
            :image="product.image"
            :width="360"
            :alt="product.name"
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
            <b v-else>مش متأكدين إنه يركب على موتوسيكلك، اسألنا</b>
            <small v-if="fitResult === true && product.fitNote">{{ product.fitNote }}</small>
            <small v-else>اسأل الدعم أو راجع كتالوج الصيانة</small>
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

        <!-- Stock Status (Item 12) -->
        <div class="stock-status" :class="{ 'out-of-stock': product.stock === 0 }">
          <span v-if="product.stock > 0">
            متوفر · {{ product.stock }} قطعة في المخزن
          </span>
          <span v-else class="out-badge">
            غير متوفر حالياً
          </span>
        </div>

        <!-- Quantity & Buy Buttons -->
        <div class="buy">
          <QtyStepper
            v-model="quantity"
            :min="product.stock > 0 ? 1 : 0"
            :max="product.stock"
            :disabled="product.stock === 0"
          />
          <button
            type="button"
            class="btn add-cart-btn"
            :disabled="product.stock === 0"
            @click="handleAddToCart"
          >
            <AppIcon name="cart" :size="18" />
            <span>{{ product.stock === 0 ? 'غير متوفر' : 'أضف للسلة' }}</span>
          </button>
          <button
            type="button"
            class="btn o buy-now-btn"
            :disabled="product.stock === 0"
            @click="handleBuyNow"
          >
            اشتري دلوقتي
          </button>
        </div>

        <!-- Perks List -->
        <div class="perks">
          <div class="perk-item">
            <AppIcon name="truck" :size="22" :stroke-width="1.5" />
            <div>
              <span>{{ storeConfig.deliveryEstimate }}</span>
              <small>· توصيل {{ storeConfig.deliveryFee }} ج.م، ومجاني فوق {{ storeConfig.freeDeliveryThreshold.toLocaleString('en') }}</small>
            </div>
          </div>
          <div class="perk-item">
            <AppIcon name="pin" :size="22" :stroke-width="1.5" />
            <div>
              <span>استلام من الجراج</span>
              <small>· {{ storeConfig.pickupTimes }}</small>
            </div>
          </div>
          <div class="perk-item">
            <AppIcon name="ret" :size="22" :stroke-width="1.5" />
            <div>
              <span>استرجاع خلال {{ storeConfig.returnDays }} يوم</span>
              <small>· {{ storeConfig.returnPolicyNote }}</small>
            </div>
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
        :disabled="product.stock === 0"
        @click="handleAddToCart"
      >
        <AppIcon name="cart" :size="16" />
        <span>{{ product.stock === 0 ? 'غير متوفر' : 'أضف للسلة' }}</span>
      </button>
    </div>

    <!-- Bike Picker Modal Dialog -->
    <div v-if="showBikePicker" class="modal-backdrop" @click="showBikePicker = false">
      <div class="modal-card" ref="bikePickerModalRef" @click.stop role="dialog" aria-modal="true" aria-label="تغيير الموتوسيكل">
        <h3>اختر موديل موتوسيكلك</h3>
        <p>هنحدث حالة التوافق لكل قطع الغيار بناءً على اختيارك</p>
        <div class="models-grid">
          <button
            v-for="model in storeConfig.models"
            :key="model"
            type="button"
            class="model-chip"
            :class="{ active: userStore.selectedBike === model }"
            @click="selectBike(model)"
          >
            {{ model }}
          </button>
        </div>
        <button type="button" class="btn o sm close-modal-btn" @click="showBikePicker = false">
          إغلاق
        </button>
      </div>
    </div>

    <!-- Tabs: Description, Specs, Reviews, Compatibility -->
    <div class="in">
      <Tabs
        v-model="activeTab"
        :tabs="productTabs"
      />
    </div>

    <!-- Tab Content -->
    <div class="tabc in" id="reviews">
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
            <div v-for="rev in reviewsList" :key="rev.id" class="review-item">
              <div class="review-author">
                <b>{{ rev.author }}</b>
                <RatingStars :rating="rev.rating" />
              </div>
              <p>{{ rev.content }}</p>
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
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { api } from '@/api/index.js'
import { useUserStore } from '@/stores/user.js'
import { useCartStore } from '@/stores/cart.js'
import { useToastStore } from '@/stores/toast.js'
import { doesFit } from '@/utils/fit.js'
import { useModalTrap } from '@/utils/modal.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import ProductIllustration from '@/components/common/ProductIllustration.vue'
import RatingStars from '@/components/common/RatingStars.vue'
import PriceTag from '@/components/common/PriceTag.vue'
import QtyStepper from '@/components/common/QtyStepper.vue'
import ProductRow from '@/components/common/ProductRow.vue'
import Tabs from '@/components/common/Tabs.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const cartStore = useCartStore()
const toastStore = useToastStore()

const loading = ref(true)
const product = ref(null)
const companionProducts = ref([])
const categoriesList = ref([])
const reviewsList = ref([])

const activeThumb = ref(0)
const activeTab = ref('desc')
const quantity = ref(1)
const showBikePicker = ref(false)
const selectedOption = ref(null)
const bikePickerModalRef = ref(null)

useModalTrap(showBikePicker, bikePickerModalRef, () => {
  showBikePicker.value = false
})

const productTabs = computed(() => [
  { id: 'desc', label: 'الوصف' },
  { id: 'specs', label: 'المواصفات' },
  { id: 'reviews', label: `التقييمات (${product.value?.reviewCount || 0})` },
  { id: 'fit', label: 'الموديلات المتوافقة' }
])

const loadProductData = async () => {
  loading.value = true
  try {
    const slug = route.params.slug
    const [p, all, cats, revs] = await Promise.all([
      api.getProductBySlug(slug),
      api.getProducts(),
      api.getCategories(),
      api.getReviews()
    ])
    product.value = p
    selectedOption.value = p.options?.[0] ?? null
    activeTab.value = 'desc'
    activeThumb.value = 0
    quantity.value = 1
    companionProducts.value = all.filter((prod) => prod.id !== p.id).slice(0, 5)
    categoriesList.value = cats
    reviewsList.value = revs
  } catch (e) {
    product.value = null
  } finally {
    loading.value = false
  }
}

watch(
  () => route.params.slug,
  () => {
    loadProductData()
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
  return userStore.userBikeYear
})

const breadcrumbItems = computed(() => {
  if (!product.value) return [{ label: 'الرئيسية', to: '/' }, { label: 'المنتج' }]
  const cat = categoriesList.value.find((c) => c.slug === product.value.category)
  const catName = cat ? cat.name : 'المنتجات'
  const catSlug = cat ? cat.slug : 'oils'
  return [
    { label: 'الرئيسية', to: '/' },
    { label: catName, to: `/c/${catSlug}` },
    { label: product.value.name }
  ]
})

const selectBike = (model) => {
  userStore.setSelectedBike(model)
  showBikePicker.value = false
}

const handleAddToCart = () => {
  if (!product.value || product.value.stock === 0) return
  const status = cartStore.addItem(product.value, quantity.value, selectedOption.value)
  if (status === 'added') {
    toastStore.show(`تمت إضافة ${product.value.name} إلى السلة بنجاح`, 'success')
  }
}

const handleBuyNow = () => {
  if (!product.value || product.value.stock === 0) return
  const status = cartStore.addItem(product.value, quantity.value, selectedOption.value)
  if (status === 'added' || status === 'clamped') {
    router.push('/checkout')
  }
}
</script>

<style scoped>
.product-page {
  padding-bottom: 30px;
}

@media (max-width: 768px) {
  .product-page {
    padding-bottom: 80px;
  }
}

.pdp-skeleton {
  padding-top: 30px;
}

.pdp {
  display: grid;
  grid-template-columns: 1.15fr 1fr;
  gap: 40px;
  padding-top: 18px;
}

.gal {
  display: flex;
  gap: 14px;
}

.gal .th {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.thumb-btn {
  width: 64px;
  height: 64px;
  border: 1.5px solid var(--ln);
  border-radius: var(--radius-btn);
  background: var(--sf);
  display: grid;
  place-items: center;
  cursor: pointer;
  padding: 0;
  transition: border-color 0.15s ease;
}

.thumb-btn.on {
  border-color: var(--ink);
}

.gal .big {
  flex: 1;
  aspect-ratio: 1;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  display: grid;
  place-items: center;
  background: #ffffff;
}

.buy-box {
  display: flex;
  flex-direction: column;
}

.meta-line {
  font-size: 14px;
  color: var(--mu);
  margin-bottom: 4px;
}

.buy-box h1 {
  font-size: 28px;
  font-weight: 800;
  line-height: 1.3;
  margin: 0 0 10px;
}

.rate-line {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.reviews-count {
  font-size: 14px;
  color: var(--ink);
  text-decoration: underline;
  text-underline-offset: 3px;
}

.price {
  margin-bottom: 18px;
}

.fitbox {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: var(--radius-btn);
  background: var(--sf);
  margin-bottom: 20px;
  border: 1px solid var(--ln);
}

.fitbox.ok {
  background: var(--oksoft);
  color: var(--ok);
  border-color: rgba(27, 127, 75, 0.2);
}

.fitbox div {
  flex: 1;
}

.fitbox b {
  display: block;
  font-size: 14.5px;
  color: var(--ink);
}

.fitbox.ok b {
  color: var(--ok);
}

.fitbox small {
  display: block;
  font-size: 12.5px;
  color: var(--mu);
}

.change-bike-btn {
  background: none;
  border: none;
  font-size: 13px;
  font-weight: 700;
  color: var(--ink);
  text-decoration: underline;
  cursor: pointer;
  padding: 0;
}

.opt {
  margin-bottom: 20px;
}

.opt h5 {
  font-size: 14px;
  font-weight: 700;
  margin: 0 0 8px;
}

.os {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.option-pill {
  padding: 8px 16px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  background: #ffffff;
  font-size: 14px;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s ease;
}

.option-pill.on {
  border-color: var(--ink);
  background: var(--ink);
  color: #ffffff;
  font-weight: 700;
}

.stock-status {
  font-size: 14px;
  color: var(--ok);
  margin-bottom: 18px;
  font-weight: 500;
}

.stock-status.out-of-stock {
  color: var(--sale);
}

.out-badge {
  color: var(--sale);
  font-weight: 700;
}

.buy {
  display: flex;
  gap: 10px;
  margin-bottom: 24px;
}

.add-cart-btn {
  flex: 2;
}

.buy-now-btn {
  flex: 1;
}

.perks {
  border-top: 1px solid var(--ln);
  padding-top: 18px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.perk-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 13.5px;
}

.perk-item span {
  font-weight: 700;
}

.perk-item small {
  color: var(--mu);
}

.tabc {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 36px;
  padding-bottom: 40px;
}

.desc-text {
  font-size: 16px;
  line-height: 1.8;
  margin: 0;
  color: var(--ink);
}

.spec {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
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

.review-item {
  border-bottom: 1px solid var(--ln);
  padding: 14px 0;
}

.review-item:last-child {
  border-bottom: none;
}

.review-author {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 6px;
}

.compat-list {
  list-style: square;
  padding-inline-start: 20px;
  line-height: 1.8;
}

/* Sticky buy bar for phones */
.mobile-sticky-buy {
  display: none;
}

@media (max-width: 768px) {
  .pdp {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  .gal {
    flex-direction: column;
  }
  .gal .th {
    flex-direction: row;
    order: 2;
  }
  .tabc {
    grid-template-columns: 1fr;
  }
  .mobile-sticky-buy {
    display: flex;
    position: fixed;
    bottom: 0;
    inset-inline: 0;
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

/* Modal */
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
  margin: 0 0 6px;
  font-size: 20px;
  font-weight: 800;
}

.modal-card p {
  color: var(--mu);
  font-size: 13.5px;
  margin: 0 0 18px;
}

.models-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-bottom: 20px;
}

.model-chip {
  padding: 10px 14px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  background: #ffffff;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  text-align: center;
}

.model-chip.active {
  background: var(--ink);
  color: #ffffff;
  border-color: var(--ink);
}

.close-modal-btn {
  width: 100%;
}
</style>
