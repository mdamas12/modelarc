<?php

namespace App\Support;

final class BudgetRange
{
    /**
     * @return array<string, string> value => label
     */
    public static function options(): array
    {
        /** @var array<string, string> $ranges */
        $ranges = config('leads.budget_ranges', []);

        return $ranges;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_keys(self::options());
    }

    public static function label(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::options()[$value] ?? $value;
    }

    public static function isValid(?string $value): bool
    {
        return $value !== null && $value !== '' && array_key_exists($value, self::options());
    }
}
