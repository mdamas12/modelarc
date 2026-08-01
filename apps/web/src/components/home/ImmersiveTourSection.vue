<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import SectionHeader from '@/components/common/SectionHeader.vue';
import VirtualTourViewer from '@/components/virtual-tour/VirtualTourViewer.vue';
import { useHomeStore } from '@/stores/homeStore';

const home = useHomeStore();
const activeThumb = ref(0);
const playRequested = ref(false);
const viewerRef = ref<InstanceType<typeof VirtualTourViewer> | null>(null);

const tour = computed(() => home.featuredTour);

const initialSceneId = computed(() => tour.value?.scenes[0]?.id ?? '');

onMounted(() => {
  if (!home.loaded) void home.loadHome();
});

watch(tour, () => {
  activeThumb.value = 0;
  playRequested.value = false;
});

function selectThumb(index: number) {
  activeThumb.value = index;
  playRequested.value = true;
  const sceneId = tour.value?.scenes[index]?.id;
  if (sceneId) {
    void viewerRef.value?.switchScene?.(String(sceneId));
  }
}

function onSceneChange(sceneId: string) {
  const index = tour.value?.scenes.findIndex((s) => String(s.id) === String(sceneId)) ?? -1;
  if (index >= 0) activeThumb.value = index;
}
</script>

<template>
  <section class="ma-section ma-section--cream immersive">
    <div class="ma-container immersive__grid">
      <div class="immersive__copy">
        <SectionHeader
          eyebrow="Experiencia inmersiva"
          title="Recorre tus futuros espacios en 360°"
          lead="Antes de construir, explora cada ambiente con recorridos virtuales de alta fidelidad. Orientación, puntos interactivos y escenas conectadas."
        />
        <div v-if="tour?.scenes?.length" class="immersive__thumbs">
          <button
            v-for="(scene, i) in tour.scenes"
            :key="scene.id"
            type="button"
            class="immersive__thumb"
            :class="{ 'immersive__thumb--active': activeThumb === i }"
            :style="{ backgroundImage: `url(${scene.thumbnailUrl})` }"
            :aria-label="scene.name"
            @click="selectThumb(i)"
          />
        </div>
        <router-link
          :to="tour ? `/recorridos-360/${tour.slug}` : '/recorridos-360'"
          class="ma-btn ma-btn--gold"
        >
          Ver recorridos
        </router-link>
      </div>

      <div class="immersive__viewer">
        <div v-if="home.loading && !tour" class="immersive__status">Preparando tu experiencia…</div>
        <div v-else-if="!tour?.scenes?.length" class="immersive__status">
          Aún no hay un recorrido publicado para mostrar.
        </div>
        <VirtualTourViewer
          v-else
          :key="tour.id"
          ref="viewerRef"
          :scenes="tour.scenes"
          :initial-scene-id="initialSceneId"
          :autoplay="playRequested"
          :cover-image="tour.coverImage"
          height="520px"
          @scene-change="onSceneChange"
        />
      </div>
    </div>
  </section>
</template>

<style scoped lang="scss">
.immersive {
  &__grid {
    display: grid;
    grid-template-columns: 0.9fr 1.1fr;
    gap: 3rem;
    align-items: center;
  }

  &__thumbs {
    display: flex;
    gap: 0.65rem;
    margin: 0 0 1.75rem;
  }

  &__thumb {
    width: 4.5rem;
    height: 4.5rem;
    border: 1px solid transparent;
    background-size: cover;
    background-position: center;
    cursor: pointer;
    padding: 0;

    &--active {
      border-color: var(--ma-gold);
    }
  }

  &__viewer {
    min-height: 520px;
  }

  &__status {
    min-height: 520px;
    display: grid;
    place-items: center;
    color: var(--ma-muted);
    border: 1px solid rgba(26, 26, 26, 0.08);
    background: rgba(255, 255, 255, 0.5);
    padding: 2rem;
    text-align: center;
  }
}

@media (max-width: 960px) {
  .immersive__grid {
    grid-template-columns: 1fr;
  }
}
</style>
