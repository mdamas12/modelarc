<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { useQuasar } from 'quasar';
import { City, Country, State } from 'country-state-city';
import SectionHeader from '@/components/common/SectionHeader.vue';
import { submitContact } from '@/services/contactApi';
import type { ContactPayload } from '@/types/models';

const $q = useQuasar();
const sending = ref(false);

const form = reactive<ContactPayload>({
  name: '',
  email: '',
  phone: '',
  country: '',
  state: '',
  city: '',
  service: 'Diseño arquitectónico',
  message: '',
});

const countryCode = ref('');
const stateCode = ref('');

const services = [
  'Diseño arquitectónico',
  'Construcción',
  'Remodelación',
  'Recorrido 360°',
  'Otro',
];

const socialLinks = [
  {
    label: 'Instagram',
    href: 'https://www.instagram.com/modelarc_/',
    icon: 'instagram',
  },
  {
    label: 'Facebook',
    href: 'https://www.facebook.com/share/1H3KpGgge4/?mibextid=wwXIfr',
    icon: 'facebook',
  },
  {
    label: 'WhatsApp',
    href: 'https://wa.me/584249171058',
    icon: 'whatsapp',
  },
  {
    label: 'Gmail',
    href: 'https://mail.google.com/mail/?view=cm&fs=1&to=modelarca@gmail.com',
    icon: 'gmail',
  },
];

const countries = Country.getAllCountries().map((c) => ({
  code: c.isoCode,
  name: c.name,
}));

const states = computed(() => {
  if (!countryCode.value) return [];
  return State.getStatesOfCountry(countryCode.value).map((s) => ({
    code: s.isoCode,
    name: s.name,
  }));
});

const cities = computed(() => {
  if (!countryCode.value || !stateCode.value) return [];
  return City.getCitiesOfState(countryCode.value, stateCode.value).map((c) => c.name);
});

const venezuela = countries.find((c) => c.code === 'VE');
if (venezuela) {
  countryCode.value = venezuela.code;
  form.country = venezuela.name;
}

watch(countryCode, (code) => {
  const selected = countries.find((c) => c.code === code);
  form.country = selected?.name || '';
  stateCode.value = '';
  form.state = '';
  form.city = '';
});

watch(stateCode, (code) => {
  const selected = states.value.find((s) => s.code === code);
  form.state = selected?.name || '';
  form.city = '';
});

