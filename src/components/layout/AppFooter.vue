<template>
  <footer class="app-footer" :class="{ 'has-sticky-bar': isProductPage }">
    <div class="footer-inner">
      <div class="cols">
        <!-- Col 1: About -->
        <div class="col-brand">
          <div class="logo">
            {{ storeConfig.name }}
            <small>{{ storeConfig.tagline }}</small>
          </div>
          <p class="brand-desc">
            قطع غيار وزيوت وإكسسوارات أصلية لموتوسيكلات Zontes، وصيانة وفحص في الجراج.
          </p>
        </div>

        <!-- Col 2: Shop -->
        <div class="col-links" :class="{ open: openCol === 'shop' }">
          <h5>
            <button type="button" class="col-toggle" :aria-expanded="openCol === 'shop'" @click="toggleCol('shop')">
              <span>تسوّق</span>
              <AppIcon name="down" :size="16" class="col-chev" />
            </button>
          </h5>
          <RouterLink to="/c/oils">زيوت</RouterLink>
          <RouterLink to="/c/filters">فلاتر</RouterLink>
          <RouterLink to="/c/brakes">فرامل</RouterLink>
          <RouterLink to="/c/helmets">خوذ وملابس</RouterLink>
          <RouterLink to="/c/oils?offer=true">العروض</RouterLink>
        </div>

        <!-- Col 3: Garage -->
        <div class="col-links" :class="{ open: openCol === 'garage' }">
          <h5>
            <button type="button" class="col-toggle" :aria-expanded="openCol === 'garage'" @click="toggleCol('garage')">
              <span>الجراج</span>
              <AppIcon name="down" :size="16" class="col-chev" />
            </button>
          </h5>
          <RouterLink to="/booking">حجز صيانة</RouterLink>
          <RouterLink to="/booking">فحص قبل الشراء</RouterLink>
          <RouterLink to="/bikes">موتوسيكلات للبيع</RouterLink>
          <a href="#footer">العنوان والمواعيد</a>
        </div>

        <!-- Col 4: Help -->
        <div class="col-links" :class="{ open: openCol === 'help' }">
          <h5>
            <button type="button" class="col-toggle" :aria-expanded="openCol === 'help'" @click="toggleCol('help')">
              <span>مساعدة</span>
              <AppIcon name="down" :size="16" class="col-chev" />
            </button>
          </h5>
          <a href="#footer">الشحن والتوصيل</a>
          <a href="#footer">الاسترجاع والاستبدال</a>
          <RouterLink to="/account">تتبع الطلب</RouterLink>
          <a href="#footer">الأسئلة الشائعة</a>
        </div>

        <!-- Col 5: Newsletter & Contact -->
        <div class="col-newsletter">
          <h5>العروض على إيميلك</h5>
          <p class="nl-desc">خصومات وتذكير بمواعيد الصيانة.</p>
          <form class="nl-form" @submit.prevent="subscribed = true">
            <label for="footer-newsletter-email" class="visually-hidden">البريد الإلكتروني للعروض</label>
            <input
              id="footer-newsletter-email"
              type="email"
              placeholder="بريدك الإلكتروني"
              class="nl-input"
              required
            />
            <button type="submit" class="nl-btn">
              {{ subscribed ? 'تم!' : 'اشترك' }}
            </button>
          </form>
          <div class="contact-info">
            <a :href="`https://wa.me/${storeConfig.whatsapp}`" target="_blank" rel="noopener">
              واتساب: {{ storeConfig.phone }}
            </a>
          </div>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="footer-bottom">
        <span>© 2026 {{ storeConfig.name }} · <RouterLink to="/credits" class="credits-link">مصادر الصور</RouterLink></span>
        <div class="pay-methods">
          <span v-for="pm in storeConfig.paymentMethods" :key="pm.id">
            {{ pm.title }}
          </span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import AppIcon from '@/components/common/AppIcon.vue'
import { storeConfig } from '@/config/store.js'

const route = useRoute()
const subscribed = ref(false)
const isProductPage = computed(() => route?.name === 'product' || route?.name === 'bike-detail')

