<template>
  <div class="home-page">
    <!-- Hero Section: Main Banner Carousel + 2 Side Banners -->
    <section class="hero-grid in">
      <HeroCarousel :slides="heroSlides" v-slot="{ currentSlide }">
        <div class="ban main">
          <div class="art">
            <BikeIllustration :color="currentSlide.bikeColor" :width="460" stroke="#4a4a4a" />
          </div>
          <div class="tx">
            <h2>{{ currentSlide.title }}</h2>
            <p>{{ currentSlide.desc }}</p>
            <div class="hero-actions">
              <button type="button" class="btn w" @click="handleHeroAction(currentSlide)">
                {{ currentSlide.btnPrimary }}
              </button>
              <RouterLink to="/booking" class="btn hero-outline-btn">
                احجز التركيب
              </RouterLink>
            </div>
          </div>
        </div>
      </HeroCarousel>

      <!-- Side Banners -->
      <div class="side2">
        <div class="ban s">
          <div class="tx">
            <h3>خوذ LS2 الجديدة</h3>
            <p>موديلات 2026 وصلت</p>
            <RouterLink to="/c/helmets">تسوّق الخوذ</RouterLink>
          </div>
          <div class="art">
            <ProductIllustration type="helmet" :width="120" />
          </div>
        </div>

        <div class="ban s">
          <div class="tx">
            <h3>موتوسيكلات مفحوصة</h3>
            <p>{{ storeConfig.totalInspectedBikes }} موتوسيكل بتقرير فحص</p>
            <RouterLink to="/bikes">شوف المعروض</RouterLink>
          </div>
          <div class="art">
            <BikeIllustration color="#56634a" :width="150" />
          </div>
        </div>
      </div>
    </section>

    <!-- Finder Widget -->
    <section class="in">
      <div class="finder">
        <div class="finder-title">
          <b>دوّر على قطع موتوسيكلك</b>
          <small>هنعرضلك اللي يركب بس</small>
        </div>

        <div class="finder-field">
          <select v-model="selectedModel" class="finder-select" aria-label="موديل الموتوسيكل">
            <option v-for="model in storeConfig.models" :key="model" :value="model">
              {{ model }}
            </option>
          </select>
          <AppIcon name="down" :size="14" class="field-icon" />
        </div>

        <div class="finder-field">
          <select v-model="selectedYear" class="finder-select" aria-label="سنة الموديل">
            <option v-for="yr in storeConfig.years" :key="yr" :value="yr">
              {{ yr }}
            </option>
          </select>
          <AppIcon name="down" :size="14" class="field-icon" />
        </div>

        <div class="finder-field">
          <select v-model="selectedCategory" class="finder-select" aria-label="القسم">
            <option value="">كل الأقسام</option>
            <option v-for="cat in categories" :key="cat.slug" :value="cat.slug">
              {{ cat.name }}
            </option>
          </select>
          <AppIcon name="down" :size="14" class="field-icon" />
        </div>

        <button type="button" class="btn" @click="handleFinderSubmit">
          اعرض القطع
        </button>
      </div>
    </section>

    <!-- Shop by Category circles -->
    <section class="sec in">
      <div class="sh">
        <h2>تسوّق حسب القسم</h2>
      </div>
      <div class="cats">
        <RouterLink
          v-for="cat in categories"
          :key="cat.slug"
          :to="`/c/${cat.slug}`"
          class="cat-circle"
        >
          <div class="im">
            <ProductIllustration :type="cat.icon" :width="80" />
          </div>
          <span class="cat-name">{{ cat.name }}</span>
        </RouterLink>
      </div>
    </section>

    <!-- Error State with Retry (Item 18) -->
    <div v-if="error" class="in" style="padding: 24px 0">
      <EmptyState
        title="تعذر تحميل بيانات الصفحة"
        :description="error"
        action-label="إعادة المحاولة"
        @action="loadHomeData"
      />
    </div>

    <!-- Best Sellers Row -->
    <section class="sec in">
      <div class="sh">
        <h2>الأكثر مبيعاً</h2>
        <RouterLink to="/c/oils" class="sh-more">عرض الكل</RouterLink>
      </div>
      <div v-if="loading" class="grid">
        <ProductCardSkeleton v-for="i in 5" :key="i" />
      </div>
      <div v-else class="grid">
        <ProductCard
          v-for="product in bestSellers"
          :key="product.id"
          :product="product"
        />
      </div>
    </section>

    <!-- Weekly Offers Row -->
    <section class="sec in">
      <div class="sh">
        <h2>عروض الأسبوع</h2>
        <RouterLink to="/c/oils?offer=true" class="sh-more">كل العروض</RouterLink>
      </div>
      <div v-if="loading" class="grid">
        <ProductCardSkeleton v-for="i in 5" :key="i" />
      </div>
      <div v-else class="grid">
        <ProductCard
          v-for="product in weeklyOffers"
          :key="product.id"
          :product="product"
        />
      </div>
    </section>

    <!-- Used Bikes Row -->
    <section class="sec in">
      <div class="sh">
        <h2>موتوسيكلات مستعملة مفحوصة</h2>
        <RouterLink to="/bikes" class="sh-more">كل الموتوسيكلات ({{ storeConfig.totalInspectedBikes }})</RouterLink>
      </div>
      <div v-if="loading" class="grid g3">
        <BikeCardSkeleton v-for="i in 3" :key="i" />
      </div>
      <div v-else class="grid g3">
        <BikeCard
          v-for="bike in featuredBikes"
          :key="bike.id"
          :bike="bike"
        />
      </div>
    </section>

    <!-- Service Booking Section -->
    <section class="in">
      <div class="svc">
        <div class="tx">
          <h2>احجز صيانة موتوسيكلك أونلاين</h2>
          <p>اختار الخدمة والميعاد، ويوصلك تأكيد على الواتساب. الدفع في الجراج.</p>
          <ul>
            <li v-for="svc in storeConfig.serviceFeatures" :key="svc.title">
              <span>{{ svc.title }}</span>
              <span class="svc-price">{{ svc.price }}</span>
            </li>
          </ul>
          <RouterLink to="/booking" class="btn">احجز ميعاد</RouterLink>
        </div>
        <div class="art">
          <BikeIllustration color="#d6d6d6" stroke="#5a5a5a" :width="400" />
        </div>
      </div>
    </section>

    <!-- Trust Perks Bar -->
    <section class="in">
      <div class="trust">
        <div v-for="t in storeConfig.trustItems" :key="t.title" class="trust-item">
          <AppIcon :name="t.icon" :size="30" :stroke-width="1.4" class="trust-icon" />
          <div class="trust-text">
            <b>{{ t.title }}</b>
            <small>{{ t.desc }}</small>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { categories } from '@/data/categories.js'
