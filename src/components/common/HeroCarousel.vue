<template>
  <div
    class="hero-carousel-wrap"
    @mouseenter="pauseAutoplay"
    @mouseleave="resumeAutoplay"
    @focusin="pauseAutoplay"
    @focusout="resumeAutoplay"
    @touchstart="handleTouchStart"
    @touchend="handleTouchEnd"
  >
    <slot :current-slide="slides[currentIndex]" :current-index="currentIndex" />

    <!-- Prev / Next navigation arrows (Item 23) -->
    <button
      type="button"
      class="carousel-nav-btn prev"
      @click="prevSlide"
      aria-label="الشريحة السابقة"
    >
      <AppIcon name="right" :size="20" />
    </button>
    <button
      type="button"
      class="carousel-nav-btn next"
      @click="nextSlide"
      aria-label="الشريحة التالية"
    >
      <AppIcon name="left" :size="20" />
    </button>

    <!-- Navigation dots -->
    <div class="dots" role="tablist" aria-label="شرائح العرض">
      <button
        v-for="(_, idx) in slides"
        :key="idx"
        type="button"
        class="dot-btn"
        :class="{ on: currentIndex === idx }"
        @click="goToSlide(idx)"
        :aria-label="`شريحة ${idx + 1}`"
      ></button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import AppIcon from '@/components/common/AppIcon.vue'

const props = defineProps({
  slides: {
    type: Array,
    required: true
  },
  interval: {
    type: Number,
    default: 5000
  }
})

const currentIndex = ref(0)
let timer = null
let touchStartX = 0

const nextSlide = () => {
  currentIndex.value = (currentIndex.value + 1) % props.slides.length
}

const prevSlide = () => {
  currentIndex.value = (currentIndex.value - 1 + props.slides.length) % props.slides.length
}

const goToSlide = (idx) => {
  currentIndex.value = idx
}

const startAutoplay = () => {
  stopAutoplay()
  timer = setInterval(nextSlide, props.interval)
}

const stopAutoplay = () => {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

const pauseAutoplay = () => {
  stopAutoplay()
}

const resumeAutoplay = () => {
  startAutoplay()
}

const handleTouchStart = (e) => {
  touchStartX = e.changedTouches[0].screenX
}

const handleTouchEnd = (e) => {
  const touchEndX = e.changedTouches[0].screenX
  const diff = touchEndX - touchStartX
  if (Math.abs(diff) > 40) {
    if (diff > 0) {
      // In RTL, swipe right goes to next slide
      nextSlide()
    } else {
      prevSlide()
    }
  }
}

onMounted(() => {
  startAutoplay()
})

onUnmounted(() => {
  stopAutoplay()
})

defineExpose({
  next: nextSlide,
  prev: prevSlide,
  goTo: goToSlide,
  currentIndex
})
</script>

<style scoped>
.hero-carousel-wrap {
  position: relative;
  width: 100%;
}

.dots {
  position: absolute;
  bottom: 22px;
  inset-inline-start: 56px;
  display: flex;
  gap: 6px;
  z-index: 10;
}

.dot-btn {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.35);
  border: none;
  cursor: pointer;
  padding: 0;
  transition: all 0.2s ease;
}

.dot-btn.on {
  background: #ffffff;
  width: 22px;
  border-radius: 4px;
}

.dot-btn:focus-visible {
  outline: 2px solid #ffffff;
  outline-offset: 2px;
}

.carousel-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 44px;
  height: 44px;
  min-width: 44px;
  min-height: 44px;
  border-radius: 50%;
  background: rgba(22, 22, 22, 0.65);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.2);
  display: grid;
  place-items: center;
  cursor: pointer;
  z-index: 10;
  transition: all 0.2s ease;
  opacity: 0;
}

.hero-carousel-wrap:hover .carousel-nav-btn,
.carousel-nav-btn:focus-visible {
  opacity: 1;
}

.carousel-nav-btn.prev {
  inset-inline-start: 16px;
}

.carousel-nav-btn.next {
  inset-inline-end: 16px;
}

.carousel-nav-btn:hover {
  background: rgba(22, 22, 22, 0.95);
}

.carousel-nav-btn:focus-visible {
  outline: 2px solid #ffffff;
  outline-offset: 2px;
}

@media (max-width: 768px) {
  .carousel-nav-btn {
    opacity: 0.85;
    width: 44px;
    height: 44px;
    min-width: 44px;
    min-height: 44px;
  }
}
</style>
