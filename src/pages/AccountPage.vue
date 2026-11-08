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
                      <b>{{ b.lastVisit || '—' }}</b>
                    </div>
                    <div>
                      <small>الرخصة</small>
                      <b>{{ b.license || '—' }}</b>
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
                  <button type="button" class="btn o sm" @click="openEditBikeModal(b)">
                    تعديل
                  </button>
                  <RouterLink to="/booking" class="btn sm">
                    احجز صيانة
                  </RouterLink>
                  <RouterLink to="/p/motul-7100-10w40" class="btn o sm">
                    اطلب طقم الصيانة
                  </RouterLink>
                  <button type="button" class="btn o sm danger-btn" @click="handleDeleteBike(b.id || b.dbId)">
                    حذف
                  </button>
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
                      <tr v-for="r in serviceHistoryList" :key="r.date">
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
                    <tr v-for="r in serviceHistoryList" :key="r.date">
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
              <button type="button" class="btn o sm" @click="openAddAddressModal">
                + أضف عنوان جديد
              </button>
            </div>
            <div v-if="!userAddresses || userAddresses.length === 0">
              <EmptyState
                icon="pin"
                title="لم يتم تسجيل عناوين بعد"
                description="يمكنك إضافة وتحديث عنوان التوصيل لمتابعة وتسهيل وصول طلباتك."
                action-label="+ أضف عنوان جديد"
                @action="openAddAddressModal"
              />
            </div>
            <div v-else class="address-cards">
              <div v-for="addr in userAddresses" :key="addr.id" class="box" style="margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                  <b>{{ addr.isDefault ? 'العنوان الرئيسي' : 'عنوان توصيل' }}</b>
                  <span v-if="addr.isDefault" class="tg">افتراضي</span>
                </div>
                <p>{{ addr.street }}، {{ addr.area || '' }}، {{ addr.governorate || '' }}</p>
                <div style="display: flex; gap: 8px; margin-top: 10px;">
                  <button type="button" class="btn o sm" @click="openEditAddressModal(addr)">
                    تعديل
                  </button>
                  <button type="button" class="btn o sm danger-btn" @click="handleDeleteAddress(addr.id)">
                    حذف
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- 7. Settings Tab (Item 7 & 10) -->
          <div v-else-if="activeTab === 'settings'">
            <div class="sec-head">
              <h1>إعدادات الحساب</h1>
            </div>
            <div class="box">
              <div class="fl">
                <div class="fld">
                  <FormField id="acc-name" label="الاسم" v-model="settingsName" />
                  <span v-if="settingsErrors.name" class="field-error">{{ settingsErrors.name[0] }}</span>
                </div>
                <div class="fld">
                  <FormField
                    id="acc-phone"
                    label="الموبايل (لا يمكن تغييره)"
                    type="tel"
                    dir="ltr"
                    v-model="settingsPhone"
                    readonly
                    disabled
                  />
                </div>
              </div>
              <div class="fl" style="margin-top: 12px;">
                <div class="fld">
                  <label for="acc-gov">المحافظة</label>
                  <select id="acc-gov" v-model="settingsGovernorate" class="form-select">
                    <option value="" disabled>اختر المحافظة</option>
                    <option v-for="gov in storeConfig.governorates" :key="gov.name" :value="gov.name">
                      {{ gov.name }}
                    </option>
                  </select>
                  <span v-if="settingsErrors.governorate" class="field-error">{{ settingsErrors.governorate[0] }}</span>
                </div>
                <div class="fld">
                  <FormField id="acc-area" label="المنطقة" v-model="settingsArea" />
                  <span v-if="settingsErrors.area" class="field-error">{{ settingsErrors.area[0] }}</span>
                </div>
              </div>
              <div class="fld" style="margin-top: 12px;">
                <FormField id="acc-street" label="تفاصيل العنوان" v-model="settingsStreet" />
                <span v-if="settingsErrors.street" class="field-error">{{ settingsErrors.street[0] }}</span>
              </div>

              <div v-if="settingsSaved" class="success-banner">
                تم حفظ بيانات الحساب بنجاح.
              </div>

              <button type="button" class="btn sm" style="margin-top: 16px" @click="handleSaveSettings" :disabled="isSavingSettings">
                {{ isSavingSettings ? 'جاري الحفظ...' : 'حفظ التعديلات' }}
              </button>
            </div>

            <!-- Password Change Section (Item 10) -->
            <div class="box" style="margin-top: 24px;">
              <div class="sec-head" style="margin-bottom: 16px;">
                <h3>تغيير كلمة المرور</h3>
              </div>
              <form @submit.prevent="handleUpdatePassword">
                <div class="form-group">
                  <FormField
                    id="acc-current-pw"
                    label="كلمة المرور الحالية"
                    type="password"
                    v-model="currentPassword"
                    required
                  />
                  <span v-if="passwordErrors.current_password" class="field-error">{{ passwordErrors.current_password[0] }}</span>
                </div>
                <div class="fl" style="margin-top: 12px;">
                  <div class="fld">
                    <FormField
                      id="acc-new-pw"
                      label="كلمة المرور الجديدة"
                      type="password"
                      v-model="newPassword"
                      required
                    />
                    <span v-if="passwordErrors.new_password" class="field-error">{{ passwordErrors.new_password[0] }}</span>
                  </div>
                  <div class="fld">
                    <FormField
                      id="acc-confirm-pw"
                      label="تأكيد كلمة المرور الجديدة"
                      type="password"
                      v-model="confirmPassword"
                      required
                    />
                    <span v-if="passwordErrors.confirm" class="field-error">{{ passwordErrors.confirm }}</span>
                  </div>
                </div>

                <div v-if="passwordSaved" class="success-banner" style="margin-top: 12px;">
                  تم تحديث كلمة المرور بنجاح.
                </div>

                <button type="submit" class="btn sm" style="margin-top: 16px" :disabled="isUpdatingPassword">
                  {{ isUpdatingPassword ? 'جاري التحديث...' : 'تحديث كلمة المرور' }}
                </button>
              </form>
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
            <button type="submit" class="btn" :disabled="isAddingBike">
              {{ isAddingBike ? 'جاري الحفظ...' : 'حفظ الموتوسيكل' }}
            </button>
            <button type="button" class="btn o" @click="showAddBikeModal = false">إلغاء</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Bike Modal (Item 10) -->
    <div v-if="showEditBikeModal" class="modal-backdrop" @click="showEditBikeModal = false">
      <div class="modal-card" ref="editBikeModalRef" @click.stop role="dialog" aria-modal="true" aria-label="تعديل الموتوسيكل">
        <h3>تعديل بيانات الموتوسيكل</h3>
        <form @submit.prevent="handleUpdateBike">
          <div class="form-group">
            <label for="edit-bike-year">سنة الصنع</label>
            <select id="edit-bike-year" v-model="editBikeYear" class="form-select-full">
              <option v-for="y in storeConfig.years" :key="y" :value="y">
                {{ y }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <FormField id="edit-bike-plate" label="رقم اللوحة" v-model="editBikePlate" placeholder="مثال: س ط ع 1234" required />
          </div>
          <div class="form-group">
            <FormField id="edit-bike-odo" label="قراءة العداد (كم)" type="number" v-model="editBikeOdo" placeholder="مثال: 5000" required />
          </div>
          <div class="modal-actions">
            <button type="submit" class="btn" :disabled="isEditingBike">
              {{ isEditingBike ? 'جاري الحفظ...' : 'حفظ التعديلات' }}
            </button>
            <button type="button" class="btn o" @click="showEditBikeModal = false">إلغاء</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Address Modal (Add / Edit) (Item 10) -->
    <div v-if="showAddressModal" class="modal-backdrop" @click="showAddressModal = false">
      <div class="modal-card" ref="addressModalRef" @click.stop role="dialog" aria-modal="true" :aria-label="editingAddressId ? 'تعديل العنوان' : 'إضافة عنوان'">
        <h3>{{ editingAddressId ? 'تعديل العنوان' : 'إضافة عنوان توصيل جديد' }}</h3>
        <form @submit.prevent="handleSaveAddressModal">
          <div class="form-group">
            <label for="modal-addr-gov">المحافظة</label>
            <select id="modal-addr-gov" v-model="addrGov" class="form-select-full">
              <option v-for="gov in storeConfig.governorates" :key="gov.name" :value="gov.name">
                {{ gov.name }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <FormField id="modal-addr-area" label="المنطقة" v-model="addrArea" placeholder="مثال: مدينة نصر" required />
          </div>
          <div class="form-group">
            <FormField id="modal-addr-street" label="تفاصيل العنوان والشارع" v-model="addrStreet" placeholder="مثال: 15 شارع الطيران، الدور الثالث" required />
          </div>
          <div class="modal-actions">
            <button type="submit" class="btn" :disabled="isSavingAddress">
              {{ isSavingAddress ? 'جاري الحفظ...' : (editingAddressId ? 'حفظ التعديلات' : 'إضافة العنوان') }}
            </button>
            <button type="button" class="btn o" @click="showAddressModal = false">إلغاء</button>
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
import { useUserStore } from '@/stores/user.js'
import { useFavoritesStore } from '@/stores/favorites.js'
import { useToastStore } from '@/stores/toast.js'
import { api } from '@/api/index.js'
import { useModalTrap } from '@/utils/modal.js'
import { doesFit } from '@/utils/fit.js'
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
const serviceHistoryList = ref([])

const loadAccountData = async () => {
  loading.value = true
  accountError.value = ''
  try {
    const [userData, bks, ords, prods, hist] = await Promise.all([
      userStore.fetchUser(),
      api.getUserBookings(),
      api.getUserOrders(),
      api.getProducts({ model: userStore.selectedBike || undefined, perPage: 8 }),
      api.getServiceHistory()
    ])
    if (userData) {
      settingsName.value = userData.name || ''
      settingsPhone.value = userData.phone || ''
      if (userData.address) {
        settingsGovernorate.value = userData.address.governorate || ''
        settingsArea.value = userData.address.area || ''
        settingsStreet.value = userData.address.street || ''
      }
    }
    userBookings.value = bks
    userOrders.value = ords
    allProducts.value = prods
    serviceHistoryList.value = hist
    await favStore.syncFavorites()
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
  if (favStore.favoriteProducts && favStore.favoriteProducts.length > 0) {
    return favStore.favoriteProducts
  }
  return allProducts.value.filter((p) => favStore.isFavorite(p.id) || favStore.isFavorite(p.slug))
})

const userAddresses = computed(() => {
  if (userStore.user?.addresses && userStore.user.addresses.length > 0) {
    return userStore.user.addresses
  }
  if (userStore.user?.address?.street) {
    return [userStore.user.address]
  }
  return []
})

const compatibleProducts = computed(() => {
  if (userStore.selectedBike) {
    return allProducts.value.filter((p) => doesFit(p, userStore.selectedBike) !== false).slice(0, 5)
  }
  return allProducts.value.slice(0, 5)
})

// --- Add Bike Modal ---
const showAddBikeModal = ref(false)
const addBikeModalRef = ref(null)

useModalTrap(showAddBikeModal, addBikeModalRef, () => {
  showAddBikeModal.value = false
})
const newBikeModel = ref(storeConfig.models[0])
const newBikeYear = ref(2024)
const newBikePlate = ref('')
const newBikeOdo = ref('')
const isAddingBike = ref(false)

const handleAddBike = async () => {
  if (isAddingBike.value) return
  isAddingBike.value = true
  try {
    await userStore.addBike({
      bikeModelCode: newBikeModel.value,
      model: 'Zontes ' + newBikeModel.value,
      year: Number(newBikeYear.value),
      plate: newBikePlate.value,
      odometer: Number(newBikeOdo.value) || 0
    })
    showAddBikeModal.value = false
    newBikePlate.value = ''
    newBikeOdo.value = ''
    toastStore.show('تمت إضافة الموتوسيكل بنجاح', 'success')
  } catch (err) {
    toastStore.show(err.message || 'تعذر إضافة الموتوسيكل', 'error')
  } finally {
    isAddingBike.value = false
  }
}

// --- Edit Bike Modal (Item 10) ---
const showEditBikeModal = ref(false)
const editBikeModalRef = ref(null)
useModalTrap(showEditBikeModal, editBikeModalRef, () => {
  showEditBikeModal.value = false
})
const editBikeId = ref(null)
const editBikeModelCode = ref('')
const editBikeYear = ref(2024)
const editBikePlate = ref('')
const editBikeOdo = ref('')
const editBikeColor = ref('')
const isEditingBike = ref(false)

const openEditBikeModal = (bike) => {
  editBikeId.value = bike.id || bike.dbId
  editBikeModelCode.value = bike.bikeModelCode || (bike.model ? bike.model.replace(/^Zontes\s+/i, '') : storeConfig.models[0])
  editBikeYear.value = bike.year || 2024
  editBikePlate.value = bike.plate || ''
  editBikeOdo.value = bike.odo ?? bike.odometer ?? ''
  editBikeColor.value = bike.col || bike.color || '#2e2e2e'
  showEditBikeModal.value = true
}

const handleUpdateBike = async () => {
  if (isEditingBike.value || !editBikeId.value) return
  isEditingBike.value = true
  try {
    await userStore.updateBike(editBikeId.value, {
      bikeModelCode: editBikeModelCode.value,
      model: 'Zontes ' + editBikeModelCode.value,
      year: Number(editBikeYear.value),
      plate: editBikePlate.value,
      odometer: Number(editBikeOdo.value) || 0,
      color: editBikeColor.value
    })
    showEditBikeModal.value = false
    toastStore.show('تم تحديث بيانات الموتوسيكل بنجاح', 'success')
    await userStore.fetchUser()
  } catch (err) {
    toastStore.show(err.message || 'تعذر تحديث بيانات الموتوسيكل', 'error')
  } finally {
    isEditingBike.value = false
  }
}

const handleDeleteBike = async (bikeId) => {
  if (!confirm('هل أنت متأكد من حذف هذا الموتوسيكل من حسابك؟')) return
  try {
    await userStore.deleteBike(bikeId)
    toastStore.show('تم حذف الموتوسيكل بنجاح', 'success')
  } catch (err) {
    toastStore.show(err.message || 'تعذر حذف الموتوسيكل', 'error')
  }
}

// --- Address Modal (Item 10) ---
const showAddressModal = ref(false)
const addressModalRef = ref(null)
useModalTrap(showAddressModal, addressModalRef, () => {
  showAddressModal.value = false
})
const editingAddressId = ref(null)
const addrGov = ref(storeConfig.governorates[0]?.name || 'القاهرة')
const addrArea = ref('')
const addrStreet = ref('')
const isSavingAddress = ref(false)

const openAddAddressModal = () => {
  editingAddressId.value = null
  addrGov.value = storeConfig.governorates[0]?.name || 'القاهرة'
  addrArea.value = ''
  addrStreet.value = ''
  showAddressModal.value = true
}

const openEditAddressModal = (addr) => {
  editingAddressId.value = addr.id
  addrGov.value = addr.governorate || storeConfig.governorates[0]?.name || 'القاهرة'
  addrArea.value = addr.area || ''
  addrStreet.value = addr.street || ''
  showAddressModal.value = true
}

const handleSaveAddressModal = async () => {
  if (isSavingAddress.value) return
  isSavingAddress.value = true
  try {
    const payload = {
      governorate: addrGov.value,
      area: addrArea.value.trim(),
      street: addrStreet.value.trim(),
      is_default: editingAddressId.value ? undefined : true
    }
    if (editingAddressId.value) {
      await api.updateAddress(editingAddressId.value, payload)
      toastStore.show('تم تحديث العنوان بنجاح', 'success')
    } else {
      await api.saveAddress(payload)
      toastStore.show('تمت إضافة العنوان بنجاح', 'success')
    }
    showAddressModal.value = false
    await userStore.fetchUser()
  } catch (err) {
    toastStore.show(err.message || 'تعذر حفظ العنوان', 'error')
  } finally {
    isSavingAddress.value = false
  }
}

const handleDeleteAddress = async (id) => {
  if (!confirm('هل أنت متأكد من حذف هذا العنوان؟')) return
  try {
    await api.deleteAddress(id)
    toastStore.show('تم حذف العنوان بنجاح', 'success')
    await userStore.fetchUser()
  } catch (err) {
    toastStore.show(err.message || 'تعذر حذف العنوان', 'error')
  }
}

// --- Settings Tab (Item 7 & 10) ---
const settingsName = ref(userStore.user?.name || '')
const settingsPhone = ref(userStore.user?.phone || '')
const settingsGovernorate = ref(userStore.user?.address?.governorate || '')
const settingsArea = ref(userStore.user?.address?.area || '')
const settingsStreet = ref(userStore.user?.address?.street || '')
const settingsErrors = ref({})
const settingsSaved = ref(false)
const isSavingSettings = ref(false)

const handleSaveSettings = async () => {
  if (isSavingSettings.value) return
  isSavingSettings.value = true
  settingsErrors.value = {}
  try {
    await userStore.updateProfile({
      name: settingsName.value.trim()
    })

    if (settingsStreet.value.trim()) {
      const existingAddress = userStore.user?.address || userAddresses.value[0]
      if (existingAddress?.id) {
        await api.updateAddress(existingAddress.id, {
          governorate: settingsGovernorate.value,
          area: settingsArea.value.trim(),
          street: settingsStreet.value.trim(),
          is_default: true
        })
      } else {
        await api.saveAddress({
          governorate: settingsGovernorate.value,
          area: settingsArea.value.trim(),
          street: settingsStreet.value.trim(),
          is_default: true
        })
      }
    }
    await userStore.fetchUser()
    settingsSaved.value = true
    toastStore.show('تم حفظ التعديلات بنجاح', 'success')
    setTimeout(() => {
      settingsSaved.value = false
    }, 3000)
  } catch (err) {
    if (err.errors) {
      settingsErrors.value = err.errors
    }
    toastStore.show(err.message || 'تعذر حفظ بيانات الحساب', 'error')
  } finally {
    isSavingSettings.value = false
  }
}

// --- Password Change (Item 10) ---
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const passwordErrors = ref({})
const isUpdatingPassword = ref(false)
const passwordSaved = ref(false)

const handleUpdatePassword = async () => {
  passwordErrors.value = {}
  if (newPassword.value !== confirmPassword.value) {
    passwordErrors.value = { confirm: 'كلمة المرور غير متطابقة' }
    return
  }
  if (newPassword.value.length < 6) {
    passwordErrors.value = { new_password: ['كلمة المرور الجديدة يجب ألا تقل عن 6 أحرف'] }
    return
  }

  isUpdatingPassword.value = true
  try {
    await api.updateMe({
      current_password: currentPassword.value,
      new_password: newPassword.value
    })
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
    passwordSaved.value = true
    toastStore.show('تم تحديث كلمة المرور بنجاح', 'success')
    setTimeout(() => {
      passwordSaved.value = false
    }, 3000)
  } catch (err) {
    if (err.errors) {
      passwordErrors.value = err.errors
    }
    toastStore.show(err.message || 'تعذر تحديث كلمة المرور', 'error')
  } finally {
    isUpdatingPassword.value = false
  }
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
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: var(--ink);
  background: transparent;
  width: 100%;
  text-align: start;
  transition: all 0.15s ease;
}

.tab-link:hover {
  background: var(--sf);
}

.tab-link.on {
  background: var(--sf2);
  font-weight: 700;
}

.logout-btn {
  margin-top: 12px;
  border-top: 1px solid var(--ln);
  border-radius: 0;
  padding-top: 14px;
  color: var(--sale);
}

.logout-btn:hover {
  background: rgba(220, 38, 38, 0.05);
}

.acc-main {
  min-width: 0;
}

.sec-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.sec-head h1 {
  font-size: 22px;
  font-weight: 800;
  margin: 0;
}

.sec-head h3 {
  font-size: 17px;
  font-weight: 800;
  margin: 0;
}

.veh {
  display: grid;
  grid-template-columns: 200px 1fr auto;
  gap: 20px;
  padding: 18px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  align-items: center;
  margin-bottom: 20px;
  background: #ffffff;
}

.veh .im {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--sf);
  border-radius: 8px;
  padding: 12px;
}

.veh-info {
  min-width: 0;
}

.veh-info > b {
  font-size: 17px;
  display: block;
}

.veh-plate {
  display: inline-block;
  background: var(--sf);
  border: 1px solid var(--ln);
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 700;
  margin: 4px 0 10px;
}

.kv {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 12px;
}

.kv small {
  display: block;
  font-size: 11.5px;
  color: var(--mu);
}

.kv b {
  font-size: 13.5px;
}

.meter {
  height: 6px;
  background: var(--sf2);
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 4px;
}

.meter i {
  display: block;
  height: 100%;
  background: var(--ink);
  border-radius: 3px;
  transition: width 0.3s ease;
}

.meter-note {
  font-size: 11.5px;
  color: var(--mu);
}

.veh-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.danger-btn {
  color: var(--sale);
  border-color: rgba(220, 38, 38, 0.3);
}

.danger-btn:hover {
  background: rgba(220, 38, 38, 0.05);
}

.box {
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 18px;
  background: #ffffff;
  margin-bottom: 20px;
}

.box-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.box-head h3 {
  font-size: 16px;
  font-weight: 800;
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
  font-size: 13.5px;
}

th {
  text-align: start;
  padding: 8px 12px;
  color: var(--mu);
  font-weight: 600;
  border-bottom: 1px solid var(--ln);
  white-space: nowrap;
}

td {
  padding: 10px 12px;
  border-bottom: 1px solid var(--ln);
  white-space: nowrap;
}

tr:last-child td {
  border-bottom: none;
}

.tech-cell {
  color: var(--mu);
}

.tg {
  display: inline-block;
  background: var(--sf);
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11.5px;
  font-weight: 600;
}

.fl {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.fld {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.fld label {
  font-size: 12.5px;
  font-weight: 700;
  color: var(--ink);
}

.form-select {
  height: 42px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 10px;
  font-size: 13.5px;
  font-family: inherit;
  background: #ffffff;
  outline: none;
}

.form-select:focus-visible {
  outline: 2px solid var(--ink);
}

.field-error {
  display: block;
  color: var(--sale, #dc2626);
  font-size: 12px;
  margin-top: 3px;
}

.success-banner {
  margin-top: 12px;
  padding: 10px 14px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 6px;
  color: #166534;
  font-size: 13px;
}

.fav-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
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
