<template>
  <div class="fit" :class="statusClass">
    <AppIcon v-if="status === 'fits'" name="check" :size="13" :stroke-width="2.4" />
    <span>{{ message }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useUserStore } from '@/stores/user.js'
import AppIcon from '@/components/common/AppIcon.vue'

const props = defineProps({
  fits: {
    type: Array,
    default: () => []
  },
  note: {
    type: String,
    default: ''
  }
})

const userStore = useUserStore()

const status = computed(() => {
  if (!props.fits || props.fits.length === 0) {
    return 'universal'
  }
  const currentBike = userStore.selectedBike
  if (props.fits.some((model) => currentBike.includes(model) || model.includes(currentBike))) {
    return 'fits'
  }
  return 'unconfirmed'
})

const message = computed(() => {
  if (status.value === 'fits') {
    return `يركب على ${userStore.selectedBike}`
  } else if (status.value === 'universal') {
    return 'مقاسات متعددة'
  } else {
    return 'مش متأكدين إنه يركب على موتوسيكلك، اسألنا'
  }
})

const statusClass = computed(() => ({
  'fit-ok': status.value === 'fits',
  'fit-mu': status.value === 'universal',
  'fit-warn': status.value === 'unconfirmed'
}))
</script>

<style scoped>
.fit {
  font-size: 12px;
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 6px;
}

.fit-ok {
  color: var(--ok);
}

.fit-mu {
  color: var(--mu);
}

.fit-warn {
  color: var(--mu);
}
</style>
