<template>
  <div class="pc">
    <div class="im">
      <!-- Badge: Discount % or Custom label (e.g. الأكثر مبيعاً / جديد) -->
      <span v-if="discountPercentage > 0" class="badge">
        -{{ discountPercentage }}%
      </span>
      <span v-else-if="product.badge" class="badge n">
        {{ product.badge }}
      </span>

      <!-- Favorite toggle button -->
      <button
        type="button"
        class="fav"
        :class="{ active: isFav }"
        @click.stop.prevent="toggleFav"
        :aria-label="isFav ? 'إزالة من المفضلة' : 'إضافة إلى المفضلة'"
      >
        <AppIcon name="heart" :size="16" />
      </button>

      <!-- Clickable link to product page -->
      <RouterLink :to="`/p/${product.slug}`" class="im-link" :aria-label="product.name">
        <ProductIllustration
          :type="product.type"
          :color="product.color"
          :image="product.image"
          :width="120"
        />
      </RouterLink>
    </div>

    <!-- Brand -->
    <div class="br">{{ product.brand }}</div>

    <!-- Product Title -->
    <h4>
      <RouterLink :to="`/p/${product.slug}`" class="title-link">
        {{ product.name }}
      </RouterLink>
    </h4>

    <!-- Rating -->
    <RatingStars :rating="product.rating" :count="product.reviewCount" />

    <!-- Fit badge based on selected bike -->
    <FitBadge :product="product" :fits="product.fits" />

    <!-- Price -->
    <div class="price">
      <PriceTag :price="product.price" :old-price="product.oldPrice" />
    </div>

    <!-- Add to cart button -->
    <button
      type="button"
      class="btn sm"
      @click="handleAddToCart"
      :disabled="product.stock === 0"
    >
      <AppIcon name="cart" :size="16" />
      <span>{{ product.stock === 0 ? 'غير متوفر' : 'أضف للسلة' }}</span>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useCartStore } from '@/stores/cart.js'
import { useFavoritesStore } from '@/stores/favorites.js'
import ProductIllustration from '@/components/common/ProductIllustration.vue'
import RatingStars from '@/components/common/RatingStars.vue'
import FitBadge from '@/components/common/FitBadge.vue'
import PriceTag from '@/components/common/PriceTag.vue'
import AppIcon from '@/components/common/AppIcon.vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const cartStore = useCartStore()
const favStore = useFavoritesStore()

const isFav = computed(() => favStore.isFavorite(props.product.id))

const toggleFav = () => {
  favStore.toggle(props.product.id)
}

const discountPercentage = computed(() => {
  if (props.product.oldPrice && props.product.oldPrice > props.product.price) {
    return Math.round((1 - props.product.price / props.product.oldPrice) * 100)
  }
  return 0
})

const handleAddToCart = () => {
  cartStore.addItem(props.product, 1)
}
</script>

<style scoped>
.pc {
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 12px;
  display: flex;
  flex-direction: column;
  position: relative;
  background: #ffffff;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.pc:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

.pc .im {
  aspect-ratio: 1;
  border-radius: 10px;
  background: var(--sf);
  display: grid;
  place-items: center;
  margin-bottom: 12px;
  position: relative;
  overflow: hidden;
}

.im-link {
  display: grid;
  place-items: center;
  width: 100%;
  height: 100%;
  text-decoration: none;
}

.pc .badge {
  position: absolute;
  top: 8px;
  right: 8px;
  background: var(--sale);
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  border-radius: 6px;
  padding: 2px 8px;
  z-index: 2;
}

.pc .badge.n {
  background: var(--ink);
}

.pc .fav {
  position: absolute;
  top: 8px;
  left: 8px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #ffffff;
  display: grid;
  place-items: center;
  color: var(--ink);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  z-index: 2;
  transition: transform 0.15s ease;
}

.pc .fav:hover {
  transform: scale(1.1);
}

.pc .fav.active {
  color: var(--sale);
}

.pc .br {
  font-size: 12px;
  color: var(--mu);
}

.pc h4 {
  margin: 2px 0 6px;
  font-size: 14.5px;
  font-weight: 700;
  line-height: 1.5;
  min-height: 44px;
}

.title-link {
  color: var(--ink);
  text-decoration: none;
}

.title-link:hover {
  text-decoration: underline;
}

.pc .price {
  margin: 8px 0 12px;
}

.pc .btn {
  margin-top: auto;
  width: 100%;
}
</style>
