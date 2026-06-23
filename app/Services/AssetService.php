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
