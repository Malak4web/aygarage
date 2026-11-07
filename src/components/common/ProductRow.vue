<template>
  <section class="sec in">
    <div v-if="title || moreLink" class="sh">
      <h2 v-if="title">{{ title }}</h2>
      <RouterLink v-if="moreLink && moreText" :to="moreLink" class="sh-more">
        {{ moreText }}
      </RouterLink>
    </div>

    <div :class="['grid', columnsClass]">
      <slot>
        <ProductCard
          v-for="product in products"
          :key="product.id"
          :product="product"
        />
      </slot>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import ProductCard from '@/components/common/ProductCard.vue'

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  moreLink: {
    type: String,
    default: ''
  },
  moreText: {
    type: String,
    default: ''
  },
  products: {
    type: Array,
    default: () => []
  },
  columns: {
    type: [Number, String],
    default: 5
  }
})

const columnsClass = computed(() => {
  return Number(props.columns) === 3 ? 'g3' : 'g5'
})
</script>

<style scoped>
.sec {
  padding-top: 44px;
}

.sh {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 18px;
}

.sh h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 800;
}

.sh-more {
  font-size: 14px;
  color: var(--ink);
  font-weight: 700;
  text-decoration: none;
}

.sh-more:hover {
  text-decoration: underline;
}

.grid {
  display: grid;
  gap: 16px;
}

.grid.g5 {
  grid-template-columns: repeat(5, 1fr);
}

.grid.g3 {
  grid-template-columns: repeat(3, 1fr);
}

@media (max-width: 1100px) {
  .grid.g5 {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .grid.g5 {
    grid-template-columns: repeat(2, 1fr);
  }
  .grid.g3 {
    grid-template-columns: 1fr;
  }
}
</style>
