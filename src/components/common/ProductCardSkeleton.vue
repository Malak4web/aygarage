<template>
  <div class="pc skeleton-pc">
    <div class="im skeleton"></div>
    <div class="skeleton brand-skeleton"></div>
    <div class="skeleton title-skeleton"></div>
    <div class="skeleton rate-skeleton"></div>
    <div class="skeleton fit-skeleton"></div>
    <div class="skeleton price-skeleton"></div>
  </div>
</template>

<style scoped>
.skeleton-pc {
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  padding: 12px;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  pointer-events: none;
}

.im {
  aspect-ratio: 1;
  border-radius: var(--radius-btn);
  margin-bottom: 12px;
}

.brand-skeleton {
  width: 35%;
  height: 12px;
  margin-bottom: 8px;
}

.title-skeleton {
  width: 85%;
  height: 18px;
  margin-bottom: 10px;
}

.rate-skeleton {
  width: 45%;
  height: 12px;
  margin-bottom: 8px;
}

.fit-skeleton {
  width: 60%;
  height: 14px;
  margin-bottom: 12px;
}

.price-skeleton {
  width: 40%;
  height: 20px;
  margin-top: auto;
}
</style>
