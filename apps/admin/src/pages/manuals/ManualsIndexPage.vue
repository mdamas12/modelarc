<template>
  <q-page class="page-wrap">
    <div class="page-header">
      <div>
        <h1 class="page-title">Manuales</h1>
        <p class="page-subtitle">Guías paso a paso para usar el panel de administración</p>
      </div>
    </div>

    <div class="manual-grid">
      <router-link
        v-for="cat in manuals"
        :key="cat.id"
        :to="`/manuales/${cat.id}`"
        class="manual-card admin-card"
      >
        <div class="manual-card__icon">
          <q-icon :name="cat.icon" size="28px" color="primary" />
        </div>
        <div class="manual-card__body">
          <h2 class="manual-card__title">{{ cat.title }}</h2>
          <p class="manual-card__summary">{{ cat.summary }}</p>
          <span class="manual-card__meta">{{ cat.guides.length }} guías</span>
        </div>
        <q-icon name="chevron_right" color="grey-6" size="22px" />
      </router-link>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { manuals } from '@/manuals'
</script>

<style scoped lang="scss">
.manual-grid {
  display: grid;
  gap: 14px;
}

.manual-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 18px 20px;
  text-decoration: none;
  color: inherit;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;

  &:hover {
    border-color: var(--ma-gold);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
  }

  &__icon {
    flex: 0 0 auto;
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: rgba(196, 164, 124, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &__body {
    flex: 1;
    min-width: 0;
  }

  &__title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
  }

  &__summary {
    margin: 4px 0 0;
    color: var(--ma-muted);
    font-size: 0.9rem;
    line-height: 1.45;
  }

  &__meta {
    display: inline-block;
    margin-top: 8px;
    font-size: 0.75rem;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--ma-gold);
  }
}
</style>
