<template>
  <div class="booking-page">
    <Breadcrumbs :items="breadcrumbItems" />

    <!-- Confirmation Banner when completed -->
    <div v-if="bookingConfirmed" class="confirmed-wrap in">
      <div class="confirmed-card">
        <div class="ok-icon-circle">
          <AppIcon name="check" :size="36" :stroke-width="2.5" />
        </div>
        <h2>تم تأكيد حجز الصيانة بنجاح!</h2>
        <p class="ref-text">
          رقم الحجز: <b>#{{ bookingRef }}</b>
        </p>
        <p class="desc">
          مستنينك في جراج أبو يوسف يوم <b>{{ selectedDay.name }} {{ selectedDay.num }} {{ selectedDay.month }}</b> الساعة <b>{{ selectedTime }}</b>.
          هنكلمك نأكد الميعاد قبل ما تيجي.
        </p>
        <div class="confirmed-actions">
          <RouterLink to="/account?tab=bookings" class="btn">
            عرض حجوزاتي
          </RouterLink>
          <button type="button" class="btn o" @click="resetBooking">
            حجز صيانة أخرى
          </button>
        </div>
      </div>
    </div>

    <div v-else class="book in">
      <div class="booking-flow">
        <h1 class="page-title">حجز صيانة</h1>

        <!-- Step 1: Motorcycle -->
        <div class="box">
          <h3>1. الموتوسيكل</h3>

          <!-- Saved bikes list if user has any -->
          <div v-if="userStore.userBikes && userStore.userBikes.length > 0" class="rad-list">
            <RadioCard
              v-for="b in userStore.userBikes"
              :key="b.id"
              name="bike-choice"
              v-model="bikeChoice"
              :value="b.id"
              :title="b.model + ' · ' + b.year"
              :desc="b.plate + ' · ' + Number(b.odo).toLocaleString('en') + ' كم'"
            />
          </div>
          <div v-else class="no-bikes-hint">
            <template v-if="!userStore.isAuthenticated">
              سجّل دخول عشان تختار موتوسيكلك المحفوظ، أو حدد موديل الموتوسيكل والعداد في الأسفل (تأكيد الحجز يتطلب تسجيل الدخول).
            </template>
            <template v-else>
              معندكش موتوسيكلات مسجلة في حسابك، حدد بيانات موتوسيكلك في الأسفل:
            </template>
          </div>

          <div style="margin-top: 10px">
            <RadioCard
              name="bike-choice"
              v-model="bikeChoice"
              value="custom"
              title="موتوسيكل تاني"
              desc="اكتب الموديل والعداد"
            />
          </div>

          <div v-if="bikeChoice === 'custom'" class="custom-bike-inputs">
            <FormField label="موديل الموتوسيكل" id="booking-custom-model">
              <select id="booking-custom-model" v-model="customModel" class="form-select-full">
                <option v-for="m in storeConfig.models" :key="m" :value="m">
                  {{ m }}
                </option>
              </select>
            </FormField>
            <FormField label="قراءة العداد الحالية (كم)" id="booking-custom-odo">
              <input
                id="booking-custom-odo"
                type="number"
                v-model.number="customOdo"
                placeholder="مثال: 12000"
                class="form-input-full"
              />
            </FormField>
          </div>
        </div>

        <!-- Step 2: Service Selection (RadioCards per Item 22) -->
        <div class="box">
          <h3>2. الخدمة</h3>
          <div v-if="loadingServices" class="svs">
            <div v-for="i in 4" :key="i" class="service-skeleton" style="min-height: 80px; border: 1px solid var(--ln); border-radius: var(--radius-box); background: var(--sf); opacity: 0.6;"></div>
          </div>
          <div v-else-if="serviceError" style="padding: 16px 0; text-align: center;">
            <p style="color: var(--sale); margin-bottom: 8px;">{{ serviceError }}</p>
            <button type="button" class="btn sm o" @click="loadServices">إعادة المحاولة</button>
          </div>
          <div v-else class="svs">
            <RadioCard
              v-for="svc in serviceList"
              :key="svc.id"
              name="service"
              :model-value="selectedService?.id"
              :value="svc.id"
              :title="svc.name"
              :desc="svc.desc"
              @update:model-value="selectedService = svc"
            />
          </div>
        </div>

        <!-- Step 3: Date and Time -->
        <div class="box">
          <div class="date-header">
            <h3>3. اليوم والميعاد</h3>
            <span class="month-label">{{ currentMonthLabel }}</span>
          </div>

          <div class="days" role="group" aria-label="الأيام المتاحة">
            <button
              v-for="day in days"
              :key="day.dateString"
              type="button"
              class="day-slot"
              :class="{
                on: selectedDay.dateString === day.dateString,
                off: day.off
              }"
              :disabled="day.off"
              :aria-pressed="selectedDay.dateString === day.dateString"
              @click="!day.off && (selectedDay = day)"
            >
              <span>{{ day.name }}</span>
              <b>{{ day.num }}</b>
            </button>
          </div>

          <div v-if="slotsLoading" style="padding: 16px 0; text-align: center; color: var(--mu);">
            جاري تحميل المواعيد المتاحة...
          </div>
          <div v-else-if="slotsError" style="padding: 16px 0; text-align: center;">
            <p style="color: var(--sale); margin-bottom: 8px;">{{ slotsError }}</p>
            <button type="button" class="btn sm o" @click="loadSlots(selectedDay.dateString)">إعادة المحاولة</button>
          </div>
          <div v-else class="times" role="group" aria-label="الأوقات المتاحة">
            <button
              v-for="time in timeSlots"
              :key="time.val"
              type="button"
              class="time-slot"
              :class="{
                on: selectedTime === time.val,
                off: time.off
              }"
              :disabled="time.off"
              :aria-pressed="selectedTime === time.val"
              @click="!time.off && (selectedTime = time.val)"
            >
              {{ time.val }}
            </button>
          </div>
        </div>
      </div>

      <!-- Sticky Summary -->
      <aside class="sum">
        <h3>ملخص الحجز</h3>
        <div class="dl">
          <span>الموتوسيكل</span>
          <b>{{ selectedBikeTitle }}</b>
        </div>
        <div class="dl">
          <span>الخدمة</span>
          <b>{{ selectedService?.name || '—' }}</b>
        </div>
        <div class="dl">
          <span>الميعاد</span>
          <b>{{ selectedDay.name }} {{ selectedDay.num }} {{ selectedDay.month }} · {{ selectedTime }}</b>
        </div>
        <div class="dl">
          <span>المكان</span>
          <b>جراج أبو يوسف، فرع مصر القديمة</b>
        </div>
        <div class="dl">
          <span>التكلفة التقديرية</span>
          <b>{{ selectedService?.priceFormatted || '—' }}</b>
        </div>

        <div v-if="bookingError" class="submit-error">
          {{ bookingError }}
        </div>

        <button
          type="button"
          class="btn full confirm-btn"
          :disabled="isSubmitting || !selectedService"
          @click="handleConfirmBooking"
        >
          {{ isSubmitting ? 'جاري التأكيد...' : 'تأكيد الحجز' }}
        </button>

        <p class="pay-note">الدفع في الجراج بعد ما الفني يخلص الشغل وتستلم الموتوسيكل وتجرّبه.</p>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter, useRoute, RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { useUserStore } from '@/stores/user.js'
