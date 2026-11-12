<template>
  <div class="credits in">
    <h1>مصادر الصور</h1>
    <p class="lead">
      صور الموتوسيكلات من Wikimedia Commons بترخيص Creative Commons، وباقي الصور من Unsplash.
      شكراً للمصوّرين.
    </p>

    <ul class="list">
      <li v-for="c in imageCredits" :key="c.page">
        <a :href="c.page" target="_blank" rel="noopener" class="title" dir="ltr">
          {{ c.title ? c.title.replace(/^File:/, '') : 'Unsplash photo' }}
        </a>
        <span class="meta" dir="ltr">
          <template v-if="c.author">{{ c.author }} · </template>
          <a v-if="c.licenseUrl" :href="c.licenseUrl" target="_blank" rel="noopener">{{ c.license }}</a>
          <template v-else>{{ c.license }}</template>
          · {{ c.source }}
        </span>
        <span v-if="c.changes" class="chg">{{ c.changes }}</span>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { imageCredits } from '@/config/imageCredits.js'
</script>

<style scoped>
.credits {
  padding-top: 28px;
  padding-bottom: 48px;
  max-width: 820px;
}

h1 {
  font-size: 28px;
  font-weight: 800;
  margin: 0 0 8px;
}

.lead {
  color: var(--mu);
  margin: 0 0 24px;
}

.list {
  list-style: none;
  padding: 0;
  margin: 0;
  border-top: 1px solid var(--ln);
}

.list li {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 4px 12px;
  padding: 12px 0;
  border-bottom: 1px solid var(--ln);
  font-size: 14px;
}

.title {
  font-weight: 700;
  color: var(--ink);
  overflow-wrap: anywhere;
}

.meta {
  color: var(--mu);
  font-size: 13px;
}

.meta a {
  color: inherit;
  text-decoration: underline;
}

.chg {
  color: var(--mu);
  font-size: 12px;
}
</style>
