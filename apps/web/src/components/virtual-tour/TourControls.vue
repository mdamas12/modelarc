<script setup lang="ts">
const emit = defineEmits<{
  fullscreen: [];
  reset: [];
}>();
</script>

<template>
  <div class="tour-controls">
    <button type="button" class="tour-controls__btn" title="Centrar vista" @click="emit('reset')">
      ◎
    </button>
    <button type="button" class="tour-controls__btn" title="Pantalla completa" @click="emit('fullscreen')">
      ⛶
    </button>
  </div>
</template>

<style scoped lang="scss">
.tour-controls {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 4;
  display: flex;
  gap: 0.4rem;

  &__btn {
    width: 2.4rem;
    height: 2.4rem;
    border: 1px solid rgba(196, 164, 124, 0.45);
    background: rgba(17, 17, 17, 0.75);
    color: var(--ma-gold);
    cursor: pointer;
    font-size: 1rem;
    line-height: 1;
    transition: background 0.2s ease;

    &:hover {
      background: var(--ma-gold);
      color: var(--ma-charcoal);
    }
  }
}
</style>