import { api } from '@/api/index.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import FormField from '@/components/common/FormField.vue'
import RadioCard from '@/components/common/RadioCard.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const breadcrumbItems = [
  { label: 'الرئيسية', to: '/' },
  { label: 'حجز صيانة' }
]

// Item 25: Bike selection & reading route.query.bike
const bikeChoice = ref(userStore.userBikes && userStore.userBikes.length > 0 ? userStore.userBikes[0].id : 'custom')
const customModel = ref('ZT350-T')
const customOdo = ref('')

const serviceList = ref([])
const selectedService = ref(null)
const loadingServices = ref(true)
const serviceError = ref('')

const loadServices = async () => {
  loadingServices.value = true
  serviceError.value = ''
  try {
    const list = await api.getServices()
    serviceList.value = list
    restorePendingChoices()
    if (list.length > 0 && !selectedService.value) {
      selectedService.value = list[0]
    }
  } catch (e) {
    serviceError.value = 'تعذر تحميل قائمة الخدمات'
  } finally {
    loadingServices.value = false
  }
}

const restorePendingChoices = () => {
  try {
    const raw = sessionStorage.getItem('ayg_pending_booking')
    if (raw) {
      sessionStorage.removeItem('ayg_pending_booking')
      const p = JSON.parse(raw)
      if (p.bikeChoice) bikeChoice.value = p.bikeChoice
      if (p.customModel) customModel.value = p.customModel
      if (p.customOdo) customOdo.value = p.customOdo
      if (p.time) selectedTime.value = p.time
      if (p.dateString) {
        const foundDay = days.value.find(d => d.dateString === p.dateString)
        if (foundDay) selectedDay.value = foundDay
      }
      if (p.serviceId && serviceList.value.length > 0) {
        const foundSvc = serviceList.value.find(s => s.id === p.serviceId)
        if (foundSvc) selectedService.value = foundSvc
      }
    }
  } catch (e) {}
}

