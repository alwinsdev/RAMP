<?php

declare(strict_types=1);

namespace App\DataObjects;

/**
 * The complete dashboard payload (CR-09, docs/04 SCR-01).
 *
 * Every figure is computed by DashboardService from the live, role-scoped dataset —
 * no hard-coded totals (BR-DI-05). KPIs reconcile: district-card asset counts and
 * the category distribution both sum to totalAssets; the health total equals it too.
 *
 * @property array<int, DistrictSummary> $districtCards
 * @property array<int, Breakdown>       $categoryDistribution
 * @property array<int, AssetData>       $recentAssets
 */
final readonly class DashboardSummary
{
    /**
     * @param  array<int, DistrictSummary>  $districtCards
     * @param  array<int, Breakdown>  $categoryDistribution
     * @param  array<int, AssetData>  $recentAssets
     */
    public function __construct(
        public int $totalAssets,
        public int $totalDistricts,
        public int $totalCategories,
        public int $totalZones,
        public int $totalPanchayats,
        public HealthSummary $health,
        public array $districtCards,
        public array $categoryDistribution,
        public array $recentAssets,
    ) {
    }

    /** The largest category count — used to scale the distribution bars. */
    public function maxCategoryCount(): int
    {
        return array_reduce(
            $this->categoryDistribution,
            static fn (int $max, Breakdown $b): int => max($max, $b->count),
            1,
        );
    }
}