async function onSubmit() {
  if (!form.name || !form.email || !form.message || !form.country || !form.state || !form.city) {
    $q.notify({ type: 'warning', message: 'Completa nombre, email, ubicación y mensaje.' });
    return;
  }
  sending.value = true;
  try {
    const result = await submitContact({ ...form });
    $q.notify({ type: 'positive', message: result.message, color: 'primary', textColor: 'dark' });
    form.name = '';
    form.email = '';
    form.phone = '';
    form.city = '';
    form.message = '';
    stateCode.value = '';
    form.state = '';
    if (venezuela) {
      countryCode.value = venezuela.code;
      form.country = venezuela.name;
    } else {
      countryCode.value = '';
      form.country = '';
    }
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
          <label>
            País
            <select v-model="countryCode" required>
              <option disabled value="">Selecciona un país</option>
              <option v-for="c in countries" :key="c.code" :value="c.code">{{ c.name }}</option>
            </select>
          </label>
          <label>
            Estado
            <select v-model="stateCode" required :disabled="!countryCode || !states.length">
              <option disabled value="">Selecciona un estado</option>
              <option v-for="s in states" :key="s.code" :value="s.code">{{ s.name }}</option>
            </select>
          </label>
          <label>
            Ciudad
            <select
              v-if="cities.length"
              v-model="form.city"
              required
              :disabled="!stateCode"
            >
              <option disabled value="">Selecciona una ciudad</option>
              <option v-for="city in cities" :key="city" :value="city">{{ city }}</option>
            </select>
            <input
              v-else
              v-model="form.city"
              type="text"
              required
              :disabled="!stateCode"
              placeholder="Escribe la ciudad"
            />
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
          <h3>Oficina</h3>
          <p class="contact-aside__row">
            <q-icon name="place" size="20px" aria-hidden="true" />
            <span>Puerto Ordaz, estado Bolívar, Venezuela.</span>
          </p>
          <p class="contact-aside__row">
            <q-icon name="email" size="20px" aria-hidden="true" />
            <a href="mailto:modelarca@gmail.com">modelarca@gmail.com</a>
          </p>
          <p class="contact-aside__row">
            <q-icon name="phone" size="20px" aria-hidden="true" />
            <a href="https://wa.me/584249171058" target="_blank" rel="noopener noreferrer">
              (+58)-4249171058
            </a>
          </p>
          <div class="ma-divider" />
          <p class="contact-aside__row contact-aside__note">
            <q-icon name="schedule" size="20px" aria-hidden="true" />
            <span>Lunes - Viernes, 08:00 AM - 16:30 PM</span>
          </p>

          <div class="contact-aside__social-block">
            <p class="contact-aside__social-title">
              Contáctanos a través de nuestras redes sociales:
            </p>
            <div class="contact-aside__social">
              <a
                v-for="item in socialLinks"
                :key="item.label"
                :href="item.href"
                :aria-label="item.label"
                target="_blank"
                rel="noopener noreferrer"
              >
              <svg
                v-if="item.icon === 'instagram'"
                viewBox="0 0 24 24"
                aria-hidden="true"
              >
                <path
                  fill="currentColor"
                  d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm0 2a3 3 0 00-3 3v10a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H7zm5 3.5A4.5 4.5 0 1112 16a4.5 4.5 0 010-9zm0 2A2.5 2.5 0 1014.5 12 2.5 2.5 0 0012 7.5zm5.25-.75a1 1 0 11-1 1 1 1 0 011-1z"
                />
              </svg>
              <svg
                v-else-if="item.icon === 'facebook'"
                viewBox="0 0 24 24"
                aria-hidden="true"
              >
                <path
                  fill="currentColor"
                  d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h3l1-3h-4v-2c0-.6.4-1 1-1z"
                />
              </svg>
              <svg
                v-else-if="item.icon === 'whatsapp'"
                viewBox="0 0 24 24"
                aria-hidden="true"
              >
                <path
                  fill="currentColor"
                  d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.89.49 3.73 1.42 5.36L2 22l4.89-1.52a9.86 9.86 0 004.95 1.26h.01c5.46 0 9.91-4.45 9.91-9.91C21.76 6.45 17.5 2 12.04 2zm5.76 14.05c-.24.68-1.4 1.25-1.94 1.33-.5.07-1.13.1-1.82-.11-.42-.13-.96-.31-1.65-.61-2.9-1.25-4.79-4.17-4.93-4.36-.14-.19-1.15-1.53-1.15-2.92 0-1.39.73-2.07.99-2.36.26-.29.57-.36.76-.36h.55c.18 0 .42-.07.66.5.24.58.82 2 .89 2.14.07.14.12.31.02.5-.1.19-.14.31-.28.48-.14.17-.3.38-.42.51-.14.14-.28.29-.12.57.16.28.71 1.17 1.52 1.9 1.05.94 1.93 1.23 2.21 1.37.28.14.44.12.6-.07.16-.19.7-.81.89-1.09.19-.28.38-.23.64-.14.26.1 1.66.78 1.95.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"
                />
              </svg>
              <svg
                v-else-if="item.icon === 'gmail'"
                viewBox="0 0 24 24"
                aria-hidden="true"
              >
                <path
                  fill="currentColor"
                  d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 010 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z"
                />
              </svg>
              </a>
            </div>
          </div>
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

    &:disabled {
      opacity: 0.55;
      cursor: not-allowed;
      background: #f3f1ee;
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
    margin: 0 0 0.85rem;
    color: var(--ma-muted);
  }

  a:hover {
    color: var(--ma-gold-dark);
  }

  &__row {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    line-height: 1.45;

    .q-icon {
      flex-shrink: 0;
      margin-top: 0.1rem;
      color: var(--ma-gold);
    }
  }

  &__note {
    font-size: 0.9rem;
  }

  &__social-block {
    margin-top: 1.35rem;
  }

  &__social-title {
    margin: 0 0 0.75rem;
    color: var(--ma-charcoal);
    font-size: 0.92rem;
    line-height: 1.45;
  }

  &__social {
    display: flex;
    flex-wrap: wrap;
    gap: 0.85rem;

    a {
      width: 2.1rem;
      height: 2.1rem;
      display: grid;
      place-items: center;
      color: var(--ma-gold-dark);
      border: 1px solid rgba(196, 164, 124, 0.45);
      border-radius: 2px;
      transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;

      &:hover {
        background: var(--ma-gold);
        border-color: var(--ma-gold);
        color: var(--ma-charcoal-deep);
      }
    }
  }
}

@media (max-width: 800px) {
  .contact-grid,
  .contact-form {
    grid-template-columns: 1fr;
  }
}
</style>
