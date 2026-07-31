<script setup lang="ts">
defineProps<{
  eyebrow?: string;
  title: string;
  lead?: string;
  align?: 'left' | 'center';
  dark?: boolean;
}>();
</script>

<template>
  <header
    class="section-header"
    :class="[
      `section-header--${align || 'left'}`,
      { 'section-header--dark': dark },
    ]"
  >
    <p v-if="eyebrow" class="ma-eyebrow">{{ eyebrow }}</p>
    <h2 class="ma-heading">{{ title }}</h2>
    <div class="ma-divider" :class="{ 'ma-divider--center': align === 'center' }" />
    <p v-if="lead" class="ma-lead">{{ lead }}</p>
  </header>
</template>

<style scoped lang="scss">
.section-header {
  margin-bottom: 3rem;
  max-width: 40rem;

  &--center {
    text-align: center;
    margin-inline: auto;

    .ma-lead {
      margin-inline: auto;
    }
  }

  &--dark {
    color: var(--ma-cream);
  }
}

.ma-divider--center {
  margin-inline: auto;
}
</style>
