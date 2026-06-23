<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AssetDataProvider;
use App\DataObjects\AssetData;
use App\DataObjects\CategoryData;
use App\DataObjects\DistrictData;
use App\DataObjects\PanchayatData;
use App\DataObjects\PhotoData;
use App\DataObjects\ZoneData;
use App\Support\Filtering\AssetFilter;
use App\Support\Lifecycle\LifecycleCalculator;

/**
 * Orchestrates all asset reads for the UI. This is where business logic lives:
 * lifecycle enrichment, search, and filtering — never in a component or a provider
 * (ARCHITECTURE_RULES §3, BR-PR-02).
 *
 * Every asset returned by this service is enriched with its computed LifecycleResult
 * through the single shared LifecycleCalculator, so status is consistent everywhere
 * and exists in exactly one place.
 */
final class AssetService
{
    public function __construct(
        private readonly AssetDataProvider $provider,
        private readonly LifecycleCalculator $lifecycle,
    ) {
    }

    /**
     * All assets, lifecycle-enriched, with the filter + search applied.
     *
     * @return array<int, AssetData>
     */
    public function list(AssetFilter $filter): array
    {
        $assets = array_map(
            fn (AssetData $asset): AssetData => $this->enrich($asset),
            $this->provider->assets(),
        );

        $matched = array_filter(
            $assets,
            fn (AssetData $asset): bool => $this->matchesFilter($asset, $filter),
        );

        return array_values($matched);
    }

    /** Result count for the combined search + filter state (BR-SR-09 / BR-FL-09). */
    public function resultCount(AssetFilter $filter): int
    {
        return count($this->list($filter));
    }

    /** A single asset, lifecycle-enriched, or null when not found (SL-05). */
    public function detail(string $assetId): ?AssetData
    {
        $asset = $this->provider->assetById($assetId);

        return $asset === null ? null : $this->enrich($asset);
    }

    /** @return array<int, PhotoData> */
    public function photos(string $assetId): array
    {
        return $this->provider->photosByAsset($assetId);
    }

    // ---- Hierarchy passthroughs (used by the navigation screens) ----

    /** @return array<int, DistrictData> District is the top of the hierarchy. */
    public function districts(): array
    {
        return $this->provider->districts();
    }

    /** @return array<int, ZoneData> */
    public function zones(?string $districtId = null): array
    {
        return $this->provider->zones($districtId);
    }

    /** @return array<int, PanchayatData> */
    public function panchayats(?string $zoneId = null): array
    {
        return $this->provider->panchayats($zoneId);
    }

    /** @return array<int, CategoryData> */
    public function categories(): array
    {
        return $this->provider->categories();
    }

    // ---- Single-node lookups (used by breadcrumbs and screen headers) ----

    public function districtById(string $id): ?DistrictData
    {
        foreach ($this->provider->districts() as $district) {
            if ($district->id === $id) {
                return $district;
            }
        }

        return null;
    }

    public function zoneById(string $id): ?ZoneData
    {
        foreach ($this->provider->zones() as $zone) {
            if ($zone->id === $id) {
                return $zone;
            }
        }

        return null;
    }

    public function panchayatById(string $id): ?PanchayatData
    {
        foreach ($this->provider->panchayats() as $panchayat) {
            if ($panchayat->id === $id) {
                return $panchayat;
            }
        }

        return null;
    }

    public function categoryById(string $id): ?CategoryData
    {
        foreach ($this->provider->categories() as $category) {
            if ($category->id === $id) {
                return $category;
            }
        }

        return null;
    }

    // ---- Scoped child asset counts (used by the hierarchy drill-down screens) ----
    // Counts are derived from the live dataset (BR-DI-05); zero is a valid count.

    /**
     * Asset count per district (keyed by district id), over the whole dataset.
     *
     * @return array<string, int>
     */
    public function assetCountsByDistrict(): array
    {
        return $this->tally(fn (AssetData $a): ?string => $a->districtId);
    }

    /**
     * Asset count per zone within a district.
     *
     * @return array<string, int>
     */
    public function assetCountsByZone(string $districtId): array
    {
        return $this->tally(
            fn (AssetData $a): ?string => $a->zoneId,
            fn (AssetData $a): bool => $a->districtId === $districtId,
        );
    }

    /**
     * Asset count per panchayat within a zone.
     *
     * @return array<string, int>
     */
    public function assetCountsByPanchayat(string $zoneId): array
    {
        return $this->tally(
            fn (AssetData $a): ?string => $a->panchayatId,
            fn (AssetData $a): bool => $a->zoneId === $zoneId,
        );
    }

    /**
     * Asset count per category within a panchayat.
     *
     * @return array<string, int>
     */
    public function assetCountsByCategory(string $panchayatId): array
    {
        return $this->tally(
            fn (AssetData $a): ?string => $a->categoryId,
            fn (AssetData $a): bool => $a->panchayatId === $panchayatId,
        );
    }

    /**
     * Group raw assets by a key, optionally scoped by a predicate, and count.
     *
     * @param  callable(AssetData): ?string  $keyOf
     * @param  (callable(AssetData): bool)|null  $scope
     * @return array<string, int>
     */
    private function tally(callable $keyOf, ?callable $scope = null): array
    {
        $counts = [];

        foreach ($this->provider->assets() as $asset) {
            if ($scope !== null && ! $scope($asset)) {
                continue;
            }

            $key = $keyOf($asset);
            if ($key === null) {
                continue;
            }

            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return $counts;
    }

    /** Attach the computed lifecycle to a raw asset (the one enrichment point). */
    private function enrich(AssetData $asset): AssetData
    {
        return $asset->withLifecycle(
            $this->lifecycle->compute($asset->constructionYear, $asset->expectedLife),
        );
    }

    /**
     * AND across every active filter dimension, combined with the search term
     * (BR-FL-02 / BR-SR-06). The asset is assumed already lifecycle-enriched so the
     * status filter compares against the computed status (BR-FL-05).
     */
    private function matchesFilter(AssetData $asset, AssetFilter $filter): bool
    {
        if ($filter->districtId !== null && $asset->districtId !== $filter->districtId) {
            return false;
        }

        if ($filter->zoneId !== null && $asset->zoneId !== $filter->zoneId) {
            return false;
        }

        if ($filter->panchayatId !== null && $asset->panchayatId !== $filter->panchayatId) {
            return false;
        }

        if ($filter->categoryId !== null && $asset->categoryId !== $filter->categoryId) {
            return false;
        }

        if ($filter->assetType !== null && $asset->assetType !== $filter->assetType) {
            return false;
        }

        if ($filter->status !== null && $asset->lifecycle?->status->value !== $filter->status) {
            return false;
        }

        return $this->matchesQuery($asset, $filter->query);
    }

    /**
     * Case-insensitive, trimmed, substring match against asset_name and
     * asset_number (BR-SR-01..05). An empty query matches everything.
     */
    private function matchesQuery(AssetData $asset, string $rawQuery): bool
    {
        $q = trim(mb_strtolower($rawQuery));

        if ($q === '') {
            return true;
        }

        return str_contains(mb_strtolower($asset->assetName), $q)
            || str_contains(mb_strtolower($asset->assetNumber), $q);
    }
}
