<script setup lang="ts">
import type { TourScene } from '@/types/models';

defineProps<{
  scenes: TourScene[];
  activeId: string | null;
}>();

const emit = defineEmits<{
  select: [sceneId: string];
}>();
</script>

<template>
  <div class="scene-selector" role="listbox" aria-label="Escenas del recorrido">
    <button
      v-for="scene in scenes"
      :key="scene.id"
      type="button"
      role="option"
      class="scene-selector__item"
      :class="{ 'scene-selector__item--active': activeId === scene.id }"
      :aria-selected="activeId === scene.id"
      @click="emit('select', scene.id)"
    >
      <span
        class="scene-selector__thumb"
        :style="{ backgroundImage: `url(${scene.thumbnailUrl})` }"
      />
      <span class="scene-selector__name">{{ scene.name }}</span>
    </button>
  </div>
</template>

<style scoped lang="scss">
.scene-selector {
  display: flex;
  gap: 0.65rem;
  overflow-x: auto;
  max-width: 100%;
  padding: 0.5rem;
  background: rgba(17, 17, 17, 0.85);
  border-top: 1px solid rgba(196, 164, 124, 0.25);
  box-sizing: border-box;
  -webkit-overflow-scrolling: touch;

  &__item {
    flex: 0 0 auto;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    width: 6.5rem;
    padding: 0;
    border: 1px solid transparent;
    background: transparent;
    cursor: pointer;
    color: rgba(247, 244, 240, 0.7);
    text-align: left;

    &--active {
      border-color: var(--ma-gold);
      color: var(--ma-gold);
    }
  }

  &__thumb {
    display: block;
    width: 100%;
    aspect-ratio: 16 / 10;
    background-size: cover;
    background-position: center;
  }

  &__name {
    font-size: 0.68rem;
    letter-spacing: 0.06em;
    padding: 0 0.15rem 0.25rem;
  }
}
</style>