import { api } from '@/api/index.js'
import { useUserStore } from '@/stores/user.js'
import { useCartStore } from '@/stores/cart.js'
import ProductCard from '@/components/common/ProductCard.vue'
import ProductCardSkeleton from '@/components/common/ProductCardSkeleton.vue'
import BikeCard from '@/components/common/BikeCard.vue'
import BikeCardSkeleton from '@/components/common/BikeCardSkeleton.vue'
import ProductIllustration from '@/components/common/ProductIllustration.vue'
import BikeIllustration from '@/components/common/BikeIllustration.vue'
import HeroCarousel from '@/components/common/HeroCarousel.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const router = useRouter()
const userStore = useUserStore()
const cartStore = useCartStore()

const loading = ref(true)
const error = ref('')
const allProducts = ref([])
const allBikes = ref([])

const loadHomeData = async () => {
  loading.value = true
  error.value = ''
  try {
    const [prods, bks] = await Promise.all([
      api.getProducts(),
      api.getBikes()
    ])
    allProducts.value = prods
    allBikes.value = bks
  } catch (e) {
    error.value = 'تعذر تحميل بيانات الصفحة الرئيسية'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadHomeData()
})

// Hero Carousel slides
const heroSlides = [
  {
    title: `طقم صيانة الـ 15,000 كم بخصم ${storeConfig.heroKitDiscount}`,
    desc: 'زيت موتول 7100 + فلتر أصلي + تيل Brembo. يوصلك البيت أو نركّبهولك في الجراج.',
    btnPrimary: `اطلب الطقم · ${storeConfig.heroKitPriceFormatted}`,
    bikeColor: '#9e2b25',
    action: 'kit'
  },
  {
    title: 'زيوت موتول التخليقية 100%',
    desc: 'حماية كاملة لمحرك موتوسيكلك في درجات الحرارة العالية وسلاسة فائقة في نقل السرعات.',
    btnPrimary: 'تسوّق الزيوت',
    bikeColor: '#2d4f80',
    action: 'category',
    category: 'oils'
  },
  {
    title: 'فحص شامل 120 نقطة قبل الشراء',
    desc: 'لا تشتري موتوسيكل مستعمل بدون تقرير فحص معتمد من مهندسي جراج أبو يوسف.',
    btnPrimary: 'احجز فحص فوري',
    bikeColor: '#56634a',
    action: 'booking'
  }
]

