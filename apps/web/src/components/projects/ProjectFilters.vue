<script setup lang="ts">
defineProps<{
  categories: string[];
  modelValue: string;
  search: string;
}>();

const emit = defineEmits<{
  'update:modelValue': [value: string];
  'update:search': [value: string];
}>();
</script>

<template>
  <div class="project-filters">
    <div class="project-filters__cats">
      <button
        v-for="cat in categories"
        :key="cat"
        type="button"
        class="project-filters__chip"
        :class="{ 'project-filters__chip--active': modelValue === cat }"
        @click="emit('update:modelValue', cat)"
      >
        {{ cat }}
      </button>
    </div>
    <label class="project-filters__search">
      <span class="sr-only">Buscar</span>
      <input
        type="search"
        placeholder="Buscar proyecto..."
        :value="search"
        @input="emit('update:search', ($event.target as HTMLInputElement).value)"
      />
    </label>
  </div>
</template>

<style scoped lang="scss">
.project-filters {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
  margin-bottom: 2.5rem;

  &__cats {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  &__chip {
    border: 1px solid rgba(26, 26, 26, 0.18);
    background: transparent;
    padding: 0.55rem 1rem;
    font-family: var(--ma-font-sans);
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    cursor: pointer;
    color: var(--ma-charcoal);
    transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;

    &--active,
    &:hover {
      border-color: var(--ma-gold);
      color: var(--ma-gold-dark);
      background: rgba(196, 164, 124, 0.08);
    }
  }

  &__search input {
    min-width: 14rem;
    padding: 0.7rem 1rem;
    border: 1px solid rgba(26, 26, 26, 0.18);
    font-family: var(--ma-font-sans);
    font-size: 0.9rem;
    background: var(--ma-white);
    outline: none;

    &:focus {
      border-color: var(--ma-gold);
    }
  }
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
}
</style>
