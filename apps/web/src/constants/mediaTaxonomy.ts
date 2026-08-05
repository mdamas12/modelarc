export const PROJECT_CATEGORIES = [
  { label: 'Residencial', value: 'residencial' },
  { label: 'Comercial', value: 'comercial' },
  { label: 'Corporativo', value: 'corporativo' },
] as const;

export type ProjectCategory = (typeof PROJECT_CATEGORIES)[number]['value'];

export const MEDIA_SUBCATEGORIES: Record<ProjectCategory, { label: string; value: string }[]> = {
  residencial: [
    { label: 'Sala', value: 'sala' },
    { label: 'Comedor', value: 'comedor' },
    { label: 'Salón de juegos', value: 'salon_juegos' },
    { label: 'Área de piscina', value: 'area_piscina' },
    { label: 'Patio', value: 'patio' },
    { label: 'Baños', value: 'banos' },
    { label: 'Habitación', value: 'habitacion' },
    { label: 'Cocina', value: 'cocina' },
    { label: 'Fachada', value: 'fachada' },
    { label: 'Otro', value: 'otro' },
  ],
  comercial: [
    { label: 'Lobby', value: 'lobby' },
    { label: 'Local', value: 'local' },
    { label: 'Oficina', value: 'oficina' },
    { label: 'Fachada', value: 'fachada' },
    { label: 'Estacionamiento', value: 'estacionamiento' },
    { label: 'Otro', value: 'otro' },
  ],
  corporativo: [
    { label: 'Lobby', value: 'lobby' },
    { label: 'Sala de juntas', value: 'sala_juntas' },
    { label: 'Oficina', value: 'oficina' },
    { label: 'Áreas comunes', value: 'areas_comunes' },
    { label: 'Fachada', value: 'fachada' },
    { label: 'Otro', value: 'otro' },
  ],
};

export function subcategoriesFor(category?: string | null) {
  if (!category || !(category in MEDIA_SUBCATEGORIES)) return [];
  return MEDIA_SUBCATEGORIES[category as ProjectCategory];
}

export function allSubcategories() {
  const map = new Map<string, string>();
  for (const list of Object.values(MEDIA_SUBCATEGORIES)) {
    for (const item of list) {
      if (!map.has(item.value)) map.set(item.value, item.label);
    }
  }
  return Array.from(map.entries())
    .map(([value, label]) => ({ value, label }))
    .sort((a, b) => a.label.localeCompare(b.label, 'es'));
}

export function labelCategory(value?: string | null) {
  return PROJECT_CATEGORIES.find((o) => o.value === value)?.label || value || '—';
}

export function labelSubcategory(category?: string | null, subcategory?: string | null) {
  if (!subcategory) return '—';
  const fromCategory = subcategoriesFor(category).find((o) => o.value === subcategory)?.label;
  if (fromCategory) return fromCategory;
  return allSubcategories().find((o) => o.value === subcategory)?.label || subcategory;
}
