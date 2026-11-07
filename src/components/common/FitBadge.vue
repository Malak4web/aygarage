<template>
  <div class="fit" :class="statusClass">
    <AppIcon v-if="fitResult === true" name="check" :size="13" :stroke-width="2.4" />
    <span>{{ message }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useUserStore } from '@/stores/user.js'
import { doesFit } from '@/utils/fit.js'
import AppIcon from '@/components/common/AppIcon.vue'

const props = defineProps({
  product: {
    type: Object,
    default: null
  },
  fits: {
    type: Array,
    default: () => []
  }
})

const userStore = useUserStore()

const fitResult = computed(() => {
  const p = props.product || { fits: props.fits }
  return doesFit(p, userStore.selectedBike)
})

const message = computed(() => {
  if (fitResult.value === true) {
    return `يركب على ${userStore.selectedBike}`
  } else if (fitResult.value === null) {
    return 'مقاسات متعددة'
  } else {
    return 'مش متأكدين إنه يركب على موتوسيكلك، اسألنا'
  }
})

const statusClass = computed(() => ({
  'fit-ok': fitResult.value === true,
  'fit-mu': fitResult.value === null,
  'fit-warn': fitResult.value === false
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
