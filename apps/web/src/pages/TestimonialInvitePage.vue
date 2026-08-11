<template>
  <q-page class="invite-page">
    <section class="invite-hero">
      <div class="ma-container invite-hero__inner">
        <p class="ma-eyebrow">Tu opinión importa</p>
        <h1 class="invite-hero__title">Evalúa tu experiencia</h1>
        <p class="invite-hero__lead">
          Comparte cómo fue trabajar con Modelarc. Tu testimonio nos ayuda a seguir
          construyendo con excelencia.
        </p>
      </div>
    </section>

    <section class="invite-body">
      <div class="ma-container">
        <div v-if="loading" class="invite-status">Cargando invitación…</div>

        <div v-else-if="done" class="invite-done">
          <q-icon name="check_circle" color="primary" size="48px" />
          <h2>¡Gracias{{ form.client_name ? `, ${form.client_name}` : '' }}!</h2>
          <p>Tu valoración fue registrada correctamente. Este enlace ya no está disponible.</p>
          <router-link class="ma-btn ma-btn--gold" to="/">Volver al inicio</router-link>
        </div>

        <div v-else-if="unavailable" class="invite-done">
          <q-icon name="link_off" color="grey-6" size="48px" />
          <h2>Enlace no disponible</h2>
          <p>{{ unavailableMessage }}</p>
          <router-link class="ma-btn ma-btn--outline-dark" to="/">Ir al inicio</router-link>
        </div>

        <div v-else class="invite-layout">
          <aside class="invite-card invite-card--side">
            <div class="invite-card__head">
              <q-icon name="apartment" size="22px" color="primary" />
              <h2>Detalle del proyecto</h2>
            </div>
            <dl class="invite-meta">
              <div>
                <dt>Cliente</dt>
                <dd>{{ invite?.client_name }}</dd>
              </div>
              <div>
                <dt>Proyecto</dt>
                <dd>{{ invite?.project_display_name || invite?.project_label || invite?.project?.title || '—' }}</dd>
              </div>
              <div v-if="invite?.project?.category">
                <dt>Categoría</dt>
                <dd class="invite-meta__cap">{{ invite.project.category }}</dd>
              </div>
              <div v-if="invite?.project?.location">
                <dt>Ubicación</dt>
                <dd>{{ invite.project.location }}</dd>
              </div>
              <div>
                <dt>Estado</dt>
                <dd><span class="invite-pill">Culminado</span></dd>
              </div>
            </dl>
          </aside>

          <form class="invite-card invite-card--form" @submit.prevent="submit">
            <div class="invite-card__head">
              <q-icon name="rate_review" size="22px" color="primary" />
              <h2>Cuéntanos tu experiencia</h2>
            </div>

            <label class="invite-label">Calificación *</label>
            <div class="invite-rating">
              <q-rating v-model="form.rating" size="42px" color="primary" icon="star_border" icon-selected="star" />
              <span class="invite-rating__hint">{{ form.rating }} de 5 estrellas</span>
            </div>

            <label class="invite-label" for="quote">Comentario / testimonio *</label>
            <textarea
              id="quote"
              v-model="form.quote"
              class="invite-textarea"
              rows="7"
              placeholder="Escribe aquí tu experiencia con Modelarc…"
              required
            />

            <label class="invite-check">
              <input v-model="form.allow_publish" type="checkbox" />
              <span>Autorizo a Modelarc a publicar mi testimonio en su sitio web</span>
            </label>

            <p v-if="error" class="invite-error">{{ error }}</p>

            <button class="invite-submit" type="submit" :disabled="submitting">
              {{ submitting ? 'Enviando…' : 'Enviar evaluación' }}
            </button>
          </form>
        </div>
      </div>
    </section>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import {
  fetchTestimonialInvite,
  submitTestimonialInvite,
  type TestimonialInvitePayload,
} from '@/services/testimonialInviteApi';

const route = useRoute();
const loading = ref(true);
const submitting = ref(false);
const done = ref(false);
const unavailable = ref(false);
const unavailableMessage = ref('Este enlace ya fue utilizado o no es válido.');
const error = ref('');
const invite = ref<TestimonialInvitePayload | null>(null);

const form = reactive({
  client_name: '',
  rating: 5,
  quote: '',
  allow_publish: true,
});

async function load() {
  loading.value = true;
  error.value = '';
  try {
    const token = String(route.params.token || '');
    const res = await fetchTestimonialInvite(token);
    if (res.status === 410) {
      unavailable.value = true;
      unavailableMessage.value =
        res.data.message || 'Este enlace ya fue utilizado. Gracias por tu participación.';
      invite.value = res.data;
      return;
    }
    invite.value = res.data;
    form.client_name = res.data.client_name || '';
  } catch {
    unavailable.value = true;
    unavailableMessage.value = 'No encontramos esta invitación. Verifica el enlace del correo.';
  } finally {
    loading.value = false;
  }
}

