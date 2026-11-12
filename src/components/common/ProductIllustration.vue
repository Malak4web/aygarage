<template>
  <img
    v-if="image"
    :src="image"
    :alt="alt || ''"
    class="prod-image"
    loading="lazy"
    decoding="async"
  />
  <svg
    v-else
    :width="width"
    viewBox="0 0 100 96"
    :aria-label="alt || undefined"
    :role="alt ? 'img' : undefined"
    :aria-hidden="!alt ? 'true' : undefined"
    class="prod-svg"
    v-html="svgContent"
  />
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'oil'
  },
  width: {
    type: [Number, String],
    default: 120
  },
  color: {
    type: String,
    default: ''
  },
  image: {
    type: String,
    default: ''
  },
  alt: {
    type: String,
    default: ''
  }
})

const c = computed(() => props.color || '#1d1d1d')

const svgContent = computed(() => {
  const col = c.value
  const t = props.type || 'oil'
  const shapes = {
    oil: `<rect x="32" y="20" width="36" height="64" rx="6" fill="${col}"/><rect x="41" y="9" width="18" height="13" rx="2" fill="#555"/><rect x="36" y="40" width="28" height="22" rx="2" fill="#fff"/><rect x="40" y="46" width="20" height="3" fill="${col}"/><rect x="40" y="52" width="14" height="2" fill="#bbb"/>`,
    filter: `<rect x="26" y="28" width="48" height="54" rx="8" fill="${col}"/><rect x="26" y="42" width="48" height="3" fill="#666"/><rect x="26" y="64" width="48" height="3" fill="#666"/><circle cx="50" cy="24" r="9" fill="#8a8a8a"/><rect x="38" y="50" width="24" height="8" rx="1" fill="#fff"/>`,
    brake: `<path d="M20 72 Q20 30 60 22 L80 22 L80 40 Q46 44 42 78 Z" fill="#8f8f8f"/><path d="M28 70 Q30 40 62 32 L74 32 L74 36 Q48 42 44 72 Z" fill="${col}"/>`,
    helmet: `<path d="M16 66 Q16 22 54 20 Q86 20 86 56 L86 72 L28 76 Z" fill="${col}"/><path d="M40 44 Q60 38 84 46 L84 60 L44 62 Z" fill="#6b6b6b"/>`,
    chain: `<g fill="none" stroke="${col}" stroke-width="6"><rect x="12" y="40" width="22" height="16" rx="8"/><rect x="30" y="40" width="22" height="16" rx="8"/><rect x="48" y="40" width="22" height="16" rx="8"/><rect x="66" y="40" width="22" height="16" rx="8"/></g>`,
    plug: `<rect x="44" y="8" width="12" height="20" fill="#eee" stroke="#999"/><rect x="38" y="28" width="24" height="18" fill="#8f8f8f"/><rect x="42" y="46" width="16" height="30" fill="#c4c4c4"/><rect x="48" y="76" width="4" height="12" fill="#777"/>`,
    glove: `<path d="M30 86 L30 46 L34 20 L40 20 L42 42 L46 14 L52 14 L52 42 L58 16 L64 16 L62 44 L68 24 L74 26 L70 60 L66 86 Z" fill="${col}"/><rect x="30" y="74" width="36" height="12" fill="#8f8f8f"/>`,
    light: `<circle cx="50" cy="50" r="30" fill="${col}"/><circle cx="50" cy="50" r="20" fill="#e9e9e9"/><circle cx="50" cy="50" r="8" fill="#fff"/>`,
    tyre: `<circle cx="50" cy="50" r="36" fill="${col}"/><circle cx="50" cy="50" r="20" fill="#e9e9e9"/><circle cx="50" cy="50" r="36" fill="none" stroke="#3a3a3a" stroke-width="4" stroke-dasharray="4 4"/>`,
    battery: `<rect x="20" y="30" width="60" height="44" rx="4" fill="${col}"/><rect x="28" y="24" width="10" height="6" fill="#888"/><rect x="62" y="24" width="10" height="6" fill="#888"/><rect x="30" y="44" width="40" height="14" rx="2" fill="#fff"/>`
  }
  return shapes[t] || shapes.oil
})
</script>

<style scoped>
.prod-svg {
  display: block;
  max-width: 100%;
  height: auto;
}

/* Real photos fill their box (cards, thumbs, gallery) */
.prod-image {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
</style>
