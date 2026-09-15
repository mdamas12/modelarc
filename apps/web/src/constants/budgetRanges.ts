/** Mirrors apps/api/config/leads.php — keep values in sync with backend validation. */
export const BUDGET_RANGE_OPTIONS = [
  { value: 'low', label: 'Bajo' },
  { value: 'medium', label: 'Medio' },
  { value: 'high', label: 'Alto' },
  { value: 'special', label: 'Proyecto Especial' },
] as const;

export type BudgetRangeValue = (typeof BUDGET_RANGE_OPTIONS)[number]['value'];

export function budgetRangeLabel(value?: string | null): string {
  if (!value) return '—';
  return BUDGET_RANGE_OPTIONS.find((o) => o.value === value)?.label || value;
}