const handleHeroAction = (slide) => {
  if (slide?.action === 'category') {
    router.push(`/c/${slide.category || 'oils'}`)
  } else if (slide?.action === 'booking') {
    router.push('/booking')
  } else {
    const p1 = allProducts.value.find((p) => p.id === 'p1')
    if (p1) {
      const opt = p1.options && p1.options.length > 0 ? p1.options[0] : null
      cartStore.addItem(p1, 1, opt)
    }
    router.push('/cart')
  }
}

// Finder State
const selectedModel = ref(userStore.selectedBike || 'ZT350-T')
const selectedYear = ref(userStore.userBikeYear || 2023)
const selectedCategory = ref('')

const handleFinderSubmit = () => {
  userStore.setSelectedBike(selectedModel.value, selectedYear.value)
  const targetCategory = selectedCategory.value || 'oils'
  router.push({
    path: `/c/${targetCategory}`,
    query: {
      model: selectedModel.value,
      year: selectedYear.value
    }
  })
}

// Data slices matching shop.html
const bestSellers = computed(() => {
  return allProducts.value.slice(0, 5)
})

const weeklyOffers = computed(() => {
  const prods = allProducts.value
  if (prods.length >= 8) {
    return [prods[0], prods[2], prods[4], prods[9] || prods[1], prods[7]]
  }
  return prods.slice(0, 5)
})

const featuredBikes = computed(() => {
  return allBikes.value.slice(0, 3)
})
</script>

<style scoped>
.hero-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 16px;
  padding-top: 20px;
}

.ban {
  border-radius: var(--radius-box);
  overflow: hidden;
  position: relative;
  display: flex;
  align-items: center;
}

.ban.main {
  min-height: 400px;
  background: #161616;
  color: #ffffff;
  padding: 40px 56px;
  position: relative;
}

.ban.main .tx {
  position: relative;
  z-index: 2;
}

.ban.main .art {
  position: absolute;
  inset-inline-end: 30px;
  bottom: 30px;
  pointer-events: none;
  z-index: 1;
}

.ban.main h2 {
  font-size: 44px;
  font-weight: 800;
  line-height: 1.25;
  margin: 0 0 12px;
  max-width: 14ch;
}

.ban.main p {
  color: #c9c9c9;
  font-size: 17px;
  margin: 0 0 26px;
  max-width: 36ch;
}

.hero-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  position: relative;
  z-index: 3;
}

.hero-outline-btn {
  background: transparent !important;
  box-shadow: inset 0 0 0 1.5px #ffffff !important;
  color: #ffffff !important;
}

.hero-outline-btn:hover {
  background: rgba(255, 255, 255, 0.1) !important;
}

.side2 {
  display: grid;
  grid-template-rows: 1fr 1fr;
  gap: 16px;
}

.ban.s {
  padding: 24px 26px;
  background: var(--sf);
  position: relative;
}

.ban.s h3 {
  font-size: 22px;
  font-weight: 800;
  margin: 0 0 6px;
  line-height: 1.3;
}

.ban.s p {
  margin: 0 0 12px;
  color: var(--mu);
  font-size: 14px;
}

.ban.s a {
  font-weight: 700;
  font-size: 14px;
  color: var(--ink);
  text-decoration: underline;
  text-underline-offset: 4px;
}

.ban.s .art {
  position: absolute;
  inset-inline-end: 14px;
  bottom: 14px;
}

/* Finder bar */
.finder {
  margin-top: 20px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  padding: 18px 22px;
  display: grid;
  grid-template-columns: auto 1fr 1fr 1fr auto;
  gap: 12px;
  align-items: center;
  background: #ffffff;
}

.finder-title b {
  font-size: 17px;
  display: block;
}

.finder-title small {
  display: block;
  font-weight: 400;
  font-size: 13px;
  color: var(--mu);
}