// Item 25: Dynamically generate next 7 days from today (local timezone safe)
const generateNext7Days = () => {
  const dayNames = ['أحد', 'اثنين', 'ثلاثاء', 'أربعاء', 'خميس', 'جمعة', 'سبت']
  const result = []
  const today = new Date()
  for (let i = 0; i < 7; i++) {
    const d = new Date(today.getFullYear(), today.getMonth(), today.getDate() + i)
    const dayOfWeek = d.getDay()
    const year = d.getFullYear()
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    const dateString = `${year}-${month}-${day}`
    result.push({
      dateString,
      name: dayNames[dayOfWeek],
      num: d.getDate(),
      month: d.toLocaleDateString('ar-EG-u-nu-latn', { month: 'short' }),
      off: dayOfWeek === 5 // Friday off
    })
  }
  return result
}

const days = ref(generateNext7Days())
const selectedDay = ref(days.value.find((d) => !d.off) || days.value[0])

const currentMonthLabel = computed(() => {
  const now = new Date()
  return now.toLocaleDateString('ar-EG-u-nu-latn', { month: 'long', year: 'numeric' })
})

const defaultTimeSlots = [
  { val: '11:00 ص', time: '11:00' },
  { val: '12:00 م', time: '12:00', off: true },
  { val: '1:00 م', time: '13:00' },
  { val: '2:00 م', time: '14:00' },
  { val: '3:00 م', time: '15:00' },
  { val: '4:00 م', time: '16:00' },
  { val: '5:00 م', time: '17:00', off: true },
  { val: '6:00 م', time: '18:00' },
  { val: '7:00 م', time: '19:00' },
  { val: '8:00 م', time: '20:00' }
]
const timeSlots = ref([...defaultTimeSlots])
const selectedTime = ref('2:00 م')

let slotsRequestSeq = 0
const slotsLoading = ref(false)
const slotsError = ref('')

const loadSlots = async (dateStr) => {
  if (!dateStr) return
  const currentSeq = ++slotsRequestSeq
  slotsLoading.value = true
  slotsError.value = ''

  try {
    const res = await api.getSlots(dateStr)
    if (currentSeq !== slotsRequestSeq) return

    const list = Array.isArray(res) ? res : (res?.data || [])
    if (list.length > 0) {
      timeSlots.value = list.map((s) => ({
        val: s.label || s.val || s.time,
        time: s.time,
        off: !!s.off,
        available: !!s.available
      }))

      // If today and all slots are past/off, advance to next open day (Item B.1)
      const isToday = dateStr === days.value[0]?.dateString
      const allOff = timeSlots.value.every((s) => s.off)
      if (isToday && allOff) {
        const nextDay = days.value.slice(1).find((d) => !d.off)
        if (nextDay && selectedDay.value?.dateString === dateStr) {
          selectedDay.value = nextDay
          return
        }
      }

      // If current selection is off or not in list, select first available
      const current = timeSlots.value.find((s) => s.val === selectedTime.value || s.time === selectedTime.value)
      if (!current || current.off) {
        const firstAvail = timeSlots.value.find((s) => !s.off)
        if (firstAvail) {
          selectedTime.value = firstAvail.val
        }
      }
    } else {
      timeSlots.value = []
    }
  } catch (err) {
    if (currentSeq !== slotsRequestSeq) return
    slotsError.value = 'تعذر تحميل المواعيد المتاحة'
    timeSlots.value = []
  } finally {
    if (currentSeq === slotsRequestSeq) {
      slotsLoading.value = false
    }
  }
}

