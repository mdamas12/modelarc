<script setup lang="ts">
import SiteFooter from '@/components/common/SiteFooter.vue';
import SiteHeader from '@/components/common/SiteHeader.vue';
</script>

<template>
  <q-layout view="lHh Lpr lFf" class="public-layout">
    <SiteHeader />

    <q-page-container>
      <router-view v-slot="{ Component }">
        <transition name="page" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </q-page-container>

    <SiteFooter />

    <aside class="floating-social" aria-label="Redes sociales">
      <a href="#" aria-label="Instagram">IG</a>
      <a href="#" aria-label="WhatsApp">WA</a>
    </aside>
  </q-layout>
</template>

<style scoped lang="scss">
.public-layout {
  background: var(--ma-white);
  min-height: 100vh;
}

.floating-social {
  position: fixed;
  right: 1rem;
  bottom: 1.5rem;
  z-index: 50;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;

  a {
    width: 2.5rem;
    height: 2.5rem;
    display: grid;
    place-items: center;
    background: var(--ma-charcoal);
    color: var(--ma-gold);
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    border: 1px solid rgba(196, 164, 124, 0.35);
    border-radius: 2px;
    transition: background 0.2s ease;

    &:hover {
      background: var(--ma-gold);
      color: var(--ma-charcoal);
    }
  }
}

@media (max-width: 700px) {
  .floating-social {
    display: none;
  }
}
</style>
