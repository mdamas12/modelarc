<template>
  <div class="not-found">
    <p class="ma-eyebrow">Error</p>
    <h1>404</h1>
    <p>La página que buscas no existe.</p>
    <router-link to="/" class="ma-btn ma-btn--gold">Volver al inicio</router-link>
  </div>
</template>

<style scoped lang="scss">
.not-found {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  background: var(--ma-charcoal-deep);
  color: var(--ma-cream);
  padding: 2rem;

  h1 {
    font-size: clamp(5rem, 18vw, 10rem);
    font-weight: 400;
    margin: 0 0 0.5rem;
    color: var(--ma-gold);
  }

  p {
    margin: 0 0 2rem;
    color: rgba(247, 244, 240, 0.65);
  }
}
</style>
