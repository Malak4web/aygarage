<template>
  <div class="booking-page">
    <Breadcrumbs :items="breadcrumbItems" />

    <div v-if="bookingConfirmed" class="booking-success in">
      <div class="success-box">
        <div class="check-icon">
          <AppIcon name="check" :size="36" :stroke-width="2.5" />
        </div>
        <h2>تم تأكيد ميعاد الصيانة بنجاح!</h2>
        <p class="bk-code">رقم الحجز: <b>{{ bookingRef }}</b></p>
        <p class="bk-desc">
          تم حجز خدمة <b>{{ selectedService.name }}</b> لموتوسيكلك <b>{{ selectedBikeTitle }}</b> يوم <b>{{ selectedDay.name }} {{ selectedDay.num }} نوفمبر الساعة {{ selectedTime }}</b>.
        </p>
        <div class="bk-notice">
          <AppIcon name="wrench" :size="20" />
          <span>هيوصلك رسالة تأكيد على الواتساب، والدفع هيتم في الجراج بعد الانتهاء واستلام الموتوسيكل.</span>
        </div>
        <div class="bk-actions">
          <RouterLink to="/account" class="btn">
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
          <label
            class="rad"
            :class="{ on: bikeChoice === 'saved' }"
          >
            <input
              type="radio"
              value="saved"
              v-model="bikeChoice"
              class="hidden-radio"
            />
            <i></i>
            <span class="rad-body">
              <b>Zontes {{ userStore.selectedBike }} · 2023</b>
              <small>س ط ع 1234 · 12,400 كم</small>
            </span>
          </label>

          <label
            class="rad"
            :class="{ on: bikeChoice === 'custom' }"
          >
            <input
              type="radio"
              value="custom"
              v-model="bikeChoice"
              class="hidden-radio"
            />
            <i></i>
            <span class="rad-body">
              <b>موتوسيكل تاني</b>
              <small>اكتب الموديل والعداد</small>
            </span>
          </label>

          <div v-if="bikeChoice === 'custom'" class="custom-bike-inputs">
            <select v-model="customModel" class="form-select-full">
              <option v-for="m in storeConfig.models" :key="m" :value="m">
                {{ m }}
              </option>
            </select>
            <input
              type="text"
              v-model="customOdo"
              placeholder="قراءة العداد الحالية (كم)"
              class="form-input-full"
            />
          </div>
        </div>

        <!-- Step 2: Service Selection -->
        <div class="box">
          <h3>2. الخدمة</h3>
          <div class="svs">
            <div
              v-for="svc in serviceList"
              :key="svc.id"
              class="service-item"
              :class="{ on: selectedService.id === svc.id }"
              @click="selectedService = svc"
            >
              <b>{{ svc.name }}</b>
              <small>{{ svc.desc }}</small>
            </div>
          </div>
        </div>

        <!-- Step 3: Date and Time -->
        <div class="box">
          <div class="date-header">
            <h3>3. اليوم والميعاد</h3>
            <span class="month-label">نوفمبر 2026</span>
          </div>

          <div class="days">
            <div
              v-for="(day, idx) in days"
              :key="day.num"
              class="day-slot"
              :class="{
                on: selectedDay.num === day.num,
                off: day.off
              }"
              @click="!day.off && (selectedDay = day)"
            >
              <span>{{ day.name }}</span>
              <b>{{ day.num }}</b>
            </div>
          </div>

          <div class="times">
            <span
              v-for="(time, idx) in timeSlots"
              :key="time.val"
              class="time-slot"
              :class="{
                on: selectedTime === time.val,
                off: time.off
              }"
              @click="!time.off && (selectedTime = time.val)"
            >
              {{ time.val }}
            </span>
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
          <b>{{ selectedService.name }}</b>
        </div>
        <div class="dl">
          <span>الميعاد</span>
          <b>{{ selectedDay.name }} {{ selectedDay.num }}/11 · {{ selectedTime }}</b>
        </div>
        <div class="dl">
          <span>المدة</span>
          <b>{{ selectedService.duration }}</b>
        </div>
        <div class="dl tot">
          <span>التكلفة التقديرية</span>
          <b>{{ selectedService.priceFormatted }}</b>
        </div>

        <button
          type="button"
          class="btn full confirm-booking-btn"
          @click="handleConfirmBooking"
        >
          تأكيد الحجز
        </button>

        <div class="booking-notice">
          هيوصلك تأكيد على الواتساب. الدفع في الجراج.
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { services } from '@/data/services.js'
import { useUserStore } from '@/stores/user.js'
import { api } from '@/api/index.js'
import Breadcrumbs from '@/components/common/Breadcrumbs.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const router = useRouter()
const userStore = useUserStore()

const breadcrumbItems = [
  { label: 'الرئيسية', to: '/' },
  { label: 'حجز صيانة' }
]

const bikeChoice = ref('saved')
const customModel = ref('ZT125-U')
const customOdo = ref('')

const serviceList = services
const selectedService = ref(services[0])

const days = [
  { name: 'سبت', num: 7 },
  { name: 'أحد', num: 8 },
  { name: 'اثنين', num: 9 },
  { name: 'ثلاثاء', num: 10 },
  { name: 'أربعاء', num: 11 },
  { name: 'خميس', num: 12 },
  { name: 'جمعة', num: 13, off: true }
]
const selectedDay = ref(days[2]) // الاثنين 9

const timeSlots = [
  { val: '11:00 ص' },
  { val: '12:00 م', off: true },
  { val: '1:00 م' },
  { val: '2:00 م' },
  { val: '3:00 م' },
  { val: '4:00 م' },
  { val: '5:00 م', off: true },
  { val: '6:00 م' },
  { val: '7:00 م' },
  { val: '8:00 م' }
]
const selectedTime = ref('2:00 م')

