<template>
  <q-page class="page-wrap">
    <div v-if="!category" class="admin-card q-pa-xl text-center text-grey-7">
      Categoría no encontrada.
      <div class="q-mt-md">
        <q-btn flat no-caps color="primary" label="Volver a Manuales" to="/manuales" />
      </div>
    </div>

    <template v-else>
      <div class="page-header">
        <div>
          <q-btn
            flat
            dense
            no-caps
            color="primary"
            icon="arrow_back"
            label="Manuales"
            class="q-mb-sm"
            to="/manuales"
          />
          <h1 class="page-title">{{ category.title }}</h1>
          <p class="page-subtitle">{{ category.summary }}</p>
        </div>
      </div>

      <div class="guide-list">
        <router-link
          v-for="guide in category.guides"
          :key="guide.slug"
          :to="`/manuales/${category.id}/${guide.slug}`"
          class="guide-card admin-card"
        >
          <div class="guide-card__body">
            <h2 class="guide-card__title">{{ guide.title }}</h2>
            <p class="guide-card__summary">{{ guide.summary }}</p>
            <span class="guide-card__meta">{{ guide.steps.length }} pasos</span>
          </div>
          <q-icon name="chevron_right" color="grey-6" size="22px" />
        </router-link>
      </div>
    </template>
  </q-page>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { getManualCategory } from '@/manuals'

const route = useRoute()
const category = computed(() => getManualCategory(String(route.params.category || '')))
</script>

<style scoped lang="scss">
.guide-list {
  display: grid;
  gap: 12px;
}

.guide-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  text-decoration: none;
  color: inherit;
  transition: border-color 0.15s ease;

  &:hover {
    border-color: var(--ma-gold);
  }

  &__body {
    flex: 1;
    min-width: 0;
  }

  &__title {
    margin: 0;
    font-size: 1rem;
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
