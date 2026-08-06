<template>
  <q-page class="page-wrap">
    <div v-if="!category || !guide" class="admin-card q-pa-xl text-center text-grey-7">
      Guía no encontrada.
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
            :label="category.title"
            class="q-mb-sm"
            :to="`/manuales/${category.id}`"
          />
          <h1 class="page-title">{{ guide.title }}</h1>
          <p class="page-subtitle">{{ guide.summary }}</p>
        </div>
      </div>

      <ol class="step-list">
        <li v-for="(step, index) in guide.steps" :key="index" class="step-card admin-card">
          <div class="step-card__badge">{{ index + 1 }}</div>
          <div class="step-card__content">
            <h2 class="step-card__title">{{ step.title }}</h2>
            <p class="step-card__body">{{ step.body }}</p>

            <div class="step-card__media">
              <img
                v-if="step.image && !broken[step.image]"
                :src="step.image"
                :alt="step.title"
                class="step-card__img"
                @error="onImgError(step.image)"
              />
              <div v-else class="step-card__placeholder">
                <q-icon name="image" size="32px" color="grey-5" />
                <span>Captura pendiente</span>
              </div>
            </div>
          </div>
        </li>
      </ol>
    </template>
  </q-page>
</template>

<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import { useRoute } from 'vue-router'
import { getManualCategory, getManualGuide } from '@/manuals'

const route = useRoute()
const broken = reactive<Record<string, boolean>>({})

const category = computed(() => getManualCategory(String(route.params.category || '')))
const guide = computed(() =>
  getManualGuide(String(route.params.category || ''), String(route.params.slug || '')),
)

watch(
  () => route.fullPath,
  () => {
    Object.keys(broken).forEach((key) => {
      delete broken[key]
    })
  },
)

function onImgError(src: string | null | undefined) {
  if (src) broken[src] = true
}
</script>

<style scoped lang="scss">
.step-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 16px;
}

.step-card {
  display: flex;
  gap: 16px;
  padding: 20px;

  &__badge {
    flex: 0 0 auto;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--ma-gold);
    color: #1a1a1a;
    font-weight: 700;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &__content {
    flex: 1;
    min-width: 0;
  }

  &__title {
    margin: 0 0 6px;
    font-size: 1.05rem;
    font-weight: 600;
  }

  &__body {
    margin: 0;
    color: var(--ma-muted);
    line-height: 1.55;
    font-size: 0.95rem;
  }

  &__media {
    margin-top: 14px;
  }

  &__img {
    display: block;
    width: 100%;
    max-width: 860px;
    border-radius: 8px;
    border: 1px solid var(--ma-border);
    background: #fff;
  }

  &__placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 160px;
    max-width: 860px;
    border-radius: 8px;
    border: 1px dashed var(--ma-border);
    background: #fafafa;
    color: var(--ma-muted);
    font-size: 0.85rem;
  }
}

@media (max-width: 600px) {
  .step-card {
    flex-direction: column;
  }
}
</style>
