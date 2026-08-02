<script setup lang="ts">
withDefaults(
  defineProps<{
    visible?: boolean;
    message?: string;
    subtle?: boolean;
  }>(),
  {
    visible: false,
    message: 'Preparando Experiencia',
    subtle: false,
  },
);
</script>

<template>
  <Transition name="tour-fade">
    <div
      v-if="visible"
      class="tour-loading"
      :class="{ 'tour-loading--subtle': subtle }"
    >
      <div class="tour-loading__ring" />
      <p>{{ message }}</p>
    </div>
  </Transition>
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
  pointer-events: none;

  p {
    margin: 0;
    font-size: 0.8rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
  }

  &--subtle {
    background: rgba(17, 17, 17, 0.28);

    p {
      display: none;
    }

    .tour-loading__ring {
      width: 1.75rem;
      height: 1.75rem;
      border-width: 1.5px;
    }
  }

  &__ring {
    width: 2.5rem;
    height: 2.5rem;
    border: 2px solid rgba(196, 164, 124, 0.25);
    border-top-color: var(--ma-gold);
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
  }
}

.tour-fade-enter-active,
.tour-fade-leave-active {
  transition: opacity 0.2s ease;
}

.tour-fade-enter-from,
.tour-fade-leave-to {
  opacity: 0;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
