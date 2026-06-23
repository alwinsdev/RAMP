<?php

declare(strict_types=1);

namespace App\DataObjects;

/**
 * The complete dashboard payload (docs/04 SCR-01, docs/08 §9).
 *
 * Every figure here is computed by DashboardService from the live dataset —
 * no hard-coded totals anywhere (BR-DI-05). KPIs reconcile: zone-wise and
 * panchayat-wise breakdown sums and the health total all equal totalAssets.
 *
 * @property array<int, Breakdown> $zoneBreakdown
 * @property array<int, Breakdown> $panchayatBreakdown
 * @property array<int, Breakdown> $categoryBreakdown
 */
final readonly class DashboardSummary
{
    /**
     * @param  array<int, Breakdown>  $zoneBreakdown
     * @param  array<int, Breakdown>  $panchayatBreakdown
     * @param  array<int, Breakdown>  $categoryBreakdown
     */
    public function __construct(
        public int $totalAssets,
        public int $totalCategories,
        public int $totalZones,
        public int $totalPanchayats,
        public HealthSummary $health,
        public array $zoneBreakdown,
        public array $panchayatBreakdown,
        public array $categoryBreakdown,
    ) {
    }
}
