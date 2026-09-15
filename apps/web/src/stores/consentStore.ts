import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import {
  acceptAllConsent,
  buildCustomConsent,
  hasDecided,
  readConsentFromStorage,
  rejectNonEssentialConsent,
  writeConsentToStorage,
  type ConsentPreferences,
} from '@/tracking/consent';

export const useConsentStore = defineStore('consent', () => {
  const preferences = ref<ConsentPreferences>(readConsentFromStorage());

  const decided = computed(() => hasDecided(preferences.value));
  const marketingAllowed = computed(() => preferences.value.marketing === true);
  const analyticsAllowed = computed(() => preferences.value.analytics === true);
  const showBanner = computed(() => !decided.value);

  function persist(next: ConsentPreferences) {
    preferences.value = next;
    writeConsentToStorage(next);
  }

  function acceptAll() {
    persist(acceptAllConsent());
  }

  function rejectNonEssential() {
    persist(rejectNonEssentialConsent());
  }

  function savePreferences(input: { analytics: boolean; marketing: boolean }) {
    persist(buildCustomConsent(input));
  }

  function openPreferences() {
    // Clearing decidedAt forces the banner / preferences panel to show again.
    preferences.value = {
      ...preferences.value,
      decidedAt: null,
    };
  }

  return {
    preferences,
    decided,
    marketingAllowed,
    analyticsAllowed,
    showBanner,
    acceptAll,
    rejectNonEssential,
    savePreferences,
    openPreferences,
  };
});
