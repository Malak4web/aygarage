<template>
  <img
    v-if="image"
    :src="image"
    :alt="alt || 'موتوسيكل'"
    :width="width"
    class="bike-img"
  />
  <svg
    v-else
    :width="width"
    viewBox="0 0 400 240"
    aria-hidden="true"
    class="bike-svg"
  >
    <g fill="none" :stroke="st" stroke-width="7">
      <circle cx="90" cy="170" r="50" />
      <circle cx="315" cy="170" r="50" />
    </g>
    <g :fill="st">
      <circle cx="90" cy="170" r="18" />
      <circle cx="315" cy="170" r="20" />
    </g>
    <g fill="none" :stroke="st" stroke-width="6" stroke-linecap="round" stroke-linejoin="round">
      <path d="M90 170 L178 148" />
      <path d="M178 148 L202 96 L286 84" />
      <path d="M315 170 L286 70" />
      <path d="M280 60 L306 54" />
      <path d="M186 168 L118 158" />
    </g>
    <path d="M160 122 L232 118 L240 160 L182 170 Z" fill="#555" />
    <path d="M195 80 Q232 54 282 68 L290 92 L206 100 Z" :fill="body" />
    <path d="M106 94 Q150 79 197 83 L199 96 L112 102 Z" fill="#222" />
    <path d="M118 152 L64 141 L60 155 L116 164 Z" fill="#777" />
    <path d="M266 142 Q314 104 352 136" fill="none" :stroke="body" stroke-width="8" stroke-linecap="round" />
  </svg>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  width: {
    type: [Number, String],
    default: 260
  },
  color: {
    type: String,
    default: '#c9c9c9'
  },
  stroke: {
    type: String,
    default: '#3a3a3a'
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

const body = computed(() => props.color || '#c9c9c9')
const st = computed(() => props.stroke || '#3a3a3a')
</script>

<style scoped>
.bike-svg, .bike-img {
  display: block;
  max-width: 100%;
  height: auto;
}
</style>
