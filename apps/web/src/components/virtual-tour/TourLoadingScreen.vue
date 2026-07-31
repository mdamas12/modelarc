<script setup lang="ts">
defineProps<{
  visible?: boolean;
  message?: string;
}>();
</script>

<template>
  <div v-if="visible !== false" class="tour-loading">
    <div class="tour-loading__ring" />
    <p>{{ message || 'Cargando recorrido 360°…' }}</p>
  </div>
</template>

<style scoped lang="scss">
.tour-loading {
  position: absolute;
  inset: 0;
  z-index: 5;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  background: rgba(17, 17, 17, 0.88);
  color: var(--ma-cream);

  p {
    margin: 0;
    font-size: 0.8rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
  }

  &__ring {
    width: 2.5rem;
    height: 2.5rem;
    border: 2px solid rgba(196, 164, 124, 0.25);
    border-top-color: var(--ma-gold);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