// Phone: link columns collapse into an accordion
const openCol = ref('')
const toggleCol = (key) => {
  openCol.value = openCol.value === key ? '' : key
}
</script>

<style scoped>
.app-footer {
  margin-top: 56px;
  background: #121212;
  color: #cfcfcf;
  padding: 44px var(--page-pad) 24px;
  font-size: 14px;
}

.footer-inner {
  max-width: 1280px;
  margin: 0 auto;
}

.cols {
  display: grid;
  grid-template-columns: 1.6fr 1fr 1fr 1fr 1.3fr;
  gap: 30px;
}

.logo {
  color: #ffffff;
  font-weight: 800;
  font-size: 24px;
  line-height: 1;
}

.logo small {
  display: block;
  font-weight: 400;
  font-size: 11px;
  color: #8a8a8a;
  letter-spacing: 0.25em;
  margin-top: 5px;
}

.brand-desc {
  margin: 14px 0 0;
  max-width: 34ch;
  line-height: 1.7;
  color: #cfcfcf;
}

.col-links h5, .col-newsletter h5 {
  color: #ffffff;
  font-size: 15px;
  margin: 0 0 12px;
  font-weight: 700;
}

.col-links a {
  display: block;
  margin-bottom: 8px;
  color: #cfcfcf;
  text-decoration: none;
  transition: color 0.15s ease;
}

.col-links a:hover {
  color: #ffffff;
}

.nl-desc {
  margin: 0 0 8px;
  color: #cfcfcf;
}

.nl-form {
  display: flex;
  height: 44px;
  border-radius: var(--radius-btn);
  overflow: hidden;
  margin-top: 6px;
}

.nl-input {
  flex: 1;
  background: #262626;
  border: none;
  padding: 0 12px;
  color: #ffffff;
  font-size: 13px;
}

.nl-input:focus-visible {
  outline: 2px solid #ffffff;
  outline-offset: -2px;
}

.nl-input::placeholder {
  color: #8a8a8a;
}

.nl-btn {
  background: #ffffff;
  color: #121212;
  display: grid;
  place-items: center;
  padding: 0 16px;
  font-size: 13px;
  font-weight: 700;
  border: none;
  cursor: pointer;
}

.contact-info {
  margin-top: 16px;
  color: #cfcfcf;
}

.credits-link {
  color: inherit;
  text-decoration: underline;
  text-underline-offset: 3px;
}

.footer-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid #2a2a2a;
  margin-top: 30px;
  padding-top: 18px;
  font-size: 13px;
  color: #8a8a8a;
  flex-wrap: wrap;
  gap: 12px;
}

.pay-methods {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.pay-methods span {
  border: 1px solid #3a3a3a;
  border-radius: 6px;
  padding: 2px 10px;
  font-size: 12px;
  color: #cfcfcf;
}

@media (max-width: 992px) {
  .cols {
    grid-template-columns: 1fr 1fr;
    gap: 24px;
  }

  .col-brand {
    grid-column: span 2;
  }

  .col-newsletter {
    grid-column: span 2;
  }
}

@media (max-width: 768px) {
  .app-footer.has-sticky-bar {
    padding-bottom: 90px;
  }
}

.col-toggle {
  all: unset;
  display: contents;
}

.col-chev {
  display: none;
}

@media (max-width: 600px) {
  .cols {
    grid-template-columns: minmax(0, 1fr);
    gap: 0;
  }

  .col-brand, .col-newsletter {
    grid-column: span 1;
  }

  .col-brand {
    padding-bottom: 16px;
  }

  .col-newsletter {
    padding-top: 20px;
    border-top: 1px solid #2a2a2a;
  }

  .col-links {
    border-top: 1px solid #2a2a2a;
  }


  .col-links h5 {
    margin: 0;
  }

  .col-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 14px 0;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
  }

  .col-chev {
    display: block;
    color: #8a8a8a;
    transition: transform 0.2s ease;
  }

  .col-links.open .col-chev {
    transform: rotate(180deg);
  }

  .col-links a {
    display: none;
  }

  .col-links.open {
    padding-bottom: 12px;
  }

  .col-links.open a {
    display: block;
  }
}
</style>
