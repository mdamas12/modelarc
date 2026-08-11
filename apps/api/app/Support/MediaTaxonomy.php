<?php

namespace App\Support;

use App\Models\Category;

final class MediaTaxonomy
{
    /**
     * Legacy hardcoded taxonomy, kept as a fallback for categories that
     * are not (yet) managed through the Category/Subcategory tables.
     */
    public const CATEGORIES = [
        'residencial',
        'comercial',
        'corporativo',
    ];

    /**
     * @return array<string, list<string>>
     */
    public static function subcategoriesByCategory(): array
    {
        return [
            'residencial' => [
                'sala',
                'comedor',
                'salon_juegos',
                'area_piscina',
                'patio',
                'banos',
                'habitacion',
                'cocina',
                'fachada',
                'otro',
            ],
            'comercial' => [
                'lobby',
                'local',
                'oficina',
                'fachada',
                'estacionamiento',
                'otro',
            ],
            'corporativo' => [
                'lobby',
                'sala_juntas',
                'oficina',
                'areas_comunes',
                'fachada',
                'otro',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function subcategoriesFor(?string $category): array
    {
        if (! $category) {
            return [];
        }

        return self::subcategoriesByCategory()[$category] ?? [];
    }

    /**
     * Resolves the allowed subcategory slugs for a category slug, preferring
     * the DB-managed Subcategory records and falling back to the legacy
     * hardcoded taxonomy when the category has none configured.
     *
     * @return list<string>
     */
    public static function resolveSubcategorySlugs(?string $categorySlug): array
    {
        if (! $categorySlug) {
            return [];
        }

        $category = Category::query()->where('slug', $categorySlug)->first();

        if ($category) {
            $dbSlugs = $category->subcategories()->pluck('slug')->all();

            return $dbSlugs !== [] ? $dbSlugs : [];
        }

        return self::subcategoriesFor($categorySlug);
    }

    public static function isValidCategory(?string $category): bool
    {
        if ($category === null || $category === '') {
            return true;
        }

        if (Category::query()->where('slug', $category)->exists()) {
            return true;
        }

        return in_array($category, self::CATEGORIES, true);
    }

    public static function isValidSubcategory(?string $category, ?string $subcategory): bool
    {
        if ($subcategory === null || $subcategory === '') {
            return true;
        }

        $dbCategory = $category ? Category::query()->where('slug', $category)->first() : null;

        if ($dbCategory) {
            $allowed = $dbCategory->subcategories()->pluck('slug')->all();

            // Categories managed via the admin CRUD without subcategories
            // configured yet shouldn't block arbitrary subcategory values.
            return $allowed === [] || in_array($subcategory, $allowed, true);
        }

        return in_array($subcategory, self::subcategoriesFor($category), true);
    }
}
