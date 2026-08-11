<?php

use App\Support\MediaTaxonomy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Seeds the default Category/Subcategory taxonomy (idempotent) and
     * backfills projects.category_id from the legacy projects.category string.
     */
    public function up(): void
    {
        $now = now();

        $defaultCategoryNames = [
            'residencial' => 'Residencial',
            'comercial' => 'Comercial',
            'corporativo' => 'Corporativo',
        ];

        // Any legacy category strings already in use (including custom ones)
        // are also ensured to exist as Category rows so no project loses its category.
        $existingCategoryStrings = DB::table('projects')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        $categoryNamesBySlug = $defaultCategoryNames;
        foreach ($existingCategoryStrings as $categoryString) {
            $slug = Str::slug($categoryString) ?: Str::slug(Str::random(8));
            if (! isset($categoryNamesBySlug[$slug])) {
                $categoryNamesBySlug[$slug] = Str::title(str_replace(['-', '_'], ' ', $categoryString));
            }
        }

        $order = 0;
        foreach ($categoryNamesBySlug as $slug => $name) {
            $existing = DB::table('categories')->where('slug', $slug)->first();

            if ($existing) {
                continue;
            }

            DB::table('categories')->insert([
                'name' => $name,
                'slug' => $slug,
                'order' => $order,
                'published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $order++;
        }

        $categories = DB::table('categories')->get()->keyBy('slug');

        // Seed subcategories from the legacy hardcoded taxonomy for the categories
        // that match it. Custom categories start with no subcategories (admins can add them).
        foreach (MediaTaxonomy::subcategoriesByCategory() as $categorySlug => $subSlugs) {
            $category = $categories->get($categorySlug);
            if (! $category) {
                continue;
            }

            $subOrder = 0;
            foreach ($subSlugs as $subSlug) {
                $exists = DB::table('subcategories')
                    ->where('category_id', $category->id)
                    ->where('slug', $subSlug)
                    ->exists();

                if ($exists) {
                    $subOrder++;

                    continue;
                }

                DB::table('subcategories')->insert([
                    'category_id' => $category->id,
                    'name' => Str::title(str_replace(['-', '_'], ' ', $subSlug)),
                    'slug' => $subSlug,
                    'order' => $subOrder,
                    'published' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $subOrder++;
            }
        }

        // Backfill projects.category_id from the legacy string column.
        foreach ($categories as $slug => $category) {
            DB::table('projects')
                ->where('category', $slug)
                ->whereNull('category_id')
                ->update(['category_id' => $category->id]);
        }
    }

    public function down(): void
    {
        // Non-destructive: leave categories/subcategories and project links in place.
    }
};
