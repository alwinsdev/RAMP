<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DashboardDataProvider;
use App\DataObjects\AssetData;
use App\DataObjects\Breakdown;
use App\DataObjects\DashboardSummary;
use App\DataObjects\HealthSummary;
use App\Enums\LifecycleStatus;
use App\Support\Lifecycle\LifecycleCalculator;

/**
 * Computes the entire dashboard payload from the live dataset. This is the single
 * aggregation implementation (BR-PR-02); no counts are hard-coded anywhere
 * (BR-DI-05). Lifecycle status used in the health summary comes from the shared
 * LifecycleCalculator (BR-LC-05) — never a stored value.
 *
 * Reconciliation guarantees (validated by AggregationTest):
 *  - zone-wise and panchayat-wise breakdown sums == totalAssets
 *  - Healthy + Near Expiry + Expired + Unknown == totalAssets
 *  - sum of category breakdown counts == totalAssets
 */
final class DashboardService
{
    public function __construct(
        private readonly DashboardDataProvider $provider,
        private readonly LifecycleCalculator $lifecycle,
    ) {
    }

    public function summary(): DashboardSummary
    {
        $assets = $this->provider->allAssets();
        $zones = $this->provider->zones();
        $panchayats = $this->provider->panchayats();
        $categories = $this->provider->categories();

        return new DashboardSummary(
            totalAssets: count($assets),
            totalCategories: count($categories),
            totalZones: count($zones),
            totalPanchayats: count($panchayats),
            health: $this->healthSummary($assets),
            zoneBreakdown: $this->breakdown(
                $assets,
                labels: $this->labelMap($zones),
                keyOf: static fn (AssetData $a): ?string => $a->zoneId,
                filterKey: 'zoneId',
            ),
            panchayatBreakdown: $this->breakdown(
                $assets,
                labels: $this->labelMap($panchayats),
                keyOf: static fn (AssetData $a): ?string => $a->panchayatId,
                filterKey: 'panchayatId',
            ),
            categoryBreakdown: $this->breakdown(
                $assets,
                labels: $this->labelMap($categories),
                keyOf: static fn (AssetData $a): ?string => $a->categoryId,
                filterKey: 'categoryId',
                includeZero: true, // categories with zero assets are still shown (BR-CT-04)
            ),
        );
    }

    /** @param array<int, AssetData> $assets */
    private function healthSummary(array $assets): HealthSummary
    {
        $tally = [
            LifecycleStatus::Healthy->value => 0,
            LifecycleStatus::NearExpiry->value => 0,
            LifecycleStatus::Expired->value => 0,
            LifecycleStatus::Unknown->value => 0,
        ];

        foreach ($assets as $asset) {
            $status = $this->lifecycle->compute($asset->constructionYear, $asset->expectedLife)->status;
            $tally[$status->value]++;
        }

        return new HealthSummary(
            healthy: $tally[LifecycleStatus::Healthy->value],
            nearExpiry: $tally[LifecycleStatus::NearExpiry->value],
            expired: $tally[LifecycleStatus::Expired->value],
            unknown: $tally[LifecycleStatus::Unknown->value],
        );
    }

    /**
     * Group assets by a key, label them, and sort by count desc then name (DB-11).
     *
     * @param  array<int, AssetData>  $assets
     * @param  array<string, string>  $labels
     * @param  callable(AssetData): ?string  $keyOf
     * @return array<int, Breakdown>
     */
    private function breakdown(array $assets, array $labels, callable $keyOf, string $filterKey, bool $includeZero = false): array
    {
        $counts = $includeZero ? array_fill_keys(array_keys($labels), 0) : [];

        foreach ($assets as $asset) {
            $key = $keyOf($asset);
            if ($key === null) {
                continue;
            }
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        $rows = [];
        foreach ($counts as $id => $count) {
            $rows[] = new Breakdown(
                id: (string) $id,
                name: $labels[$id] ?? (string) $id,
                count: $count,
                filterKey: $filterKey,
            );
        }

        usort($rows, static fn (Breakdown $a, Breakdown $b): int => $b->count <=> $a->count ?: strcmp($a->name, $b->name));

        return $rows;
    }

    /**
     * Build an id => name map from a list of DTOs exposing ->id and ->name.
     *
     * @param  array<int, object{id: string, name: string}>  $items
     * @return array<string, string>
     */
    private function labelMap(array $items): array
    {
        $map = [];
        foreach ($items as $item) {
            $map[$item->id] = $item->name;
        }

        return $map;
    }
}
