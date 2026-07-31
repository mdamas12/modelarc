<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useQuasar } from 'quasar';
import SectionHeader from '@/components/common/SectionHeader.vue';
import { submitContact } from '@/services/contactApi';
import type { ContactPayload } from '@/types/models';

const $q = useQuasar();
const sending = ref(false);

const form = reactive<ContactPayload>({
  name: '',
  email: '',
  phone: '',
  service: 'Diseño arquitectónico',
  message: '',
});

const services = [
  'Diseño arquitectónico',
  'Construcción',
  'Remodelación',
  'Recorrido 360°',
  'Otro',
];

async function onSubmit() {
  if (!form.name || !form.email || !form.message) {
    $q.notify({ type: 'warning', message: 'Completa nombre, email y mensaje.' });
    return;
  }
  sending.value = true;
  try {
    const result = await submitContact({ ...form });
    $q.notify({ type: 'positive', message: result.message, color: 'primary', textColor: 'dark' });
    form.name = '';
    form.email = '';
    form.phone = '';
    form.message = '';
  } catch {
    $q.notify({ type: 'negative', message: 'No se pudo enviar el mensaje. Intenta de nuevo.' });
  } finally {
    sending.value = false;
  }
}
</script>

<template>
  <q-page>
    <section class="page-hero ma-section ma-section--dark">
      <div class="ma-container">
        <SectionHeader
          eyebrow="Contacto"
          title="Solicita tu presupuesto"
          lead="Cuéntanos sobre tu proyecto. Te responderemos a la brevedad."
          dark
        />
      </div>
    </section>

    <section class="ma-section ma-section--light">
      <div class="ma-container contact-grid">
        <form class="contact-form" @submit.prevent="onSubmit">
          <label>
            Nombre
            <input v-model="form.name" type="text" required autocomplete="name" />
          </label>
          <label>
            Email
            <input v-model="form.email" type="email" required autocomplete="email" />
          </label>
          <label>
            Teléfono
            <input v-model="form.phone" type="tel" autocomplete="tel" />
          </label>
          <label>
            Servicio
            <select v-model="form.service">
              <option v-for="s in services" :key="s" :value="s">{{ s }}</option>
            </select>
          </label>
          <label class="contact-form__full">
            Mensaje
            <textarea v-model="form.message" rows="5" required />
          </label>
          <button type="submit" class="ma-btn ma-btn--gold" :disabled="sending">
            {{ sending ? 'Enviando…' : 'Enviar mensaje' }}
          </button>
        </form>

        <aside class="contact-aside">
          <h3>Estudio</h3>
          <p>Santo Domingo, República Dominicana</p>
          <p><a href="mailto:hola@modelarc.com">hola@modelarc.com</a></p>
          <p><a href="tel:+18095550100">+1 (809) 555-0100</a></p>
          <div class="ma-divider" />
          <p class="contact-aside__note">
            Horario: Lun–Vie 9:00–18:00 · Visitas a obra con cita previa.
          </p>
        </aside>
      </div>
    </section>
  </q-page>
</template>

<style scoped lang="scss">
.page-hero {
  padding-top: 5rem;
  padding-bottom: 3rem;

  :deep(.section-header) {
    margin-bottom: 0;
  }
}

.contact-grid {
  display: grid;
  grid-template-columns: 1.4fr 0.8fr;
  gap: 3rem;
}

.contact-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;

  label {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--ma-muted);
  }

  &__full {
    grid-column: 1 / -1;
  }

  input,
  select,
  textarea {
    font-family: var(--ma-font-sans);
    font-size: 1rem;
    font-weight: 400;
    letter-spacing: normal;
    text-transform: none;
    color: var(--ma-charcoal);
    border: 1px solid rgba(26, 26, 26, 0.18);
    padding: 0.85rem 1rem;
    background: var(--ma-white);
    outline: none;

    &:focus {
      border-color: var(--ma-gold);
    }
  }

  button {
    grid-column: 1 / -1;
    justify-self: start;
    margin-top: 0.5rem;

    &:disabled {
      opacity: 0.6;
      cursor: wait;
    }
  }
}

.contact-aside {
  border-top: 1px solid var(--ma-gold);
  padding-top: 1.5rem;

  h3 {
    font-size: 1.6rem;
    margin-bottom: 1rem;
  }

  p {
    margin: 0 0 0.5rem;
    color: var(--ma-muted);
  }

  a:hover {
    color: var(--ma-gold-dark);
  }

  &__note {
    font-size: 0.9rem;
  }
}

@media (max-width: 800px) {
  .contact-grid,
  .contact-form {
    grid-template-columns: 1fr;
  }
}
</style>
