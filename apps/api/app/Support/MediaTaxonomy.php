<?php

namespace App\Support;

final class MediaTaxonomy
{
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

    public static function isValidSubcategory(?string $category, ?string $subcategory): bool
    {
        if ($subcategory === null || $subcategory === '') {
            return true;
        }

        return in_array($subcategory, self::subcategoriesFor($category), true);
    }
}
