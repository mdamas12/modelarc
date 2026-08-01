<script setup lang="ts">
import { ref } from 'vue';

const year = new Date().getFullYear();

const links = [
  { label: 'Inicio', to: '/' },
  { label: 'Nosotros', to: '/nosotros' },
  { label: 'Proyectos', to: '/proyectos' },
  { label: 'Recorridos 360°', to: '/recorridos-360' },
  { label: 'Blog', to: '/blog' },
  { label: 'Contacto', to: '/contacto' },
];

const services = [
  'Diseño arquitectónico',
  'Construcción',
  'Remodelación',
  'Consultoría',
  'Recorridos virtuales',
];

const galleryImages = [
  'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=900&q=80',
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
];

const lightboxOpen = ref(false);
const lightboxIndex = ref(0);

function openLightbox(index: number) {
  lightboxIndex.value = index;
  lightboxOpen.value = true;
}

function prevImage() {
  lightboxIndex.value =
    (lightboxIndex.value - 1 + galleryImages.length) % galleryImages.length;
}

function nextImage() {
  lightboxIndex.value = (lightboxIndex.value + 1) % galleryImages.length;
}
</script>

<template>
  <footer class="site-footer">
    <div class="ma-container site-footer__grid">
      <div class="site-footer__col site-footer__brand-col">
        <router-link to="/" class="site-footer__brand">
          <img src="/brand/logo.svg" alt="Modelarc" class="site-footer__logo-img" />
        </router-link>
        <p class="site-footer__about">
          Arquitectura, construcción y remodelación de espacios que elevan la vida cotidiana.
          Diseño contemporáneo con rigor técnico y sensibilidad artesanal.
        </p>
      </div>

      <div class="site-footer__col site-footer__links-col">
        <h3 class="site-footer__title">Enlaces</h3>
        <ul>
          <li v-for="link in links" :key="link.to">
            <router-link :to="link.to">{{ link.label }}</router-link>
          </li>
        </ul>
      </div>

      <div class="site-footer__col site-footer__services-col">
        <h3 class="site-footer__title">Servicios</h3>
        <ul>
          <li v-for="s in services" :key="s">{{ s }}</li>
        </ul>
      </div>

      <div class="site-footer__col site-footer__contact-col">
        <h3 class="site-footer__title">Contacto</h3>
        <ul class="site-footer__contact">
          <li>
            <q-icon name="place" size="18px" aria-hidden="true" />
            <span>Puerto Ordaz, estado Bolívar, Venezuela.</span>
          </li>
          <li>
            <q-icon name="email" size="18px" aria-hidden="true" />
            <a href="mailto:info@modelarcve.com">info@modelarcve.com</a>
          </li>
          <li>
            <span class="site-footer__wa-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                <path
                  d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"
                />
              </svg>
            </span>
            <a href="https://wa.me/584249171058" target="_blank" rel="noopener noreferrer">
              (+58)-4249171058
            </a>
          </li>
        </ul>
      </div>

      <div class="site-footer__col site-footer__gallery-col">
        <div class="site-footer__ig-grid">
          <button
            v-for="(src, i) in galleryImages"
            :key="i"
            type="button"
            class="site-footer__ig-item"
            :style="{ backgroundImage: `url(${src})` }"
            :aria-label="`Ver imagen ${i + 1} de la galería`"
            @click="openLightbox(i)"
          />
        </div>
      </div>
    </div>

    <div class="site-footer__bottom ma-container">
      <div class="site-footer__copy">
        <p>© {{ year }} Modelarc. Todos los derechos reservados.</p>
        <p class="site-footer__credit">Desarrollado por MDevUp</p>
      </div>
      <div class="site-footer__social">
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
            width="18"
            height="18"
            fill="currentColor"
            aria-hidden="true"
          >
            <path
              d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"
            />
          </svg>
          <svg
            v-else-if="item.icon === 'facebook'"
            viewBox="0 0 24 24"
            width="18"
            height="18"
            fill="currentColor"
            aria-hidden="true"
          >
            <path
              d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.43c0-3.007 1.792-4.668 4.533-4.668 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"
            />
          </svg>
          <svg
            v-else
            viewBox="0 0 24 24"
            width="18"
            height="18"
            fill="currentColor"
            aria-hidden="true"
          >
            <path
              d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"
            />
          </svg>
        </a>
      </div>
    </div>

    <q-dialog v-model="lightboxOpen" maximized class="gallery-lightbox">
      <div class="gallery-lightbox__panel">
        <button
          type="button"
          class="gallery-lightbox__close"
          aria-label="Cerrar galería"
          @click="lightboxOpen = false"
        >
          <q-icon name="close" size="28px" />
        </button>
        <button
          type="button"
          class="gallery-lightbox__nav gallery-lightbox__nav--prev"
          aria-label="Imagen anterior"
          @click="prevImage"
        >
          <q-icon name="chevron_left" size="36px" />
        </button>
        <img
          :src="galleryImages[lightboxIndex]"
          :alt="`Galería ${lightboxIndex + 1}`"
          class="gallery-lightbox__img"
        />
        <button
          type="button"
          class="gallery-lightbox__nav gallery-lightbox__nav--next"
          aria-label="Imagen siguiente"
          @click="nextImage"
        >
          <q-icon name="chevron_right" size="36px" />
        </button>
        <p class="gallery-lightbox__count">
          {{ lightboxIndex + 1 }} / {{ galleryImages.length }}
        </p>
      </div>
    </q-dialog>
  </footer>
