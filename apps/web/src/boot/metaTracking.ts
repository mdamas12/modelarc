import { defineBoot } from '#q-app';
import { watch } from 'vue';
import { useConsentStore } from '@/stores/consentStore';
import {
  getMetaPixelConfig,
  setMarketingConsent,
  trackPageView,
} from '@/services/metaPixel';

/**
 * Wire Meta Pixel to marketing consent + Vue Router (public website only).
 * Pixel script loads only after marketing consent.
 */
export default defineBoot(({ router }) => {
  const config = getMetaPixelConfig();
  if (!config.enabled) return;

  const consent = useConsentStore();

  const syncConsentAndPage = () => {
    setMarketingConsent(consent.marketingAllowed);
    if (consent.marketingAllowed) {
      trackPageView(router.currentRoute.value.fullPath);
    }
  };

  syncConsentAndPage();

  watch(
    () => consent.marketingAllowed,
    () => {
      syncConsentAndPage();
    },
  );

  router.afterEach((to) => {
    if (!consent.marketingAllowed) return;
    trackPageView(to.fullPath);
  });
});
