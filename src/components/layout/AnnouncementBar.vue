<template>
  <div class="announcement-bar">
    <div class="ann-content">
      {{ storeConfig.announcement }}
    </div>
  </div>
</template>

<script setup>
import { storeConfig } from '@/config/store.js'
</script>

<style scoped>
.announcement-bar {
  background: var(--ink);
  color: #ffffff;
  font-size: 13px;
  text-align: center;
  padding: 8px 16px;
  line-height: 1.4;
}

.ann-content {
  margin: 0 auto;
  max-width: 1280px;
}

@media (max-width: 768px) {
  .announcement-bar {
    font-size: 11.5px;
    padding: 6px 12px;
  }
  .ann-content {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}
</style>
