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
          :alt="product.name"
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
      <AppIcon name="cart" :size="16" class="add-icon-desktop" />
      <svg class="add-icon-phone" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
      <span class="add-label">{{ product.stock === 0 ? 'غير متوفر' : 'أضف للسلة' }}</span>
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

const isFav = computed(() => favStore.isFavorite(props.product.slug || props.product.id))

const toggleFav = () => {
  favStore.toggle(props.product.slug || props.product.id)
}

const discountPercentage = computed(() => {
  if (props.product.oldPrice && props.product.oldPrice > props.product.price) {
    return Math.round((1 - props.product.price / props.product.oldPrice) * 100)
  }
  return 0
})

const handleAddToCart = () => {
  const defaultOption = props.product.options && props.product.options.length > 0 ? props.product.options[0] : null
  cartStore.addItem(props.product, 1, defaultOption)
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
  z-index: 2;
  position: absolute;
  top: 8px;
  inset-inline-start: 8px;
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
  z-index: 2;
  position: absolute;
  top: 8px;
  inset-inline-end: 8px;
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

.add-icon-phone {
  display: none;
}

/* ---------- Phone: compact app card ---------- */
@media (max-width: 768px) {
  .pc {
    padding: 6px 6px 10px;
    border-radius: 18px;
    border-color: #efefef;
  }

  .pc:hover {
    transform: none;
    box-shadow: none;
  }

  .pc:active {
    transform: scale(0.98);
  }

  .pc .im {
    border-radius: 14px;
    margin-bottom: 8px;
  }

  .pc .br,
  .pc h4,
  .pc .price,
  .pc :deep(.rate),
  .pc :deep(.fit) {
    padding-inline: 4px;
  }

  .pc .br {
    font-size: 11px;
  }

  .pc h4 {
    font-size: 13px;
    line-height: 1.45;
    min-height: 38px;
    margin: 1px 0 4px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .pc .price {
    margin: 6px 0 0;
    padding-inline-end: 46px;
    min-height: 38px;
    display: flex;
    align-items: flex-end;
  }

  .pc .price :deep(.price-tag) {
    flex-direction: column-reverse;
    align-items: flex-start;
    gap: 0;
  }

  .pc .price :deep(.current-price) {
    font-size: 16px;
    white-space: nowrap;
  }

  .pc .price :deep(.old-price) {
    font-size: 11.5px;
    line-height: 1.2;
  }

  /* Round add button beside the price */
  .pc .btn {
    position: absolute;
    bottom: 10px;
    inset-inline-end: 8px;
    width: 38px;
    height: 38px;
    padding: 0;
    border-radius: 12px;
  }

  .add-icon-desktop {
    display: none;
  }

  .add-icon-phone {
    display: block;
  }

  .add-label {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
  }

  .pc .fav {
    width: 30px;
    height: 30px;
    top: 6px;
    inset-inline-end: 6px;
  }

  .pc .badge {
    top: 6px;
    inset-inline-start: 6px;
    font-size: 11px;
  }
}
</style>
