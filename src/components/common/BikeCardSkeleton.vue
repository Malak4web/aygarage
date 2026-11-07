<template>
  <div class="bike skeleton-bike">
    <div class="im skeleton"></div>
    <div class="bd">
      <div class="skeleton title-skeleton"></div>
      <div class="skeleton meta-skeleton"></div>
      <div class="row">
        <div class="skeleton price-skeleton"></div>
        <div class="skeleton btn-skeleton"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.skeleton-bike {
  border: 1px solid var(--ln);
  border-radius: var(--radius-card);
  overflow: hidden;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  pointer-events: none;
}

.im {
  height: 180px;
  width: 100%;
}

.bd {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.title-skeleton {
  width: 70%;
  height: 20px;
}

.meta-skeleton {
  width: 50%;
  height: 14px;
}

.row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 6px;
}

.price-skeleton {
  width: 40%;
  height: 22px;
}

.btn-skeleton {
  width: 30%;
  height: 34px;
  border-radius: var(--radius-btn);
}
</style>
