<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DashboardDataProvider;
use App\DataObjects\AssetData;
use App\DataObjects\Breakdown;
use App\DataObjects\DashboardSummary;
use App\DataObjects\DistrictSummary;
use App\DataObjects\HealthSummary;
use App\Enums\LifecycleStatus;
use App\Support\Auth\Scope;
use App\Support\Lifecycle\LifecycleCalculator;

/**
 * Computes the entire dashboard payload from the live, role-scoped dataset. This is
 * the single aggregation implementation (BR-PR-02); no counts are hard-coded
 * (BR-DI-05). Lifecycle status comes from the shared LifecycleCalculator (BR-LC-05)
 * — never a stored value. Every read is filtered by the user's Scope (RBAC).
 *
 * Reconciliation guarantees (validated by AggregationTest):
 *  - district-card asset counts sum to totalAssets
 *  - category distribution counts sum to totalAssets
 *  - Healthy + Near Expiry + Expired + Unknown == totalAssets
 */
final class DashboardService
{
    public function __construct(
        private readonly DashboardDataProvider $provider,
        private readonly LifecycleCalculator $lifecycle,
        private readonly Scope $scope,
    ) {
    }

    public function summary(): DashboardSummary
    {
        // Apply the role-based scope so each officer only sees their area (CR-01 #6).
        $assets = array_values(array_filter($this->provider->allAssets(), fn (AssetData $a): bool => $this->scope->allowsAsset($a)));
        $districts = array_values(array_filter($this->provider->districts(), fn ($d): bool => $this->scope->allowsDistrict($d->id)));
        $zones = array_values(array_filter($this->provider->zones(), fn ($z): bool => $this->scope->allowsZone($z->id, $z->districtId)));
        $allowedZoneIds = array_map(static fn ($z): string => $z->id, $zones);
        $panchayats = array_values(array_filter(
            $this->provider->panchayats(),
            fn ($p): bool => $this->scope->allowsPanchayat($p->id) && in_array($p->zoneId, $allowedZoneIds, true),
        ));
        $categories = $this->provider->categories();

        return new DashboardSummary(
            totalAssets: count($assets),
            totalDistricts: count($districts),
            totalCategories: count($categories),
            totalZones: count($zones),
            totalPanchayats: count($panchayats),
            health: $this->healthSummary($assets),
            districtCards: $this->districtCards($districts, $zones, $panchayats, $assets),
            categoryDistribution: $this->breakdown(
                $assets,
                labels: $this->labelMap($categories),
                keyOf: static fn (AssetData $a): ?string => $a->categoryId,
                filterKey: 'categoryId',
                includeZero: true, // categories with zero assets are still shown (BR-CT-04)
            ),
            recentAssets: $this->recentAssets($assets),
        );
    }

    /**
     * One summary card per district: zone/panchayat/asset counts + health, sorted
     * by asset count desc. Drills into the district's zones (hierarchy-first, CR-04).
     *
     * @param  array<int, \App\DataObjects\DistrictData>  $districts
     * @param  array<int, \App\DataObjects\ZoneData>  $zones
     * @param  array<int, \App\DataObjects\PanchayatData>  $panchayats
     * @param  array<int, AssetData>  $assets
     * @return array<int, DistrictSummary>
     */
    private function districtCards(array $districts, array $zones, array $panchayats, array $assets): array
    {
        $cards = [];
        foreach ($districts as $district) {
            $zoneIds = array_map(static fn ($z): string => $z->id, array_filter($zones, static fn ($z): bool => $z->districtId === $district->id));
            $districtAssets = array_values(array_filter($assets, static fn (AssetData $a): bool => $a->districtId === $district->id));

            $cards[] = new DistrictSummary(
                id: $district->id,
                name: $district->name,
                zoneCount: count($zoneIds),
                panchayatCount: count(array_filter($panchayats, static fn ($p): bool => in_array($p->zoneId, $zoneIds, true))),
                assetCount: count($districtAssets),
                health: $this->healthSummary($districtAssets),
            );
        }

        usort($cards, static fn (DistrictSummary $a, DistrictSummary $b): int => $b->assetCount <=> $a->assetCount ?: strcmp($a->name, $b->name));

        return $cards;
    }

    /**
     * The newest assets (by created_at), lifecycle-enriched for the status badge.
     *
     * @param  array<int, AssetData>  $assets
     * @return array<int, AssetData>
     */
    private function recentAssets(array $assets, int $limit = 5): array
    {
        $sorted = $assets;
        usort($sorted, static fn (AssetData $a, AssetData $b): int => ($b->createdAt ?? '') <=> ($a->createdAt ?? ''));

        return array_map(
            fn (AssetData $a): AssetData => $a->withLifecycle($this->lifecycle->compute($a->constructionYear)),
            array_slice($sorted, 0, $limit),
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
            $status = $this->lifecycle->compute($asset->constructionYear)->status;
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
