<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { useConsentStore } from '@/stores/consentStore';

const consent = useConsentStore();
const showPrefs = ref(false);

const draft = reactive({
  analytics: false,
  marketing: false,
});

watch(
  () => consent.showBanner,
  (open) => {
    if (open) {
      draft.analytics = consent.preferences.analytics;
      draft.marketing = consent.preferences.marketing;
      showPrefs.value = false;
    }
  },
  { immediate: true },
);

function acceptAll() {
  consent.acceptAll();
}

function rejectNonEssential() {
  consent.rejectNonEssential();
}

function savePrefs() {
  consent.savePreferences({
    analytics: draft.analytics,
    marketing: draft.marketing,
  });
  showPrefs.value = false;
}
</script>

<template>
  <div
    v-if="consent.showBanner"
    class="consent-banner"
    role="dialog"
    aria-labelledby="consent-banner-title"
    aria-describedby="consent-banner-desc"
  >
    <div class="consent-banner__inner">
      <div class="consent-banner__copy">
        <h2 id="consent-banner-title" class="consent-banner__title">Privacidad y cookies</h2>
        <p id="consent-banner-desc" class="consent-banner__text">
          Usamos cookies esenciales para el sitio. Con tu permiso también podemos medir audiencias
          (analítica) y optimizar campañas (marketing, incluido Meta Pixel). Puedes aceptar,
          rechazar lo no esencial o configurar preferencias.
        </p>
      </div>

      <div v-if="!showPrefs" class="consent-banner__actions">
        <button type="button" class="ma-btn ma-btn--gold" @click="acceptAll">
          Aceptar todas
        </button>
        <button type="button" class="consent-banner__btn-secondary" @click="rejectNonEssential">
          Rechazar no esenciales
        </button>
        <button type="button" class="consent-banner__btn-link" @click="showPrefs = true">
          Configurar
        </button>
      </div>

      <div v-else class="consent-banner__prefs">
        <label class="consent-banner__check">
          <input type="checkbox" checked disabled />
          Esenciales (siempre activas)
        </label>
        <label class="consent-banner__check">
          <input v-model="draft.analytics" type="checkbox" />
          Analítica
        </label>
        <label class="consent-banner__check">
          <input v-model="draft.marketing" type="checkbox" />
          Marketing (Meta Pixel)
        </label>
        <div class="consent-banner__actions">
          <button type="button" class="ma-btn ma-btn--gold" @click="savePrefs">
            Guardar preferencias
          </button>
          <button type="button" class="consent-banner__btn-link" @click="showPrefs = false">
            Volver
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.consent-banner {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 80;
  padding: 1rem;
  background: rgba(28, 28, 28, 0.96);
  color: #f5f2ec;
  border-top: 1px solid rgba(196, 164, 124, 0.35);
}

.consent-banner__inner {
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  gap: 1rem;
}

.consent-banner__title {
  margin: 0 0 0.35rem;
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--ma-gold, #c4a47c);
}

.consent-banner__text {
  margin: 0;
  font-size: 0.9rem;
  line-height: 1.45;
  color: rgba(245, 242, 236, 0.88);
}

.consent-banner__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.65rem;
  align-items: center;
}

.consent-banner__btn-secondary {
  background: transparent;
  color: #f5f2ec;
  border: 1px solid rgba(245, 242, 236, 0.35);
  padding: 0.55rem 0.9rem;
  cursor: pointer;
  font: inherit;
}

.consent-banner__btn-link {
  background: none;
  border: none;
  color: var(--ma-gold, #c4a47c);
  text-decoration: underline;
  cursor: pointer;
  font: inherit;
  padding: 0.35rem 0.25rem;
}

.consent-banner__prefs {
  display: grid;
  gap: 0.65rem;
}

.consent-banner__check {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  font-size: 0.9rem;
}

@media (min-width: 860px) {
  .consent-banner__inner {
    grid-template-columns: 1.4fr 1fr;
    align-items: center;
  }
}
</style>
