<template>
  <div class="login-page in">
    <div class="auth-card">
      <div class="auth-tabs">
        <button
          type="button"
          class="auth-tab"
          :class="{ active: mode === 'login' }"
          @click="mode = 'login'"
        >
          تسجيل الدخول
        </button>
        <button
          type="button"
          class="auth-tab"
          :class="{ active: mode === 'register' }"
          @click="mode = 'register'"
        >
          حساب جديد
        </button>
      </div>

      <!-- Login Form -->
      <form v-if="mode === 'login'" @submit.prevent="handleLogin" class="auth-form">
        <FormField
          id="login-phone"
          label="رقم الموبايل"
          type="tel"
          dir="ltr"
          v-model="phone"
          placeholder="010 1234 5678"
          required
        />

        <FormField
          id="login-pwd"
          label="كلمة المرور"
          type="password"
          v-model="password"
          placeholder="••••••••"
          required
        />

        <button type="submit" class="btn full auth-btn">
          تسجيل الدخول
        </button>

        <button type="button" class="btn o full whatsapp-btn" @click="handleWhatsAppCode">
          ادخل بكود على الواتساب
        </button>
      </form>

      <!-- Register Form -->
      <form v-else @submit.prevent="handleRegister" class="auth-form">
        <FormField
          id="reg-name"
          label="الاسم بالكامل (اختياري)"
          v-model="name"
          placeholder="مثال: أحمد محمود"
        />

        <FormField
          id="reg-phone"
          label="رقم الموبايل"
          type="tel"
          dir="ltr"
          v-model="phone"
          placeholder="010 1234 5678"
          required
        />

        <FormField
          id="reg-pwd"
          label="كلمة المرور"
          type="password"
          v-model="password"
          placeholder="••••••••"
          required
        />

        <div class="form-fld">
          <label for="reg-bike">موديل موتوسيكلك (اختياري)</label>
          <select id="reg-bike" v-model="bikeModel" class="auth-select">
            <option value="">اختر الموديل</option>
            <option v-for="m in storeConfig.models" :key="m" :value="m">
              {{ m }}
            </option>
          </select>
        </div>

        <button type="submit" class="btn full auth-btn">
          إنشاء حساب وتأكيد
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { storeConfig } from '@/config/store.js'
import { useUserStore } from '@/stores/user.js'
import { useToastStore } from '@/stores/toast.js'
import FormField from '@/components/common/FormField.vue'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()
const toastStore = useToastStore()

const mode = ref('login')
const name = ref('')
// Item 29: Clear pre-filled credentials
const phone = ref('')
const password = ref('')
const bikeModel = ref('')

const redirectAfterAuth = () => {
  const returnTo = route.query.redirect || '/account'
  router.push(returnTo)
}

const handleLogin = () => {
  userStore.login(phone.value, password.value)
  toastStore.show('مرحباً بك مجدداً في جراج أبو يوسف!', 'success')
  redirectAfterAuth()
}

const handleRegister = () => {
  userStore.register(name.value, phone.value, bikeModel.value)
  toastStore.show('تم إنشاء حسابك بنجاح!', 'success')
  redirectAfterAuth()
}

// Item 29: Replace alert with toast
const handleWhatsAppCode = () => {
  if (!phone.value) {
    toastStore.show('يرجى كتابة رقم الموبايل أولاً لإرسال كود الواتساب', 'warning')
    return
  }
  toastStore.show(`تم إرسال كود تسجيل الدخول على رقم الواتساب: ${phone.value}`, 'info')
  userStore.login(phone.value, 'whatsapp_token')
  redirectAfterAuth()
}
</script>

<style scoped>
.login-page {
  padding-top: 48px;
  padding-bottom: 64px;
  display: flex;
  justify-content: center;
}

.auth-card {
  max-width: 440px;
  width: 100%;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 32px;
  background: #ffffff;
}

.auth-tabs {
  display: grid;
  grid-template-columns: 1fr 1fr;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 4px;
  background: var(--sf);
  margin-bottom: 24px;
}

.auth-tab {
  height: 38px;
  border: none;
  background: transparent;
  font-family: inherit;
  font-size: 14px;
  font-weight: 700;
  color: var(--mu);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.auth-tab.active {
  background: #ffffff;
  color: var(--ink);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-fld {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-fld label {
  font-size: 13.5px;
  font-weight: 700;
}

.auth-input,
.auth-select {
  height: 46px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 14px;
  font-size: 14px;
  font-family: inherit;
  outline: none;
}

.auth-input:focus-visible,
.auth-select:focus-visible {
  outline: 2px solid var(--ink);
}

.auth-btn {
  margin-top: 8px;
}

.whatsapp-btn {
  margin-top: -6px;
}
</style>