const bookingConfirmed = ref(false)
const bookingRef = ref('')

const selectedBikeTitle = computed(() => {
  if (bikeChoice.value === 'saved') {
    return userStore.selectedBike
  }
  return customModel.value
})

const handleConfirmBooking = async () => {
  if (!userStore.isAuthenticated) {
    return router.push({
      path: '/login',
      query: { redirect: '/booking' }
    })
  }

  const result = await api.createBooking({
    bike: selectedBikeTitle.value,
    service: selectedService.value.name,
    date: `${selectedDay.value.name} ${selectedDay.value.num} نوفمبر`,
    time: selectedTime.value,
    cost: selectedService.value.priceFormatted
  })
  bookingRef.value = result.id
  bookingConfirmed.value = true
}

const resetBooking = () => {
  bookingConfirmed.value = false
}
</script>

<style scoped>
.page-title {
  font-size: 28px;
  margin: 0 0 16px;
}

.book {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 28px;
  padding-top: 18px;
}

.box {
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  padding: 22px;
  margin-bottom: 16px;
  background: #ffffff;
}

.box h3 {
  margin: 0 0 14px;
  font-size: 17px;
}

.rad {
  display: flex;
  gap: 12px;
  align-items: center;
  border: 1px solid var(--ln);
  border-radius: 12px;
  padding: 14px 16px;
  margin-bottom: 10px;
  cursor: pointer;
}

.rad.on {
  border: 2px solid var(--ink);
}

.hidden-radio {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.rad i {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 1.5px solid #bdbdbd;
  flex: none;
}

.rad.on i {
  border: 6px solid var(--ink);
  background: #ffffff;
}

.rad-body b {
  display: block;
  font-size: 14.5px;
}

.rad-body small {
  color: var(--mu);
  font-size: 13px;
}

.custom-bike-inputs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 10px;
}

.form-select-full, .form-input-full {
  height: 44px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 12px;
  font-size: 14px;
  outline: none;
}

.svs {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.service-item {
  border: 1px solid var(--ln);
  border-radius: 12px;
  padding: 14px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.service-item.on {
  border: 2px solid var(--ink);
  background: var(--sf);
}

.service-item b {
  display: block;
  font-size: 14.5px;
}

.service-item small {
  color: var(--mu);
  font-size: 13px;
}

.date-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.month-label {
  font-size: 14px;
  font-weight: 400;
  color: var(--mu);
}

.days {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
  margin-top: 12px;
}

.day-slot {
  border: 1px solid var(--ln);
  border-radius: 10px;
  text-align: center;
  padding: 8px 0;
  font-size: 12px;
  color: var(--mu);
  cursor: pointer;
  user-select: none;
}

.day-slot b {
  display: block;
  font-size: 19px;
  color: var(--ink);
}

.day-slot.on {
  background: var(--ink);
  border-color: var(--ink);
  color: #bbb;
}

.day-slot.on b {
  color: #ffffff;
}

.day-slot.off {
  opacity: 0.4;
  cursor: not-allowed;
}

.times {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 8px;
  margin-top: 12px;
}

.time-slot {
  height: 40px;
  border: 1px solid var(--ln);
  border-radius: 10px;
  display: grid;
  place-items: center;
  font-size: 14px;
  cursor: pointer;
}

.time-slot.on {
  background: var(--ink);
  color: #ffffff;
  border-color: var(--ink);
  font-weight: 700;
}

.time-slot.off {
  color: #b5b5b5;
  text-decoration: line-through;
  cursor: not-allowed;
}

/* Summary Box */
.sum {
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  padding: 22px;
  align-self: flex-start;
  margin-top: 52px;
  background: #ffffff;
}

.sum h3 {
  margin: 0 0 14px;
  font-size: 18px;
}

.dl {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  font-size: 14.5px;
}

.dl span {
  color: var(--mu);
}

.dl.tot {
  border-top: 1px solid var(--ln);
  margin-top: 8px;
  padding-top: 14px;
  font-size: 17px;
}

.dl.tot b {
  font-size: 20px;
}

.confirm-booking-btn {
  height: 52px;
  margin-top: 14px;
}

.booking-notice {
  font-size: 13px;
  color: var(--mu);
  margin-top: 10px;
  text-align: center;
}

/* Success View */
.booking-success {
  display: flex;
  justify-content: center;
  padding-top: 40px;
  padding-bottom: 60px;
}

.success-box {
  background: #ffffff;
  border: 1px solid var(--ln);
  border-radius: var(--radius-box);
  padding: 36px 32px;
  max-width: 520px;
  text-align: center;
}

.check-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: var(--oksoft);
  color: var(--ok);
  display: grid;
  place-items: center;
  margin: 0 auto 16px;
}

.success-box h2 {
  font-size: 24px;
  margin: 0 0 8px;
}

.bk-code {
  color: var(--mu);
  font-size: 15px;
}

.bk-desc {
  margin: 16px 0;
  line-height: 1.7;
}

.bk-notice {
  display: flex;
  gap: 10px;
  background: var(--sf);
  padding: 12px 14px;
  border-radius: 10px;
  font-size: 13px;
  text-align: right;
  margin-bottom: 24px;
}

.bk-actions {
  display: flex;
  gap: 10px;
  justify-content: center;
}

@media (max-width: 900px) {
  .book {
    grid-template-columns: 1fr;
  }
  .sum {
    margin-top: 16px;
  }
  .svs {
    grid-template-columns: 1fr 1fr;
  }
  .times {
    grid-template-columns: repeat(3, 1fr);
  }
}
</style>
