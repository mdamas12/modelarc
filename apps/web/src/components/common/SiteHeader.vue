<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';

const menuOpen = ref(false);
const route = useRoute();

const links = [
  { label: 'Inicio', to: '/' },
  { label: 'Nosotros', to: '/nosotros' },
  { label: 'Servicios', to: '/servicios' },
  { label: 'Proyectos', to: '/proyectos' },
  { label: 'Recorridos 360°', to: '/recorridos-360' },
  { label: 'Contacto', to: '/contacto' },
];

function closeMenu() {
  menuOpen.value = false;
}

function isActive(to: string) {
  if (to === '/') return route.path === '/';
  return route.path === to || route.path.startsWith(`${to}/`);
}
</script>

<template>
  <header class="site-header" :class="{ 'site-header--open': menuOpen }">
    <div class="site-header__inner ma-container">
      <router-link to="/" class="site-header__logo" @click="closeMenu">
        <img src="/brand/logo.svg" alt="Modelarc" class="site-header__logo-img" />
      </router-link>

      <nav class="site-header__nav" :class="{ 'site-header__nav--open': menuOpen }" aria-label="Principal">
        <router-link
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="site-header__link"
          :class="{ 'site-header__link--active': isActive(link.to) }"
          @click="closeMenu"
        >
          {{ link.label }}
        </router-link>
      </nav>

      <div class="site-header__actions">
        <router-link to="/contacto" class="ma-btn ma-btn--gold site-header__cta" @click="closeMenu">
          Solicitar presupuesto
        </router-link>
        <button
          type="button"
          class="site-header__burger"
          :aria-expanded="menuOpen"
          aria-label="Menú"
          @click="menuOpen = !menuOpen"
        >
          <span /><span /><span />
        </button>
      </div>
    </div>
  </header>
</template>

<style scoped lang="scss">
.site-header {
  position: sticky;
  top: 0;
  z-index: 100;
  background: rgba(17, 17, 17, 0.96);
  box-shadow: 0 1px 0 rgba(196, 164, 124, 0.15);
  backdrop-filter: blur(10px);

  &__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    min-height: 4.5rem;
  }

  &__logo {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    line-height: 0;
  }

  &__logo-img {
    display: block;
    height: 40px;
    width: auto;
    max-width: min(260px, 42vw);
    object-fit: contain;
    object-position: left center;
  }

  &__nav {
    display: flex;
    align-items: center;
    gap: 1.35rem;
    flex: 1;
    justify-content: center;
  }

  &__link {
    position: relative;
    padding: 0.35rem 0 0.55rem;
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(247, 244, 240, 0.78);
    white-space: nowrap;
    transition: color 0.2s ease;

    &::after {
      content: '';
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      height: 2px;
      background: var(--ma-gold);
      transform: scaleX(0);
      transform-origin: center;
      transition: transform 0.2s ease;
    }

    &:hover {
      color: var(--ma-gold);
    }

    &--active {
      color: var(--ma-gold);
      font-weight: 600;

      &::after {
        transform: scaleX(1);
      }
    }
  }

  &__actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
  }

  &__cta {
    padding: 0.7rem 1.15rem;
    font-size: 0.68rem;
  }

  &__burger {
    display: none;
    width: 2.5rem;
    height: 2.5rem;
    border: 0;
    background: transparent;
    cursor: pointer;
    padding: 0.5rem;

    span {
      display: block;
      height: 1px;
      margin: 5px 0;
      background: var(--ma-cream);
      transition: transform 0.2s ease, opacity 0.2s ease;
    }
  }
}

@media (max-width: 1100px) {
  .site-header {
    &__nav {
      position: fixed;
      inset: 4.5rem 0 auto 0;
      flex-direction: column;
      align-items: stretch;
      gap: 0;
      padding: 1rem 1.25rem 1.5rem;
      background: rgba(17, 17, 17, 0.98);
      transform: translateY(-120%);
      opacity: 0;
      pointer-events: none;
      transition: transform 0.25s ease, opacity 0.25s ease;

      &--open {
        transform: translateY(0);
        opacity: 1;
        pointer-events: auto;
      }
    }

    &__link {
      padding: 0.9rem 0;
      border-bottom: 1px solid rgba(196, 164, 124, 0.12);

      &::after {
        left: 0;
        right: auto;
        width: 1.5rem;
        bottom: 0.55rem;
      }
    }

    &__cta {
      display: none;
    }

    &__burger {
      display: block;
    }

    &--open &__burger span:nth-child(1) {
      transform: translateY(6px) rotate(45deg);
    }

    &--open &__burger span:nth-child(2) {
      opacity: 0;
    }

    &--open &__burger span:nth-child(3) {
      transform: translateY(-6px) rotate(-45deg);
    }
  }
}
</style>