async function submit() {
  error.value = '';
  if (!form.rating || form.rating < 1) {
    error.value = 'Selecciona una calificación.';
    return;
  }
  if (form.quote.trim().length < 10) {
    error.value = 'El testimonio debe tener al menos 10 caracteres.';
    return;
  }

  submitting.value = true;
  try {
    await submitTestimonialInvite(String(route.params.token), {
      rating: form.rating,
      quote: form.quote.trim(),
      allow_publish: form.allow_publish,
      client_name: form.client_name || undefined,
    });
    done.value = true;
  } catch {
    error.value = 'No se pudo enviar la evaluación. Intenta de nuevo.';
  } finally {
    submitting.value = false;
  }
}

onMounted(load);
</script>

<style scoped lang="scss">
.invite-page {
  background: var(--ma-cream);
}

.invite-hero {
  background: var(--ma-charcoal);
  color: var(--ma-white);
  padding: clamp(3rem, 6vw, 4.5rem) 0;
  text-align: center;
}

.invite-hero__title {
  margin: 0.4rem 0 0.75rem;
  font-family: var(--ma-font-serif);
  font-size: clamp(2.2rem, 5vw, 3.4rem);
  font-weight: 400;
  line-height: 1.15;
}

.invite-hero__lead {
  margin: 0 auto;
  max-width: 34rem;
  color: rgba(255, 255, 255, 0.72);
  font-size: 1rem;
}

.invite-body {
  padding: clamp(2rem, 5vw, 3.5rem) 0 clamp(4rem, 8vw, 6rem);
}

.invite-layout {
  display: grid;
  grid-template-columns: minmax(240px, 0.85fr) minmax(0, 1.4fr);
  gap: 1.25rem;
  align-items: start;
}

.invite-card {
  background: var(--ma-white);
  border: 1px solid var(--ma-border);
  border-radius: 14px;
  padding: 1.35rem 1.4rem 1.5rem;
  box-shadow: 0 8px 28px rgba(17, 17, 17, 0.05);
}

.invite-card__head {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  margin-bottom: 1.15rem;

  h2 {
    margin: 0;
    font-family: var(--ma-font-serif);
    font-size: 1.45rem;
    font-weight: 400;
  }
}

.invite-meta {
  margin: 0;
  display: grid;
  gap: 1rem;

  dt {
    margin: 0 0 0.2rem;
    font-size: 0.7rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--ma-muted);
  }

  dd {
    margin: 0;
    font-weight: 600;
    color: var(--ma-charcoal);
  }
}

.invite-meta__cap {
  text-transform: capitalize;
}

.invite-pill {
  display: inline-flex;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  background: rgba(46, 125, 50, 0.12);
  color: #2e7d32;
  font-size: 0.78rem;
  font-weight: 700;
}

.invite-label {
  display: block;
  margin: 0 0 0.55rem;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--ma-charcoal);
}

.invite-rating {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.invite-rating__hint {
  color: var(--ma-muted);
  font-size: 0.85rem;
}

.invite-textarea {
  width: 100%;
  resize: vertical;
  min-height: 160px;
  padding: 0.85rem 1rem;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 10px;
  font: inherit;
  color: var(--ma-charcoal);
  background: #fafafa;
  margin-bottom: 1rem;

  &:focus {
    outline: 2px solid rgba(196, 164, 124, 0.45);
    border-color: var(--ma-gold);
    background: #fff;
  }
}

.invite-check {
  display: flex;
  align-items: flex-start;
  gap: 0.65rem;
  margin: 0.25rem 0 1.15rem;
  font-size: 0.9rem;
  color: #444;
  cursor: pointer;

  input {
    margin-top: 0.2rem;
  }
}

.invite-submit {
  width: 100%;
  min-height: 48px;
  border: 0;
  border-radius: 10px;
  background: var(--ma-charcoal);
  color: #fff;
  font-weight: 600;
  letter-spacing: 0.03em;
  cursor: pointer;
  transition: background 0.15s ease;

  &:hover:not(:disabled) {
    background: #000;
  }

  &:disabled {
    opacity: 0.65;
    cursor: wait;
  }
}

.invite-error {
  color: #c62828;
  font-size: 0.88rem;
  margin: 0 0 0.85rem;
}

.invite-status,
.invite-done {
  text-align: center;
  padding: 3rem 1rem;
  background: #fff;
  border-radius: 14px;
  border: 1px solid var(--ma-border);
}

.invite-done h2 {
  font-family: var(--ma-font-serif);
  margin: 0.75rem 0 0.5rem;
}

.invite-done p {
  color: var(--ma-muted);
  max-width: 28rem;
  margin: 0 auto 1.25rem;
}

@media (max-width: 860px) {
  .invite-layout {
    grid-template-columns: 1fr;
  }
}
</style>