watch(
  () => selectedDay.value?.dateString,
  (newDate) => {
    if (newDate) {
      loadSlots(newDate)
    }
  },
  { immediate: true }
)

const bookingConfirmed = ref(false)
const bookingRef = ref('')
const isSubmitting = ref(false)
const bookingError = ref('')

onMounted(() => {
  loadServices()
  if (route.query.bike) {
    bikeChoice.value = 'custom'
    const rawBike = decodeURIComponent(route.query.bike)
    const matched = storeConfig.models.find((m) => rawBike.includes(m))
    customModel.value = matched || rawBike
  }
})

const selectedBikeTitle = computed(() => {
  if (bikeChoice.value !== 'custom') {
    const found = userStore.userBikes?.find((b) => b.id === bikeChoice.value)
    if (found) return `${found.model} · ${found.year}`
  }
  return customModel.value
})

const handleConfirmBooking = async () => {
  if (!userStore.isAuthenticated) {
    try {
      sessionStorage.setItem('ayg_pending_booking', JSON.stringify({
        bikeChoice: bikeChoice.value,
        customModel: customModel.value,
        customOdo: customOdo.value,
        serviceId: selectedService.value?.id,
        dateString: selectedDay.value?.dateString,
        time: selectedTime.value
      }))
    } catch (e) {}

    return router.push({
      path: '/login',
      query: { redirect: route.fullPath }
    })
  }

  if (isSubmitting.value) return
  isSubmitting.value = true
  bookingError.value = ''

  try {
    const bookingPayload = {
      service_id: selectedService.value.id,
      user_bike_id: bikeChoice.value !== 'custom' ? bikeChoice.value : null,
      custom_model: bikeChoice.value === 'custom' ? customModel.value : null,
      odometer: bikeChoice.value === 'custom' ? (customOdo.value ? Number(customOdo.value) : null) : null,
      date: selectedDay.value.dateString,
      time: selectedTime.value,
    }

    const createdBooking = await api.createBooking(bookingPayload)
    bookingRef.value = createdBooking.number || createdBooking.id

    if (userStore.user) {
      if (!userStore.user.bookings) {
        userStore.user.bookings = []
      }
      userStore.user.bookings.unshift(createdBooking)
    }

    bookingConfirmed.value = true
    try {
      sessionStorage.removeItem('ayg_pending_booking')
    } catch (e) {}
  } catch (err) {
    bookingError.value = err?.message || 'حدث خطأ أثناء حجز الموعد، برجاء المحاولة لاحقاً.'
    if (err?.message?.includes('محجوز بالكامل') || err?.status === 422) {
      await loadSlots(selectedDay.value.dateString)
    }
  } finally {
    isSubmitting.value = false
  }
}

const resetBooking = () => {
  bookingConfirmed.value = false
  bookingRef.value = ''
  selectedService.value = serviceList.value[0] || null
}
</script>

<style scoped>
.page-title {
  font-size: 30px;
  font-weight: 800;
  margin: 10px 0 20px;
}

.book {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 32px;
  padding-top: 10px;
  padding-bottom: 48px;
}

.booking-flow {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.box {
  background: #ffffff;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 24px;
}

.box h3 {
  font-size: 18px;
  font-weight: 800;
  margin: 0 0 16px;
}

.no-bikes-hint {
  font-size: 13.5px;
  color: var(--mu);
  margin-bottom: 12px;
}

.rad {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  cursor: pointer;
  margin-bottom: 10px;
  background: #ffffff;
  transition: all 0.15s ease;
  position: relative;
}

.rad:hover {
  border-color: #b5b5b5;
}

.rad.on {
  border-color: var(--ink);
  background: var(--sf);
}

.hidden-radio {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
}

.rad:has(.hidden-radio:focus-visible) {
  outline: 2px solid var(--ink);
  outline-offset: 2px;
}

.rad i {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 1.5px solid var(--mu);
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.rad.on i {
  border-color: var(--ink);
}

.rad.on i::after {
  content: '';
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--ink);
}

.rad-body {
  display: flex;
  flex-direction: column;
}

.rad-body b {
  font-size: 14.5px;
}

.rad-body small {
  color: var(--mu);
  font-size: 13px;
}

.custom-bike-inputs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px dashed var(--ln);
}

.form-select-full,
.form-input-full {
  width: 100%;
  height: 44px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 12px;
  font-size: 14px;
  font-family: inherit;
  outline: none;
}

