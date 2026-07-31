<script setup lang="ts">
import FeaturedProjects from '@/components/home/FeaturedProjects.vue';
import HeroSection from '@/components/home/HeroSection.vue';
import ImmersiveTourSection from '@/components/home/ImmersiveTourSection.vue';
import ProcessSection from '@/components/home/ProcessSection.vue';
import ServicesSection from '@/components/home/ServicesSection.vue';
import TestimonialsSection from '@/components/home/TestimonialsSection.vue';
</script>

<template>
  <q-page class="home-page">
    <HeroSection />
    <ServicesSection />
    <FeaturedProjects />
    <ImmersiveTourSection />
    <ProcessSection />
    <TestimonialsSection />

    <section class="ma-section ma-section--cream cta-band">
      <div class="ma-container cta-band__inner">
        <div>
          <p class="ma-eyebrow">Empecemos</p>
          <h2 class="ma-heading">¿Listo para transformar tu espacio?</h2>
        </div>
        <router-link to="/contacto" class="ma-btn ma-btn--gold">Solicitar presupuesto</router-link>
      </div>
    </section>
  </q-page>
</template>

<style scoped lang="scss">
.cta-band {
  &__inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
    flex-wrap: wrap;
  }
}
</style>