.finder-field {
  position: relative;
  height: 46px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  display: flex;
  align-items: center;
  background: #ffffff;
}

.finder-select {
  width: 100%;
  height: 100%;
  border: none;
  background: transparent;
  padding: 0 14px 0 32px;
  font-size: 14px;
  font-family: inherit;
  color: var(--ink);
  appearance: none;
  cursor: pointer;
}

.finder-select:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: -2px;
}

.finder-field:focus-within {
  border-color: var(--ink);
}

:dir(ltr) .finder-select {
  padding: 0 32px 0 14px;
}

.field-icon {
  position: absolute;
  inset-inline-end: 14px;
  pointer-events: none;
  color: var(--mu);
}

/* Section styling */
.sec {
  padding-top: 44px;
}

.sh {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 18px;
}

.sh h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 800;
}

.sh-more {
  font-size: 14px;
  color: var(--ink);
  font-weight: 700;
  text-decoration: none;
}

.sh-more:hover {
  text-decoration: underline;
}

/* Categories circles */
.cats {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  gap: 14px;
}

.cat-circle {
  text-align: center;
  text-decoration: none;
  color: var(--ink);
  font-size: 14px;
  font-weight: 700;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.cat-circle .im {
  aspect-ratio: 1;
  width: 100%;
  max-width: 100px;
  border-radius: 50%;
  background: var(--sf);
  display: grid;
  place-items: center;
  margin-bottom: 10px;
  transition: transform 0.2s ease, background-color 0.2s ease;
}

.cat-circle:hover .im {
  transform: translateY(-2px);
  background: var(--sf2);
}

/* Grids (Desktop 5 cols -> Tablet 3 cols -> Phone 2 cols) */
.grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
}

.grid.g3 {
  grid-template-columns: repeat(3, 1fr);
}

/* Service Booking Banner */
.svc {
  margin-top: 44px;
  border-radius: var(--radius-box);
  background: var(--sf);
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  overflow: hidden;
}

.svc .tx {
  padding: 40px 44px;
}

.svc h2 {
  font-size: 30px;
  font-weight: 800;
  margin: 0 0 10px;
  line-height: 1.3;
}

.svc p {
  color: var(--mu);
  margin: 0 0 20px;
  max-width: 44ch;
}

.svc ul {
  list-style: none;
  padding: 0;
  margin: 0 0 24px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px 20px;
  font-size: 14.5px;
}

.svc li {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid var(--ln);
  padding-bottom: 8px;
}

.svc-price {
  color: var(--mu);
  font-weight: 700;
}

.svc .art {
  background: #161616;
  display: grid;
  place-items: center;
  padding: 20px;
}

/* Trust Bar */
.trust {
  margin-top: 44px;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  background: #ffffff;
}

.trust-item {
  display: flex;
  gap: 14px;
  align-items: center;
  padding: 20px 22px;
}

.trust-item + .trust-item {
  border-inline-start: 1px solid var(--ln);
}

.trust-text b {
  display: block;
  font-size: 15px;
}

.trust-text small {
  color: var(--mu);
  font-size: 13px;
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .hero-grid {
    grid-template-columns: 1fr;
  }
  .side2 {
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr;
  }
  .cats {
    grid-template-columns: repeat(4, 1fr);
  }
  .grid {
    grid-template-columns: repeat(3, 1fr);
  }
  .trust {
    grid-template-columns: repeat(2, 1fr);
  }
  .trust-item:nth-child(3) {
    border-inline-start: none;
  }
}

@media (max-width: 768px) {
  .finder {
    grid-template-columns: 1fr;
  }
  .side2 {
    grid-template-columns: 1fr;
  }
  .ban.main {
    padding: 30px 20px;
  }
  .ban.main h2 {
    font-size: 32px;
  }
  .ban.main .art {
    display: none;
  }
  .grid.g3 {
    grid-template-columns: 1fr;
  }
  .svc {
    grid-template-columns: 1fr;
  }
  .svc .art {
    display: none;
  }
  .trust {
    grid-template-columns: 1fr;
  }
  .trust-item + .trust-item {
    border-inline-start: none;
    border-top: 1px solid var(--ln);
  }
}

@media (max-width: 576px) {
  .grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
  .cats {
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
  }
  .cat-circle {
    font-size: 12px;
  }
}
</style>
