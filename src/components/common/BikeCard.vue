<template>
  <div class="bike">
    <div class="im">
      <span class="ins">
        <b>{{ bike.inspectionScore }}</b>
        فحص أبو يوسف
      </span>
      <RouterLink :to="`/bikes/${bike.slug}`" class="bike-link" :aria-label="bike.model">
        <BikeIllustration
          :color="bike.col"
          :image="bike.image"
          :alt="bike.model"
          :width="260"
        />
      </RouterLink>
    </div>
    <div class="bd">
      <h4>
        <RouterLink :to="`/bikes/${bike.slug}`" class="title-link">
          {{ bike.model }}
        </RouterLink>
      </h4>
      <div class="meta">
        <span>{{ bike.year }}</span>
        <span>{{ bike.km }} كم</span>
        <span>{{ bike.owner || 'أول مالك' }}</span>
      </div>
      <div class="row">
        <b>{{ formattedPrice }} <small>ج.م</small></b>
        <RouterLink :to="`/bikes/${bike.slug}`" class="btn o sm">
          التفاصيل
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import BikeIllustration from '@/components/common/BikeIllustration.vue'

const props = defineProps({
  bike: {
    type: Object,
    required: true
  }
})

const formattedPrice = computed(() => {
  return typeof props.bike.price === 'number'
    ? props.bike.price.toLocaleString('en')
    : props.bike.price
})
</script>

<style scoped>
.bike {
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  overflow: hidden;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.bike:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

.bike .im {
  aspect-ratio: 16 / 10;
  overflow: hidden;
  background: var(--sf);
  display: grid;
  place-items: center;
  position: relative;
}

.bike-link {
  display: grid;
  place-items: center;
  width: 100%;
  height: 100%;
  text-decoration: none;
}

.bike .ins {
  position: absolute;
  top: 12px;
  inset-inline-end: 12px;
  background: #ffffff;
  border-radius: 8px;
  padding: 4px 10px;
  font-size: 12.5px;
  font-weight: 700;
  display: flex;
  gap: 6px;
  align-items: center;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
  z-index: 2;
}

.bike .ins b {
  background: var(--ok);
  color: #ffffff;
  border-radius: 5px;
  padding: 0 6px;
  font-size: 12px;
}

.bike .bd {
  padding: 14px 16px 16px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.bike h4 {
  margin: 0;
  font-size: 17px;
  font-weight: 800;
}

.title-link {
  color: var(--ink);
  text-decoration: none;
}

.title-link:hover {
  text-decoration: underline;
}

.bike .meta {
  display: flex;
  gap: 14px;
  color: var(--mu);
  font-size: 13px;
  margin: 4px 0 10px;
}

.bike .row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
}

.bike .row b {
  font-size: 19px;
  font-weight: 800;
}

.bike .row b small {
  font-size: 12px;
  font-weight: 400;
}
</style>
