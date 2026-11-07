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
        <div class="form-fld">
          <label>رقم الموبايل</label>
          <input
            type="tel"
            v-model="phone"
            placeholder="010 1234 5678"
            dir="ltr"
            class="auth-input"
            required
          />
        </div>

        <div class="form-fld">
          <label>كلمة المرور</label>
          <input
            type="password"
            v-model="password"
            placeholder="••••••••"
            class="auth-input"
            required
          />
        </div>

        <button type="submit" class="btn full auth-btn">
          تسجيل الدخول
        </button>

        <button type="button" class="btn o full whatsapp-btn" @click="handleWhatsAppCode">
          ادخل بكود على الواتساب
        </button>
      </form>

      <!-- Register Form -->
      <form v-else @submit.prevent="handleRegister" class="auth-form">
        <div class="form-fld">
          <label>الاسم بالكامل</label>
          <input
            type="text"
            v-model="name"
            placeholder="مثال: كريم محمود"
            class="auth-input"
            required
          />
        </div>

        <div class="form-fld">
          <label>رقم الموبايل</label>
          <input
            type="tel"
            v-model="phone"
            placeholder="010 1234 5678"
            dir="ltr"
            class="auth-input"
            required
          />
        </div>

        <div class="form-fld">
          <label>كلمة المرور</label>
          <input
            type="password"
            v-model="password"
            placeholder="••••••••"
            class="auth-input"
            required
          />
        </div>

        <div class="form-fld">
          <label>موديل موتوسيكلك (اختياري)</label>
          <select v-model="bikeModel" class="auth-select">
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

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const mode = ref('login')
const name = ref('')
const phone = ref('010 1234 5678')
const password = ref('password')
const bikeModel = ref('')

const redirectAfterAuth = () => {
  const returnTo = route.query.redirect || '/account'
  router.push(returnTo)
}

const handleLogin = () => {
  userStore.login(phone.value, password.value)
  redirectAfterAuth()
}

const handleRegister = () => {
  userStore.register(name.value, phone.value, bikeModel.value)
  redirectAfterAuth()
}

const handleWhatsAppCode = () => {
  alert(`تم إرسال كود تسجيل الدخول على رقم الواتساب: ${phone.value}`)
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
  border-radius: var(--radius-box);
  padding: 32px;
  background: #ffffff;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
}

.auth-tabs {
  display: flex;
  border-bottom: 1px solid var(--ln);
  margin-bottom: 24px;
}

.auth-tab {
  flex: 1;
  text-align: center;
  padding: 12px 0;
  font-size: 15px;
  color: var(--mu);
  background: none;
  border: none;
  cursor: pointer;
  font-weight: 500;
  position: relative;
}

.auth-tab.active {
  color: var(--ink);
  font-weight: 700;
  box-shadow: inset 0 -2px 0 var(--ink);
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
  color: var(--mu);
}

.auth-input, .auth-select {
  height: 46px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-btn);
  padding: 0 14px;
  font-size: 14.5px;
  outline: none;
  background: #ffffff;
  color: var(--ink);
}

.auth-btn {
  height: 48px;
  margin-top: 8px;
}

.whatsapp-btn {
  height: 46px;
}
</style>