.form-select-full:focus-visible,
.form-input-full:focus-visible {
  outline: 2px solid var(--ink);
}

/* Service selection buttons */
.svs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

/* Date and Time */
.date-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.date-header h3 {
  margin: 0;
}

.month-label {
  font-size: 14px;
  color: var(--mu);
  font-weight: 700;
}

.days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
  margin-bottom: 20px;
}

.day-slot {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  height: 64px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  background: #ffffff;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s ease;
}

.day-slot:hover:not(.off) {
  border-color: var(--ink);
}

.day-slot.on {
  background: var(--ink);
  color: #ffffff;
  border-color: var(--ink);
}

.day-slot.off {
  background: var(--sf);
  opacity: 0.4;
  cursor: not-allowed;
}

.day-slot:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: 2px;
}

.day-slot span {
  font-size: 12px;
}

.day-slot b {
  font-size: 16px;
}

.times {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 8px;
}

.time-slot {
  height: 38px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  background: #ffffff;
  display: grid;
  place-items: center;
  font-size: 13px;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s ease;
}

.time-slot:hover:not(.off) {
  border-color: var(--ink);
}

.time-slot.on {
  background: var(--ink);
  color: #ffffff;
  border-color: var(--ink);
  font-weight: 700;
}

.time-slot.off {
  background: var(--sf);
  opacity: 0.4;
  cursor: not-allowed;
  text-decoration: line-through;
}

.time-slot:focus-visible {
  outline: 2px solid var(--ink);
  outline-offset: 2px;
}

/* Sidebar Summary */
.sum {
  background: #ffffff;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 24px;
  align-self: flex-start;
}

.sum h3 {
  font-size: 18px;
  font-weight: 800;
  margin: 0 0 18px;
}

.dl {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 13.5px;
}

.dl span {
  color: var(--mu);
}

.dl b {
  text-align: end;
  max-width: 60%;
}

.confirm-btn {
  margin-top: 20px;
}

.submit-error {
  color: var(--sale);
  font-size: 13px;
  margin-top: 12px;
}

.pay-note {
  font-size: 12px;
  color: var(--mu);
  margin: 14px 0 0;
  line-height: 1.5;
}

/* Confirmed Card */
.confirmed-wrap {
  padding-top: 40px;
  display: flex;
  justify-content: center;
}

.confirmed-card {
  max-width: 540px;
  width: 100%;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 40px;
  text-align: center;
  background: #ffffff;
}

.ok-icon-circle {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: var(--oksoft);
  color: var(--ok);
  display: grid;
  place-items: center;
  margin: 0 auto 18px;
}

.confirmed-card h2 {
  font-size: 24px;
  font-weight: 800;
  margin: 0 0 8px;
}

.ref-text {
  font-size: 16px;
  color: var(--mu);
  margin: 0 0 16px;
}

.ref-text b {
  color: var(--ink);
}

.confirmed-card .desc {
  font-size: 14.5px;
  line-height: 1.7;
  color: var(--ink);
  margin: 0 0 28px;
}

.confirmed-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}

@media (max-width: 900px) {
  .book {
    grid-template-columns: minmax(0, 1fr);
  }
  .svs {
    grid-template-columns: minmax(0, 1fr);
  }
  .times {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* ---------- Phone: app booking flow ---------- */
@media (max-width: 768px) {
  .page-title {
    font-size: 24px;
  }
  .book {
    gap: 16px;
    padding-bottom: 24px;
  }
  .booking-flow {
    gap: 14px;
  }
  .box,
  .sum {
    padding: 18px 16px;
    border-radius: 20px;
  }
  .box h3,
  .sum h3 {
    font-size: 17px;
    margin-bottom: 12px;
  }
  /* Days: one swipeable row */
  .days {
    grid-template-columns: none;
    grid-auto-flow: column;
    grid-auto-columns: 56px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    margin-inline: -16px;
    padding: 2px 16px 4px;
    scrollbar-width: none;
  }
  .days::-webkit-scrollbar {
    display: none;
  }
  .day-slot {
    scroll-snap-align: start;
    border-radius: 14px;
  }
  .time-slot {
    border-radius: 12px;
  }
  .confirm-btn {
    height: 52px;
    border-radius: 14px;
    font-size: 16px;
  }
}

</style>