</template>

<style scoped lang="scss">
.site-footer {
  background: var(--ma-charcoal-deep);
  color: rgba(247, 244, 240, 0.78);
  padding-top: 4.5rem;
  border-top: 1px solid rgba(196, 164, 124, 0.18);

  &__grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1.1fr 1.2fr;
    gap: 2.5rem;
    padding-bottom: 3rem;
  }

  &__brand {
    display: inline-flex;
    align-items: center;
    margin: 0 0 1rem;
    line-height: 0;
  }

  &__logo-img {
    display: block;
    height: 56px;
    width: auto;
    max-width: 240px;
    object-fit: contain;
    object-position: left center;
  }

  &__about {
    font-size: 0.92rem;
    line-height: 1.7;
    max-width: 18rem;
    margin: 0;
  }

  &__title {
    font-family: var(--ma-font-sans);
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--ma-gold);
    margin-bottom: 1.25rem;
  }

  ul {
    list-style: none;
    margin: 0;
    padding: 0;

    li {
      margin-bottom: 0.65rem;
      font-size: 0.9rem;
    }

    a:hover {
      color: var(--ma-gold);
    }
  }

  &__contact li {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    line-height: 1.45;

    .q-icon,
    .site-footer__wa-icon {
      flex-shrink: 0;
      margin-top: 0.1rem;
      color: var(--ma-gold);
    }
  }

  &__wa-icon {
    display: inline-grid;
    place-items: center;
    width: 18px;
    height: 18px;
  }

  &__ig-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.4rem;
  }

  &__ig-item {
    aspect-ratio: 1;
    background-size: cover;
    background-position: center;
    opacity: 0.85;
    transition: opacity 0.2s ease;
    border: 0;
    padding: 0;
    cursor: pointer;
    border-radius: 2px;

    &:hover {
      opacity: 1;
    }
  }

  &__bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding-block: 1.35rem;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    font-size: 0.8rem;
  }

  &__copy {
    p {
      margin: 0;
    }
  }

  &__credit {
    margin-top: 0.35rem !important;
    color: rgba(247, 244, 240, 0.55);
  }

  &__social {
    display: flex;
    gap: 0.85rem;

    a {
      width: 2.1rem;
      height: 2.1rem;
      display: grid;
      place-items: center;
      color: var(--ma-gold);
      border: 1px solid rgba(196, 164, 124, 0.35);
      border-radius: 2px;
      transition: background 0.2s ease, color 0.2s ease;

      &:hover {
        background: var(--ma-gold);
        color: var(--ma-charcoal-deep);
      }
    }
  }
}

.gallery-lightbox__panel {
  position: relative;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.94);
  display: grid;
  place-items: center;
  padding: 3.5rem 4rem;
}

.gallery-lightbox__img {
  max-width: min(1100px, 100%);
  max-height: min(80vh, 100%);
  object-fit: contain;
  border-radius: 2px;
}

.gallery-lightbox__close,
.gallery-lightbox__nav {
  position: absolute;
  border: 0;
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
  cursor: pointer;
  border-radius: 2px;
  display: grid;
  place-items: center;
}

.gallery-lightbox__close {
  top: 1rem;
  right: 1rem;
  width: 2.75rem;
  height: 2.75rem;
}

.gallery-lightbox__nav {
  top: 50%;
  transform: translateY(-50%);
  width: 3rem;
  height: 3rem;

  &--prev {
    left: 1rem;
  }

  &--next {
    right: 1rem;
  }
}

.gallery-lightbox__count {
  position: absolute;
  bottom: 1.25rem;
  left: 50%;
  transform: translateX(-50%);
  margin: 0;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.85rem;
  letter-spacing: 0.08em;
}

@media (max-width: 1000px) {
  .site-footer__grid {
    grid-template-columns: 1fr 1fr;
  }

  .site-footer__gallery-col {
    grid-column: 1 / -1;
  }
}

@media (max-width: 700px) {
  .site-footer {
    padding-top: 3rem;

    &__grid {
      grid-template-columns: 1.15fr 0.85fr;
      gap: 1.25rem 1rem;
      padding-bottom: 2rem;
    }

    &__brand-col {
      grid-column: 1;
      grid-row: 1;
    }

    &__links-col {
      grid-column: 2;
      grid-row: 1;
    }

    &__services-col {
      grid-column: 1;
      grid-row: 2;
    }

    &__contact-col {
      grid-column: 2;
      grid-row: 2;
    }

    &__gallery-col {
      grid-column: 1 / -1;
      grid-row: 3;
    }

    &__logo-img {
      height: 44px;
    }

    &__about {
      font-size: 0.82rem;
      line-height: 1.55;
      max-width: none;
    }

    &__title {
      margin-bottom: 0.75rem;
    }

    ul li {
      margin-bottom: 0.45rem;
      font-size: 0.82rem;
    }

    &__bottom {
      flex-direction: column;
      text-align: center;
      gap: 0.85rem;
    }
  }

  .gallery-lightbox__panel {
    padding: 4rem 0.75rem 3rem;
  }

  .gallery-lightbox__nav {
    width: 2.5rem;
    height: 2.5rem;

    &--prev {
      left: 0.35rem;
    }

    &--next {
      right: 0.35rem;
    }
  }
}
</style>
