<script setup lang="ts">
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

const instagramPlaceholders = [
  'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=300&q=70',
  'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=300&q=70',
  'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=300&q=70',
  'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=300&q=70',
  'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=300&q=70',
  'https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?auto=format&fit=crop&w=300&q=70',
];
</script>

<template>
  <footer class="site-footer">
    <div class="ma-container site-footer__grid">
      <div class="site-footer__col">
        <router-link to="/" class="site-footer__brand">
          <img src="/brand/logo.svg" alt="Modelarc" class="site-footer__logo-img" />
        </router-link>
        <p class="site-footer__about">
          Arquitectura, construcción y remodelación de espacios que elevan la vida cotidiana.
          Diseño contemporáneo con rigor técnico y sensibilidad artesanal.
        </p>
      </div>

      <div class="site-footer__col">
        <h3 class="site-footer__title">Enlaces</h3>
        <ul>
          <li v-for="link in links" :key="link.to">
            <router-link :to="link.to">{{ link.label }}</router-link>
          </li>
        </ul>
      </div>

      <div class="site-footer__col">
        <h3 class="site-footer__title">Servicios</h3>
        <ul>
          <li v-for="s in services" :key="s">{{ s }}</li>
        </ul>
      </div>

      <div class="site-footer__col">
        <h3 class="site-footer__title">Contacto</h3>
        <ul class="site-footer__contact">
          <li>Santo Domingo, República Dominicana</li>
          <li><a href="mailto:hola@modelarc.com">hola@modelarc.com</a></li>
          <li><a href="tel:+18095550100">+1 (809) 555-0100</a></li>
        </ul>
      </div>

      <div class="site-footer__col site-footer__ig">
        <h3 class="site-footer__title">Instagram</h3>
        <div class="site-footer__ig-grid">
          <div
            v-for="(src, i) in instagramPlaceholders"
            :key="i"
            class="site-footer__ig-item"
            :style="{ backgroundImage: `url(${src})` }"
          />
        </div>
      </div>
    </div>

    <div class="site-footer__bottom ma-container">
      <p>© {{ year }} Modelarc. Todos los derechos reservados.</p>
      <div class="site-footer__social">
        <a href="#" aria-label="Instagram">IG</a>
        <a href="#" aria-label="LinkedIn">IN</a>
        <a href="#" aria-label="WhatsApp">WA</a>
      </div>
    </div>
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

  &__social {
    display: flex;
    gap: 1.25rem;

    a {
      font-size: 0.7rem;
      letter-spacing: 0.12em;
      color: var(--ma-gold);
    }
  }
}

@media (max-width: 1000px) {
  .site-footer__grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 600px) {
  .site-footer {
    &__grid {
      grid-template-columns: 1fr;
    }

    &__bottom {
      flex-direction: column;
      text-align: center;
    }
  }
}
</style>
