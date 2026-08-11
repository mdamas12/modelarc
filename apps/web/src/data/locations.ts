/** Cascading locations via country-state-city (loaded on demand). */

import type { ICity, ICountry, IState } from 'country-state-city';

type CscModule = typeof import('country-state-city');

let csc: CscModule | null = null;
let loadPromise: Promise<CscModule> | null = null;

export async function ensureLocationData(): Promise<void> {
  if (csc) return;
  if (!loadPromise) {
    loadPromise = import('country-state-city').then((mod) => {
      csc = mod;
      return mod;
    });
  }
  await loadPromise;
}

function requireCsc(): CscModule {
  if (!csc) {
    throw new Error('Location data not loaded. Call ensureLocationData() first.');
  }
  return csc;
}

function byNameAsc<T extends { name: string }>(a: T, b: T): number {
  return a.name.localeCompare(b.name, 'es', { sensitivity: 'base' });
}

export function getAllCountries(): ICountry[] {
  return [...requireCsc().Country.getAllCountries()].sort(byNameAsc);
}

export function findCountryByName(name: string): ICountry | undefined {
  return requireCsc().Country.getAllCountries().find((c) => c.name === name);
}

export function getStatesOfCountry(countryIso: string): IState[] {
  return [...requireCsc().State.getStatesOfCountry(countryIso)].sort(byNameAsc);
}

export function getCitiesOfState(countryIso: string, stateIso: string): ICity[] {
  return [...requireCsc().City.getCitiesOfState(countryIso, stateIso)].sort(byNameAsc);
}

export function defaultCountryName(): string {
  return requireCsc().Country.getCountryByCode('VE')?.name ?? 'Venezuela';
}
