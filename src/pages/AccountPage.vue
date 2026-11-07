<template>
  <div class="account-page">
    <Breadcrumbs :items="breadcrumbItems" />

    <div class="acc in">
      <!-- Sidebar -->
      <aside class="side">
        <div class="u">
          <b>{{ userStore.user?.name || 'مستخدم الجراج' }}</b>
          <small>{{ userStore.user?.phone || '' }}</small>
        </div>

        <nav class="acc-nav">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            class="tab-link"
            :class="{ on: activeTab === tab.id }"
            @click="activeTab = tab.id"
          >
            <AppIcon :name="tab.icon" :size="18" />
            <span>{{ tab.label }}</span>
          </button>

          <button
            type="button"
            class="tab-link logout-btn"
            @click="handleLogout"
          >
            <AppIcon name="user" :size="18" />
            <span>تسجيل الخروج</span>
          </button>
        </nav>
      </aside>

      <!-- Main Content Area -->
      <main class="acc-main">
        <div v-if="loading" style="padding: 40px; text-align: center; color: var(--mu);">
          <p>جاري تحميل بيانات الحساب...</p>
        </div>

        <div v-else-if="accountError" style="padding: 24px 0;">
          <EmptyState
            title="تعذر تحميل بيانات الحساب"
            :description="accountError"
            action-label="إعادة المحاولة"
            @action="loadAccountData"
          />
        </div>

        <template v-else>
          <!-- 1. My Bikes Tab -->
          <div v-if="activeTab === 'bikes'">
          <div class="sec-head">
            <h1>موتوسيكلاتي</h1>
            <button type="button" class="btn o sm" @click="showAddBikeModal = true">
              + أضف موتوسيكل
            </button>
          </div>

          <!-- Empty State when no bikes (Item 26) -->
          <div v-if="userStore.userBikes.length === 0">
            <EmptyState
              icon="bike"
              title="معندكش موتوسيكلات مسجلة"
              description="سجّل موتوسيكلك لمتابعة مواعيد الصيانة وقطع الغيار المناسبة له بسهولة."
              action-label="+ أضف موتوسيكل"
              @action="showAddBikeModal = true"
            />
          </div>

          <!-- My Vehicle Cards -->
          <div v-else>
            <div
              v-for="b in userStore.userBikes"
              :key="b.id"
              class="veh"
            >
              <div class="im">
                <BikeIllustration :color="b.col" :width="190" />
              </div>

              <div class="veh-info">
                <b>{{ b.model }} · {{ b.year }}</b>
                <div class="veh-plate">لوحة {{ b.plate }}</div>

                <div class="kv">
                  <div>
                    <small>العداد</small>
                    <b>{{ Number(b.odo).toLocaleString('en') }} كم</b>
                  </div>
                  <div>
                    <small>الصيانة الجاية</small>
                    <b>{{ Number(b.nextService).toLocaleString('en') }} كم</b>
                  </div>
                  <div>
                    <small>آخر زيارة</small>
                    <b>{{ b.lastVisit }}</b>
                  </div>
                  <div>
                    <small>الرخصة</small>
                    <b>{{ b.license }}</b>
                  </div>
                </div>

                <!-- Clamped meter 0-100% (Item 26) -->
                <div class="meter">
                  <i :style="{ width: `${Math.min(100, Math.max(0, Math.round((Number(b.odo) / Number(b.nextService)) * 100)))}%` }"></i>
                </div>
                <small class="meter-note">
                  فاضل {{ Math.max(0, Number(b.nextService) - Number(b.odo)).toLocaleString('en') }} كم على الصيانة الجاية
                </small>
              </div>

              <div class="veh-actions">
                <RouterLink to="/booking" class="btn sm">
                  احجز صيانة
                </RouterLink>
                <RouterLink to="/p/motul-7100-10w40" class="btn o sm">
                  اطلب طقم الصيانة
                </RouterLink>
              </div>
            </div>

            <!-- Service History Table Box -->
            <div class="box">
              <div class="box-head">
                <h3>سجل الصيانة</h3>
                <a href="#pdf" @click.prevent="downloadServicePdf">تحميل PDF</a>
              </div>

              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th>التاريخ</th>
                      <th>العداد</th>
                      <th>الشغل</th>
                      <th>الفني</th>
                      <th style="text-align: end">التكلفة</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="r in (userStore.user?.serviceHistory || defaultHistory)" :key="r.date">
                      <td>{{ r.date }}</td>
                      <td>{{ r.odo }}</td>
                      <td>{{ r.service }}</td>
                      <td class="tech-cell">{{ r.tech }}</td>
                      <td style="text-align: end">{{ r.cost }} ج.م</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Recommended parts -->
            <ProductRow title="قطع غيار متوافقة مع موتوسيكلك" :products="compatibleProducts" />
          </div>
        </div>

        <!-- 2. Bookings Tab (Item 26) -->
        <div v-else-if="activeTab === 'bookings'">
          <div class="sec-head">
            <h1>حجوزاتي</h1>
            <RouterLink to="/booking" class="btn sm">
              + حجز جديد
            </RouterLink>
          </div>
          <div v-if="!userBookings || userBookings.length === 0">
            <EmptyState
              icon="wrench"
              title="لا توجد حجوزات صيانة حالية"
              description="يمكنك حجز موعد صيانة أو فحص لموتوسيكلك واختيار الخدمة والميعاد المناسب."
              action-label="احجز صيانة الآن"
              action-to="/booking"
            />
          </div>
          <div v-else class="box">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>رقم الحجز</th>
                    <th>الموتوسيكل</th>
                    <th>الخدمة</th>
                    <th>الميعاد</th>
                    <th>الحالة</th>
                    <th style="text-align: end">التكلفة</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="bkg in userBookings" :key="bkg.id">
                    <td>#{{ bkg.id }}</td>
                    <td><b>{{ bkg.bike }}</b></td>
                    <td>{{ bkg.service }}</td>
                    <td>{{ bkg.date }} · {{ bkg.time }}</td>
                    <td><span class="tg">{{ bkg.status }}</span></td>
                    <td style="text-align: end">
                      {{ bkg.cost || (typeof bkg.price === 'number' ? (bkg.price > 0 ? `${bkg.price.toLocaleString('en')} ج.م` : 'حسب الفحص') : '—') }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 3. Service History Tab -->
        <div v-else-if="activeTab === 'history'">
          <div class="sec-head">
            <h1>سجل الصيانة الكامل</h1>
          </div>
          <div class="box">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>التاريخ</th>
                    <th>العداد</th>
                    <th>الشغل</th>
                    <th>الفني</th>
                    <th style="text-align: end">التكلفة</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in (userStore.user?.serviceHistory || defaultHistory)" :key="r.date">
                    <td>{{ r.date }}</td>
                    <td>{{ r.odo }}</td>
                    <td>{{ r.service }}</td>
                    <td class="tech-cell">{{ r.tech }}</td>
                    <td style="text-align: end">{{ r.cost }} ج.م</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 4. Orders Tab -->
        <div v-else-if="activeTab === 'orders'">
          <div class="sec-head">
            <h1>طلباتي</h1>
          </div>
          <div v-if="!userOrders || userOrders.length === 0">
            <EmptyState
              icon="box"
              title="لا توجد طلبات سابقة"
              description="تصفح المتجر واطلب قطع الغيار والزيوت الأصلية لموتوسيكلك."
              action-label="تصفح المتجر"
              action-to="/c/oils"
            />
          </div>
          <div v-else class="box">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>رقم الطلب</th>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                    <th style="text-align: end">الإجمالي</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="ord in userOrders" :key="ord.id">
                    <td>#{{ ord.id }}</td>
                    <td>{{ ord.date }}</td>
                    <td><span class="tg">{{ ord.status }}</span></td>
                    <td style="text-align: end">{{ ord.total }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 5. Favorites Tab -->
        <div v-else-if="activeTab === 'favorites'">
          <div class="sec-head">
            <h1>المنتجات المفضلة</h1>
          </div>
          <div v-if="favProducts.length === 0">
            <EmptyState
              icon="heart"
              title="لا توجد منتجات في المفضلة"
              description="أضف المنتجات وقطع الغيار التي تهتم بها لتصل إليها بسهولة."
              action-label="تصفح المتجر"
              action-to="/c/oils"
            />
          </div>
          <div v-else class="fav-grid">
            <ProductCard
              v-for="p in favProducts"
              :key="p.id"
              :product="p"
            />
          </div>
        </div>

        <!-- 6. Addresses Tab -->
        <div v-else-if="activeTab === 'addresses'">
          <div class="sec-head">
            <h1>عناويني المسجلة</h1>
          </div>
          <div class="box">
            <b>عنوان المنزل الرئيسي</b>
            <p>{{ userStore.user?.address?.street || 'لم يتم تسجيل العنوان' }}، {{ userStore.user?.address?.area || '' }}، {{ userStore.user?.address?.governorate || '' }}</p>
          </div>
        </div>

        <!-- 7. Settings Tab (Item 26: v-model and saving without alert) -->
        <div v-else-if="activeTab === 'settings'">
          <div class="sec-head">
            <h1>إعدادات الحساب</h1>
          </div>
          <div class="box">
            <div class="fl">
              <div class="fld">
                <FormField id="acc-name" label="الاسم" v-model="settingsName" />
              </div>
              <div class="fld">
                <FormField id="acc-phone" label="الموبايل" type="tel" dir="ltr" v-model="settingsPhone" />
              </div>
            </div>
            <div class="fl" style="margin-top: 12px;">
              <div class="fld">
                <label for="acc-gov">المحافظة</label>
                <select id="acc-gov" v-model="settingsGovernorate" class="form-select">
                  <option v-for="gov in storeConfig.governorates" :key="gov.name" :value="gov.name">
                    {{ gov.name }}
                  </option>
                </select>
              </div>
              <div class="fld">
                <FormField id="acc-area" label="المنطقة" v-model="settingsArea" />
              </div>
            </div>
            <div class="fld" style="margin-top: 12px;">
              <FormField id="acc-street" label="تفاصيل العنوان" v-model="settingsStreet" />
            </div>

            <div v-if="settingsSaved" class="success-banner">
              تم حفظ بيانات الحساب بنجاح.
            </div>

            <button type="button" class="btn sm" style="margin-top: 16px" @click="handleSaveSettings">
              حفظ التعديلات
            </button>
          </div>
        </div>
        </template>
      </main>
    </div>

    <!-- Add Bike Modal -->
    <div v-if="showAddBikeModal" class="modal-backdrop" @click="showAddBikeModal = false">
      <div class="modal-card" ref="addBikeModalRef" @click.stop role="dialog" aria-modal="true" aria-label="إضافة موتوسيكل">
        <h3>إضافة موتوسيكل جديد لحسابك</h3>
        <form @submit.prevent="handleAddBike">
          <div class="form-group">
            <label for="bike-model">الموديل</label>
            <select id="bike-model" v-model="newBikeModel" class="form-select-full">
              <option v-for="m in storeConfig.models" :key="m" :value="m">
                {{ m }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label for="bike-year">سنة الصنع</label>
            <select id="bike-year" v-model="newBikeYear" class="form-select-full">
              <option v-for="y in storeConfig.years" :key="y" :value="y">
                {{ y }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <FormField id="bike-plate" label="رقم اللوحة" v-model="newBikePlate" placeholder="مثال: س ط ع 1234" required />
          </div>
          <div class="form-group">
            <FormField id="bike-odo" label="قراءة العداد (كم)" type="number" v-model="newBikeOdo" placeholder="مثال: 5000" required />
          </div>
          <div class="modal-actions">
            <button type="submit" class="btn">حفظ الموتوسيكل</button>
            <button type="button" class="btn o" @click="showAddBikeModal = false">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { products } from '@/data/products.js'
import { useUserStore } from '@/stores/user.js'
import { useFavoritesStore } from '@/stores/favorites.js'
import { useToastStore } from '@/stores/toast.js'
import { api } from '@/api/index.js'
import { useModalTrap } from '@/utils/modal.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import BikeIllustration from '@/components/common/BikeIllustration.vue'
import ProductCard from '@/components/common/ProductCard.vue'
import ProductRow from '@/components/common/ProductRow.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import FormField from '@/components/common/FormField.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const favStore = useFavoritesStore()
const toastStore = useToastStore()

const loading = ref(true)
const accountError = ref('')
const userBookings = ref([])
const userOrders = ref([])
const allProducts = ref([])

const loadAccountData = async () => {
  loading.value = true
  accountError.value = ''
  try {
    const [bks, ords, prods] = await Promise.all([
      api.getUserBookings(),
      api.getUserOrders(),
      api.getProducts()
    ])
    userBookings.value = bks
    userOrders.value = ords
    allProducts.value = prods
  } catch (e) {
    accountError.value = 'تعذر تحميل بيانات الحساب'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadAccountData()
})

const activeTab = ref(route.query.tab || 'bikes')

const breadcrumbItems = [
  { label: 'الرئيسية', to: '/' },
  { label: 'حسابي' }
]

// Item 26: Add bookings tab
const tabs = [
  { id: 'bikes', label: 'موتوسيكلاتي', icon: 'bike' },
  { id: 'bookings', label: 'حجوزاتي', icon: 'wrench' },
  { id: 'history', label: 'سجل الصيانة', icon: 'wrench' },
  { id: 'orders', label: 'طلباتي', icon: 'box' },
  { id: 'favorites', label: 'المفضلة', icon: 'heart' },
  { id: 'addresses', label: 'العناوين', icon: 'pin' },
  { id: 'settings', label: 'الإعدادات', icon: 'gear' }
]

const favProducts = computed(() => {
  return allProducts.value.filter((p) => favStore.isFavorite(p.id))
})

const compatibleProducts = computed(() => {
  return allProducts.value.slice(0, 5)
})

const defaultHistory = [
  { date: '12 أكتوبر 2026', odo: '12,000 كم', service: 'تغيير زيت + فلتر', tech: 'أبو يوسف', cost: '1,770' },
  { date: '5 يوليو 2026', odo: '9,300 كم', service: 'تيل فرامل أمامي وخلفي', tech: 'محمد', cost: '1,240' },
  { date: '20 مارس 2026', odo: '6,000 كم', service: 'صيانة شاملة', tech: 'أبو يوسف', cost: '2,450' }
]

const showAddBikeModal = ref(false)
const addBikeModalRef = ref(null)

useModalTrap(showAddBikeModal, addBikeModalRef, () => {
  showAddBikeModal.value = false
})
const newBikeModel = ref(storeConfig.models[0])
const newBikeYear = ref(2024)
const newBikePlate = ref('')
const newBikeOdo = ref('')

const handleAddBike = () => {
  userStore.addBike({
    id: 'ub_' + Date.now(),
    model: 'Zontes ' + newBikeModel.value,
    year: Number(newBikeYear.value),
    plate: newBikePlate.value,
    odo: Number(newBikeOdo.value) || 0,
    nextService: (Number(newBikeOdo.value) || 0) + 3000,
    lastVisit: 'اليوم',
    license: '2027',
    col: '#2e2e2e'
  })
  showAddBikeModal.value = false
  toastStore.show('تمت إضافة الموتوسيكل بنجاح', 'success')
}

// Item 26: Settings with v-model
const settingsName = ref(userStore.user?.name || '')
const settingsPhone = ref(userStore.user?.phone || '')
const settingsGovernorate = ref(userStore.user?.address?.governorate || 'القاهرة')
const settingsArea = ref(userStore.user?.address?.area || 'مدينة نصر')
const settingsStreet = ref(userStore.user?.address?.street || '')
const settingsSaved = ref(false)

const handleSaveSettings = () => {
  if (!userStore.user) return
  userStore.user.name = settingsName.value
  userStore.user.phone = settingsPhone.value
  if (!userStore.user.address) userStore.user.address = {}
  userStore.user.address.governorate = settingsGovernorate.value
  userStore.user.address.area = settingsArea.value
  userStore.user.address.street = settingsStreet.value

  try {
    localStorage.setItem('ayg_user', JSON.stringify(userStore.user))
  } catch (e) {}

  settingsSaved.value = true
  toastStore.show('تم حفظ التعديلات بنجاح', 'success')
  setTimeout(() => {
    settingsSaved.value = false
  }, 3000)
}

const handleLogout = () => {
  userStore.logout()
  toastStore.show('تم تسجيل الخروج بنجاح', 'info')
  router.push('/login')
}

const downloadServicePdf = () => {
  toastStore.show('جاري تجهيز وتحميل كشف سجل الصيانة بصيغة PDF.', 'info')
}
</script>

<style scoped>
.acc {
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: 28px;
  padding-top: 18px;
  padding-bottom: 48px;
}

.side {
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 8px;
  align-self: flex-start;
  background: #ffffff;
}

.side .u {
  padding: 12px;
  border-bottom: 1px solid var(--ln);
  margin-bottom: 6px;
}

.side .u b {
  display: block;
  font-size: 15px;
}

.side .u small {
  color: var(--mu);
  font-size: 12.5px;
}

.acc-nav {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.tab-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: var(--radius-btn);
  border: none;
  background: none;
  font-size: 14px;
  font-family: inherit;
  font-weight: 700;
  color: var(--mu);
  cursor: pointer;
  text-align: start;
  transition: all 0.15s ease;
}

.tab-link:hover {
  background: var(--sf);
  color: var(--ink);
}

.tab-link.on {
  background: var(--sf);
  color: var(--ink);
}

.tab-link:focus-visible {
  outline: 2px solid var(--ink);
}

.logout-btn {
  color: var(--sale);
  margin-top: 8px;
  border-top: 1px solid var(--ln);
  border-radius: 0;
  padding-top: 12px;
}

.logout-btn:hover {
  color: var(--sale);
  background: var(--salesoft);
}

.sec-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.sec-head h1 {
  font-size: 26px;
  font-weight: 800;
  margin: 0;
}

.veh {
  display: grid;
  grid-template-columns: 200px 1fr auto;
  gap: 24px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 20px;
  background: #ffffff;
  margin-bottom: 24px;
  align-items: center;
}

.veh .im {
  aspect-ratio: 16/10;
  display: grid;
  place-items: center;
  background: var(--sf);
  border-radius: var(--radius-box);
}

.veh-info b {
  font-size: 18px;
}

.veh-plate {
  font-size: 13px;
  color: var(--mu);
  margin-top: 2px;
}

.kv {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin: 16px 0;
}

.kv small {
  display: block;
  font-size: 12px;
  color: var(--mu);
}

.kv b {
  font-size: 14px;
}

.meter {
  height: 8px;
  background: var(--sf);
  border-radius: 4px;
  overflow: hidden;
  margin-bottom: 6px;
}

.meter i {
  display: block;
  height: 100%;
  background: var(--ok);
  border-radius: 4px;
}

.meter-note {
  font-size: 12px;
  color: var(--mu);
}

.veh-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.box {
  background: #ffffff;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 20px;
  margin-bottom: 24px;
}

.box-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.box-head h3 {
  font-size: 17px;
  margin: 0;
}

.box-head a {
  font-size: 13px;
  color: var(--ink);
  text-decoration: underline;
}

.table-wrap {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

th, td {
  padding: 12px 14px;
  text-align: start;
  border-bottom: 1px solid var(--ln);
}

th {
  font-weight: 700;
  color: var(--mu);
}

.tg {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  background: var(--oksoft);
  color: var(--ok);
  font-size: 12px;
  font-weight: 700;
}

.fav-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

.fl {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.fld {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.fld label {
  font-size: 13px;
  font-weight: 700;
}

.form-input,
.form-select {
  height: 44px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 12px;
  font-size: 14px;
  font-family: inherit;
  outline: none;
}

.form-input:focus-visible,
.form-select:focus-visible {
  outline: 2px solid var(--ink);
}

.success-banner {
  background: var(--oksoft);
  color: var(--ok);
  padding: 10px 14px;
  border-radius: 6px;
  font-size: 13.5px;
  margin-top: 14px;
  font-weight: 700;
}

/* Add bike modal */
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
  margin: 0 0 16px;
  font-size: 18px;
  font-weight: 800;
}

.form-group {
  margin-bottom: 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-group label {
  font-size: 13px;
  font-weight: 700;
}

.form-select-full,
.form-input-full {
  width: 100%;
  height: 42px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 12px;
  font-size: 13.5px;
  font-family: inherit;
  outline: none;
}

.form-select-full:focus-visible,
.form-input-full:focus-visible {
  outline: 2px solid var(--ink);
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

@media (max-width: 900px) {
  .acc {
    grid-template-columns: 1fr;
  }
  .veh {
    grid-template-columns: 1fr;
    text-align: center;
  }
  .kv {
    grid-template-columns: 1fr 1fr;
  }
  .fl {
    grid-template-columns: 1fr;
  }
  .fav-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>
