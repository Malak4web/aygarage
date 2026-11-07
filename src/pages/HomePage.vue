<template>
  <div class="home-page">
    <!-- Hero Section: Main Banner Carousel + 2 Side Banners -->
    <section class="hero-grid in">
      <div class="ban main">
        <div class="art">
          <BikeIllustration :color="currentSlide.bikeColor" :width="460" stroke="#4a4a4a" />
        </div>
        <div class="tx">
          <h2>{{ currentSlide.title }}</h2>
          <p>{{ currentSlide.desc }}</p>
          <div class="hero-actions">
            <button type="button" class="btn w" @click="handleHeroAction">
              {{ currentSlide.btnPrimary }}
            </button>
            <RouterLink to="/booking" class="btn hero-outline-btn">
              احجز التركيب
            </RouterLink>
          </div>
        </div>
        <!-- Carousel Dots -->
        <div class="dots" role="tablist" aria-label="شرائح العرض">
          <button
            v-for="(slide, idx) in heroSlides"
            :key="idx"
            type="button"
            class="dot-btn"
            :class="{ on: activeSlideIndex === idx }"
            @click="activeSlideIndex = idx"
            :aria-label="`شريحة ${idx + 1}`"
          ></button>
        </div>
      </div>

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
            <p>22 موتوسيكل بتقرير فحص</p>
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

    <!-- Best Sellers Row -->
    <section class="sec in">
      <div class="sh">
        <h2>الأكثر مبيعاً</h2>
        <RouterLink to="/c/oils" class="sh-more">عرض الكل</RouterLink>
      </div>
      <div class="grid">
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
      <div class="grid">
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
        <RouterLink to="/bikes" class="sh-more">كل الموتوسيكلات (22)</RouterLink>
      </div>
      <div class="grid g3">
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
            <li v-for="svc in serviceFeatures" :key="svc.title">
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
        <div v-for="t in trustItems" :key="t.title" class="trust-item">
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
import { ref, computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { categories } from '@/data/categories.js'
import { products } from '@/data/products.js'
import { bikes } from '@/data/bikes.js'
import { useUserStore } from '@/stores/user.js'
import { useCartStore } from '@/stores/cart.js'
import ProductCard from '@/components/common/ProductCard.vue'
import BikeCard from '@/components/common/BikeCard.vue'
import ProductIllustration from '@/components/common/ProductIllustration.vue'
import BikeIllustration from '@/components/common/BikeIllustration.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const router = useRouter()
const userStore = useUserStore()
const cartStore = useCartStore()

// Hero Carousel slides
const heroSlides = [
  {
    title: 'طقم صيانة الـ 15,000 كم بخصم 10%',
    desc: 'زيت موتول 7100 + فلتر أصلي + تيل Brembo. يوصلك البيت أو نركّبهولك في الجراج.',
    btnPrimary: 'اطلب الطقم · 5,350 ج.م',
    bikeColor: '#9e2b25'
  },
  {
    title: 'زيوت موتول التخليقية 100%',
    desc: 'حماية كاملة لمحرك موتوسيكلك في درجات الحرارة العالية وسلاسة فائقة في نقل السرعات.',
    btnPrimary: 'تسوّق الزيوت',
    bikeColor: '#2d4f80'
  },
  {
    title: 'فحص شامل 120 نقطة قبل الشراء',
    desc: 'لا تشتري موتوسيكل مستعمل بدون تقرير فحص معتمد من مهندسي جراج أبو يوسف.',
    btnPrimary: 'احجز فحص فوري',
    bikeColor: '#56634a'
  }
]

const activeSlideIndex = ref(0)
const currentSlide = computed(() => heroSlides[activeSlideIndex.value])

const handleHeroAction = () => {
  if (activeSlideIndex.value === 0) {
    const p1 = products.find((p) => p.id === 'p1')
    if (p1) cartStore.addItem(p1, 3)
    router.push('/cart')
  } else if (activeSlideIndex.value === 1) {
    router.push('/c/oils')
  } else {
    router.push('/booking')
  }
}

// Finder State
const selectedModel = ref(userStore.selectedBike || 'ZT350-T')
const selectedYear = ref(2023)
const selectedCategory = ref('')

const handleFinderSubmit = () => {
  userStore.setSelectedBike(selectedModel.value)
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
const bestSellers = computed(() => products.slice(0, 5))
const weeklyOffers = computed(() => [products[0], products[2], products[4], products[9], products[7]])
const featuredBikes = computed(() => bikes.slice(0, 3))

const serviceFeatures = [
  { title: 'تغيير زيت وفلتر', price: '1,770 ج.م' },
  { title: 'صيانة شاملة', price: '2,450 ج.م' },
  { title: 'فحص قبل الشراء', price: '600 ج.م' },
  { title: 'ضبط بلوف', price: '900 ج.م' }
]

const trustItems = [
  { icon: 'truck', title: 'توصيل لكل المحافظات', desc: 'خلال 2-4 أيام عمل' },
  { icon: 'shield', title: 'قطع أصلية', desc: 'من الوكيل أو الشركة المصنعة' },
  { icon: 'cash', title: 'الدفع عند الاستلام', desc: 'أو كارت وفودافون كاش' },
  { icon: 'ret', title: 'استرجاع خلال 14 يوم', desc: 'لو القطعة ما ركبتش' }
]
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
}

.ban.main .art {
  position: absolute;
  left: 30px;
  bottom: 30px;
  pointer-events: none;
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
}

.hero-outline-btn {
  background: transparent !important;
  box-shadow: inset 0 0 0 1.5px #ffffff !important;
  color: #ffffff !important;
}

.hero-outline-btn:hover {
  background: rgba(255, 255, 255, 0.1) !important;
}

.dots {
  position: absolute;
  bottom: 22px;
  right: 56px;
  display: flex;
  gap: 6px;
}

.dot-btn {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.35);
  border: none;
  cursor: pointer;
  padding: 0;
  transition: all 0.2s ease;
}

.dot-btn.on {
  background: #ffffff;
  width: 22px;
  border-radius: 4px;
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
  left: 14px;
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
  padding: 0 14px;
  background: #ffffff;
}

.finder-select {
  width: 100%;
  appearance: none;
  border: none;
  background: transparent;
  font-size: 14px;
  color: var(--ink);
  outline: none;
  cursor: pointer;
  padding-left: 20px;
}

.field-icon {
  position: absolute;
  left: 12px;
  pointer-events: none;
  color: var(--mu);
}

/* Sections */
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
  font-size: 14px;
  font-weight: 700;
  color: var(--ink);
  text-decoration: none;
}

.cat-circle .im {
  aspect-ratio: 1;
  border-radius: 50%;
  background: var(--sf);
  display: grid;
  place-items: center;
  margin-bottom: 10px;
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.cat-circle:hover .im {
  transform: translateY(-2px);
  background: var(--sf2);
}

/* Grids */
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
  border-right: 1px solid var(--ln);
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
@media (max-width: 1100px) {
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
    border-right: none;
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
  .grid {
    grid-template-columns: repeat(2, 1fr);
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
    border-right: none;
    border-top: 1px solid var(--ln);
  }
}
</style>
