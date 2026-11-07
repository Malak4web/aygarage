<template>
  <div class="account-page">
    <Breadcrumbs :items="breadcrumbItems" />

    <div class="acc in">
      <!-- Sidebar -->
      <aside class="side">
        <div class="u">
          <b>{{ userStore.user?.name || 'كريم محمود' }}</b>
          <small>{{ userStore.user?.phone || '010 1234 5678' }}</small>
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
        </nav>
      </aside>

      <!-- Main Content Area -->
      <main class="acc-main">
        <!-- 1. My Bikes Tab -->
        <div v-if="activeTab === 'bikes'">
          <div class="sec-head">
            <h1>موتوسيكلاتي</h1>
            <button type="button" class="btn o sm" @click="showAddBikeModal = true">
              + أضف موتوسيكل
            </button>
          </div>

          <!-- My Vehicle Card -->
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
                  <b>{{ b.odo.toLocaleString('en') }} كم</b>
                </div>
                <div>
                  <small>الصيانة الجاية</small>
                  <b>{{ b.nextService.toLocaleString('en') }} كم</b>
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

              <div class="meter">
                <i :style="{ width: `${Math.round((b.odo / b.nextService) * 100)}%` }"></i>
              </div>
              <small class="meter-note">
                فاضل {{ (b.nextService - b.odo).toLocaleString('en') }} كم على الصيانة الجاية
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
                    <th style="text-align: left">التكلفة</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in userStore.user.serviceHistory" :key="r.date">
                    <td>{{ r.date }}</td>
                    <td>{{ r.odo }}</td>
                    <td>{{ r.service }}</td>
                    <td class="tech-cell">{{ r.tech }}</td>
                    <td style="text-align: left">{{ r.cost }} ج.م</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Recent Orders Box -->
          <div class="box">
            <div class="box-head">
              <h3>آخر الطلبات</h3>
              <button type="button" class="link-btn" @click="activeTab = 'orders'">
                كل الطلبات
              </button>
            </div>

            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>رقم الطلب</th>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                    <th style="text-align: left">الإجمالي</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="ord in userStore.user.orders" :key="ord.id">
                    <td>#{{ ord.id }}</td>
                    <td>{{ ord.date }}</td>
                    <td><span class="tg">{{ ord.status }}</span></td>
                    <td style="text-align: left">{{ ord.total }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 2. Service History Tab -->
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
                    <th style="text-align: left">التكلفة</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in userStore.user.serviceHistory" :key="r.date">
                    <td>{{ r.date }}</td>
                    <td>{{ r.odo }}</td>
                    <td>{{ r.service }}</td>
                    <td class="tech-cell">{{ r.tech }}</td>
                    <td style="text-align: left">{{ r.cost }} ج.م</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 3. Orders Tab -->
        <div v-else-if="activeTab === 'orders'">
          <div class="sec-head">
            <h1>طلباتي</h1>
          </div>
          <div class="box">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>رقم الطلب</th>
                    <th>التاريخ</th>
                    <th>الحالة</th>
                    <th style="text-align: left">الإجمالي</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="ord in userStore.user.orders" :key="ord.id">
                    <td>#{{ ord.id }}</td>
                    <td>{{ ord.date }}</td>
                    <td><span class="tg">{{ ord.status }}</span></td>
                    <td style="text-align: left">{{ ord.total }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- 4. Favorites Tab -->
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
          <div v-else class="grid g3">
            <ProductCard
              v-for="p in favProducts"
              :key="p.id"
              :product="p"
            />
          </div>
        </div>

        <!-- 5. Addresses Tab -->
        <div v-else-if="activeTab === 'addresses'">
          <div class="sec-head">
            <h1>عناويني المسجلة</h1>
          </div>
          <div class="box">
            <b>عنوان المنزل الرئيسي</b>
            <p>{{ userStore.user?.address?.street }}، {{ userStore.user?.address?.area }}، {{ userStore.user?.address?.governorate }}</p>
          </div>
        </div>

        <!-- 6. Settings Tab -->
        <div v-else-if="activeTab === 'settings'">
          <div class="sec-head">
            <h1>إعدادات الحساب</h1>
          </div>
          <div class="box">
            <div class="fl">
              <div class="fld">
                <label>الاسم</label>
                <input type="text" :value="userStore.user.name" class="form-input" />
              </div>
              <div class="fld">
                <label>الموبايل</label>
                <input type="text" :value="userStore.user.phone" class="form-input" dir="ltr" />
              </div>
            </div>
            <button type="button" class="btn sm" style="margin-top: 16px" @click="handleSaveSettings">
              حفظ التعديلات
            </button>
          </div>
        </div>
      </main>
    </div>

    <!-- Add Bike Modal -->
    <div v-if="showAddBikeModal" class="modal-backdrop" @click="showAddBikeModal = false">
      <div class="modal-card" @click.stop>
        <h3>إضافة موتوسيكل جديد لحسابك</h3>
        <form @submit.prevent="handleAddBike">
          <div class="form-group">
            <label>الموديل</label>
            <select v-model="newBikeModel" class="form-select-full">
              <option v-for="m in storeConfig.models" :key="m" :value="m">
                {{ m }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>سنة الصنع</label>
            <select v-model="newBikeYear" class="form-select-full">
              <option v-for="y in storeConfig.years" :key="y" :value="y">
                {{ y }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>رقم اللوحة</label>
            <input type="text" v-model="newBikePlate" placeholder="مثال: س ط ع 1234" class="form-input-full" required />
          </div>
          <div class="form-group">
            <label>قراءة العداد (كم)</label>
            <input type="number" v-model="newBikeOdo" placeholder="مثال: 5000" class="form-input-full" required />
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
import { ref, computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { products } from '@/data/products.js'
import { useUserStore } from '@/stores/user.js'
import { useFavoritesStore } from '@/stores/favorites.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import BikeIllustration from '@/components/common/BikeIllustration.vue'
import ProductCard from '@/components/common/ProductCard.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const route = useRoute()
const userStore = useUserStore()
const favStore = useFavoritesStore()

const activeTab = ref(route.query.tab || 'bikes')

const breadcrumbItems = [
  { label: 'الرئيسية', to: '/' },
  { label: 'حسابي' }
]

const tabs = [
  { id: 'bikes', label: 'موتوسيكلاتي', icon: 'bike' },
  { id: 'history', label: 'سجل الصيانة', icon: 'wrench' },
  { id: 'orders', label: 'طلباتي', icon: 'box' },
  { id: 'favorites', label: 'المفضلة', icon: 'heart' },
  { id: 'addresses', label: 'العناوين', icon: 'pin' },
  { id: 'settings', label: 'الإعدادات', icon: 'gear' }
]

const favProducts = computed(() => {
  return products.filter((p) => favStore.isFavorite(p.id))
})

const showAddBikeModal = ref(false)
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
}

const handleSaveSettings = () => {
  alert('تم حفظ بيانات الحساب بنجاح.')
}

const downloadServicePdf = () => {
  alert('جاري تجهيز وتحميل كشف سجل الصيانة بصيغة PDF.')
}
</script>

<style scoped>
.acc {
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: 28px;
  padding-top: 18px;
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
}

.acc-nav {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.tab-link {
  display: flex;
  gap: 10px;
  align-items: center;
  padding: 10px 12px;
  border-radius: 10px;
  color: var(--ink);
  font-size: 14.5px;
  width: 100%;
  text-align: right;
  border: none;
  background: none;
  cursor: pointer;
  transition: background 0.15s ease;
}

.tab-link:hover {
  background: var(--sf);
}

.tab-link.on {
  background: var(--sf);
  font-weight: 700;
}

.sec-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.sec-head h1 {
  font-size: 26px;
  margin: 0;
  font-weight: 800;
}

/* Vehicle Card */
.veh {
  display: grid;
  grid-template-columns: 220px 1fr auto;
  gap: 22px;
  align-items: center;
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  padding: 16px;
  margin-bottom: 16px;
  background: #ffffff;
}

.veh .im {
  aspect-ratio: 16 / 10;
  border-radius: 10px;
  background: var(--sf);
  display: grid;
  place-items: center;
}

.veh-info b {
  font-size: 18px;
  display: block;
}

.veh-plate {
  color: var(--mu);
  font-size: 14px;
  margin-top: 2px;
}

.kv {
  display: grid;
  grid-template-columns: repeat(4, auto);
  gap: 22px;
  margin-top: 10px;
}

.kv small {
  display: block;
  color: var(--mu);
  font-size: 12px;
}

.kv b {
  font-size: 16px;
}

.meter {
  height: 6px;
  border-radius: 6px;
  background: var(--sf2);
  margin-top: 12px;
  overflow: hidden;
}

.meter i {
  display: block;
  height: 100%;
  background: var(--ink);
}

.meter-note {
  display: block;
  color: var(--mu);
  margin-top: 4px;
  font-size: 13px;
}

.veh-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

/* Box & Tables */
.box {
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  padding: 22px;
  margin-bottom: 16px;
  background: #ffffff;
}

.box-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.box h3 {
  margin: 0;
  font-size: 17px;
}

.box a, .link-btn {
  font-size: 14px;
  font-weight: 400;
  text-decoration: underline;
  color: var(--ink);
  background: none;
  border: none;
  cursor: pointer;
}

.table-wrap {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

th {
  text-align: right;
  font-weight: 400;
  color: var(--mu);
  padding: 10px 0;
  border-bottom: 1px solid var(--ln);
}

td {
  padding: 11px 0;
  border-bottom: 1px solid var(--ln);
}

.tech-cell {
  color: var(--mu);
}

/* Modals */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  display: grid;
  place-items: center;
  z-index: 1000;
  padding: 16px;
}

.modal-card {
  background: #ffffff;
  border-radius: var(--radius-box);
  padding: 28px;
  width: 100%;
  max-width: 440px;
}

.modal-card h3 {
  margin: 0 0 16px;
}

.form-group {
  margin-bottom: 14px;
}

.form-group label {
  display: block;
  font-size: 13px;
  color: var(--mu);
  margin-bottom: 4px;
}

.form-select-full, .form-input-full {
  width: 100%;
  height: 44px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 12px;
  font-size: 14px;
  outline: none;
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
    gap: 16px;
  }
  .kv {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
}
</style>
