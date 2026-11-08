<template>
  <div class="login-page in">
    <div class="auth-card">
      <div class="auth-tabs">
        <button
          type="button"
          class="auth-tab"
          :class="{ active: mode === 'login' }"
          @click="switchMode('login')"
        >
          تسجيل الدخول
        </button>
        <button
          type="button"
          class="auth-tab"
          :class="{ active: mode === 'register' }"
          @click="switchMode('register')"
        >
          حساب جديد
        </button>
      </div>

      <!-- General Error Alert -->
      <div v-if="errorMessage" class="auth-error-alert" role="alert">
        {{ errorMessage }}
      </div>

      <!-- Login Form -->
      <form v-if="mode === 'login'" @submit.prevent="handleLogin" class="auth-form">
        <FormField
          id="login-phone"
          label="رقم الموبايل"
          type="tel"
          dir="ltr"
          v-model="phone"
          :error="formErrors.phone"
          placeholder="010 1234 5678"
          required
        />

        <FormField
          id="login-pwd"
          label="كلمة المرور"
          type="password"
          v-model="password"
          :error="formErrors.password"
          placeholder="••••••••"
          required
        />

        <button type="submit" class="btn full auth-btn" :disabled="isSubmitting">
          {{ isSubmitting ? 'جاري تسجيل الدخول...' : 'تسجيل الدخول' }}
        </button>

        <button type="button" class="btn o full whatsapp-btn" @click="handleWhatsAppCode">
          ادخل بكود على الواتساب (قريباً)
        </button>
      </form>

      <!-- Register Form -->
      <form v-else @submit.prevent="handleRegister" class="auth-form">
        <FormField
          id="reg-name"
          label="الاسم بالكامل"
          v-model="name"
          :error="formErrors.name"
          placeholder="مثال: أحمد محمود"
          required
        />

        <FormField
          id="reg-phone"
          label="رقم الموبايل"
          type="tel"
          dir="ltr"
          v-model="phone"
          :error="formErrors.phone"
          placeholder="010 1234 5678"
          required
        />

        <FormField
          id="reg-pwd"
          label="كلمة المرور"
          type="password"
          v-model="password"
          :error="formErrors.password"
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

        <button type="submit" class="btn full auth-btn" :disabled="isSubmitting">
          {{ isSubmitting ? 'جاري إنشاء الحساب...' : 'إنشاء حساب وتأكيد' }}
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
import { normalizePhone, isValidEgyptianPhone } from '@/utils/phone.js'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()
const toastStore = useToastStore()

const mode = ref('login')
const name = ref('')
const phone = ref('')
const password = ref('')
const bikeModel = ref('')
const isSubmitting = ref(false)
const errorMessage = ref('')
const formErrors = ref({})

const switchMode = (newMode) => {
  mode.value = newMode
  errorMessage.value = ''
  formErrors.value = {}
  phone.value = ''
  password.value = ''
}

const redirectAfterAuth = () => {
  const returnTo = route.query.redirect || '/account'
  router.push(returnTo)
}

const handleLogin = async () => {
  if (isSubmitting.value) return
  errorMessage.value = ''
  formErrors.value = {}

  const normPhone = normalizePhone(phone.value)
  if (!normPhone) {
    formErrors.value = { phone: 'رقم الموبايل مطلوب' }
    return
  }
  if (!isValidEgyptianPhone(normPhone)) {
    formErrors.value = { phone: 'رقم الموبايل لازم يكون 11 رقم ويبدأ بـ 01' }
    return
  }

  isSubmitting.value = true

  try {
    await userStore.login(normPhone, password.value)
    toastStore.show('مرحباً بك مجدداً في جراج أبو يوسف!', 'success')
    redirectAfterAuth()
  } catch (err) {
    errorMessage.value = err.message || 'بيانات الدخول غير صحيحة'
    if (err.errors) {
      formErrors.value = err.errors
    }
  } finally {
    isSubmitting.value = false
  }
}

const handleRegister = async () => {
  if (isSubmitting.value) return
  errorMessage.value = ''
  formErrors.value = {}

  const trimmedName = name.value.trim()
  if (!trimmedName) {
    formErrors.value = { name: 'الاسم مطلوب' }
    return
  }

  const normPhone = normalizePhone(phone.value)
  if (!normPhone) {
    formErrors.value = { phone: 'رقم الموبايل مطلوب' }
    return
  }
  if (!isValidEgyptianPhone(normPhone)) {
    formErrors.value = { phone: 'رقم الموبايل لازم يكون 11 رقم ويبدأ بـ 01' }
    return
  }

  isSubmitting.value = true

  try {
    await userStore.register({
      name: trimmedName,
      phone: normPhone,
      password: password.value,
      bikeModel: bikeModel.value || undefined
    })
    toastStore.show('تم إنشاء حسابك بنجاح!', 'success')
    redirectAfterAuth()
  } catch (err) {
    errorMessage.value = err.message || 'تعذر إنشاء الحساب'
    if (err.errors) {
      formErrors.value = err.errors
    }
  } finally {
    isSubmitting.value = false
  }
}

const handleWhatsAppCode = () => {
  if (!phone.value) {
    toastStore.show('يرجى كتابة رقم الموبايل أولاً لإرسال كود الواتساب', 'warning')
    return
  }
  toastStore.show(`خدمة تسجيل الدخول برمز واتساب ستكون متاحة قريباً. يرجى تسجيل الدخول بكلمة المرور.`, 'info')
}
</script>

<style scoped>
.login-page {
  padding-top: 48px;
  padding-bottom: 72px;
  display: flex;
  justify-content: center;
}

.auth-card {
  width: 100%;
  max-width: 440px;
  background: #ffffff;
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 32px 28px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.auth-tabs {
  display: flex;
  gap: 8px;
  border-bottom: 2px solid var(--ln);
  margin-bottom: 24px;
}

.auth-tab {
  flex: 1;
  background: none;
  border: none;
  padding: 12px 16px;
  font-size: 15px;
  font-weight: 600;
  color: var(--mu);
  cursor: pointer;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  transition: all 0.15s ease;
}

.auth-tab.active {
  color: var(--pr);
  border-bottom-color: var(--pr);
}

.auth-error-alert {
  background: #fff1f0;
  border: 1px solid #ffa39e;
  color: #cf1322;
  padding: 10px 14px;
  border-radius: var(--radius-sm);
  font-size: 13.5px;
  margin-bottom: 18px;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.auth-btn {
  margin-top: 8px;
  padding: 12px;
  font-size: 15px;
}

.whatsapp-btn {
  font-size: 14px;
  color: #128c7e;
  border-color: #128c7e;
}

.whatsapp-btn:hover {
  background: #e7f7f4;
}

.form-fld {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-fld label {
  font-size: 13.5px;
  font-weight: 600;
  color: var(--tx);
}

.auth-select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--ln);
  border-radius: var(--radius-sm);
  background: #ffffff;
  font-size: 14px;
  color: var(--tx);
}
</style>
