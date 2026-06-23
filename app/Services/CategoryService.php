<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AssetDataProvider;
use App\DataObjects\CategoryData;

/**
 * Category taxonomy + derived per-category asset counts.
 *
 * Counts are computed over the live dataset (BR-DI-05). Every category is returned
 * even when it has zero assets (BR-CT-04), and the per-category counts reconcile to
 * the total asset count (BR-CT-03).
 */
final class CategoryService
{
    public function __construct(
        private readonly AssetDataProvider $provider,
    ) {
    }

    /**
     * All categories, each carrying its derived asset count.
     *
     * @return array<int, CategoryData>
     */
    public function withCounts(): array
    {
        $counts = [];
        foreach ($this->provider->assets() as $asset) {
            $counts[$asset->categoryId] = ($counts[$asset->categoryId] ?? 0) + 1;
        }

        return array_map(
            static fn (CategoryData $category): CategoryData => $category->withAssetCount($counts[$category->id] ?? 0),
            $this->provider->categories(),
        );
    }
}
