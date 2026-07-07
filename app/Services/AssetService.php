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
use App\Enums\LifecycleStatus;
use App\Support\Auth\Scope;
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
        private readonly Scope $scope,
    ) {}

    /**
     * Raw assets visible to the current user (role-based scope, CR-01 #6).
     *
     * @return array<int, AssetData>
     */
    private function scopedRawAssets(): array
    {
        return array_values(array_filter(
            $this->provider->assets(),
            fn (AssetData $asset): bool => $this->scope->allowsAsset($asset),
        ));
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
            $this->scopedRawAssets(),
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

    /**
     * Map markers for the Asset Intelligence Map — role-scoped, lifecycle-enriched,
     * and limited to assets with valid coordinates. Returns plain arrays ready for
     * the map's JS payload (status colour from the canonical enum; no UI/route logic).
     *
     * @return array<int, array{id:string,name:string,number:string,category:?string,panchayat:?string,status:string,color:string,year:?int,remaining:?int,lat:float,lng:float}>
     */
    public function mapMarkers(AssetFilter $filter): array
    {
        $markers = [];
        foreach ($this->list($filter) as $asset) {
            if (! $asset->hasValidCoordinates()) {
                continue;
            }

            $status = $asset->lifecycle?->status ?? LifecycleStatus::Unknown;

            $markers[] = [
                'id' => $asset->id,
                'name' => $asset->assetName,
                'number' => $asset->assetNumber,
                'category' => $asset->categoryName,
                'panchayat' => $asset->panchayatName,
                'status' => $status->value,
                'color' => $status->color(),
                'year' => $asset->constructionYear,
                'remaining' => $asset->lifecycle?->remainingLife,
                'lat' => $asset->latitude,
                'lng' => $asset->longitude,
            ];
        }

        return $markers;
    }

    /** A single asset, lifecycle-enriched, or null when not found or out of scope (SL-05). */
    public function detail(string $assetId): ?AssetData
    {
        $asset = $this->provider->assetById($assetId);

        if ($asset === null || ! $this->scope->allowsAsset($asset)) {
            return null;
        }

        return $this->enrich($asset);
    }

    /** @return array<int, PhotoData> Photos for an in-scope asset; empty otherwise (defence in depth, SL-05). */
    public function photos(string $assetId): array
    {
        $asset = $this->provider->assetById($assetId);

        if ($asset === null || ! $this->scope->allowsAsset($asset)) {
            return [];
        }

        return $this->provider->photosByAsset($assetId);
    }

    // ---- Hierarchy passthroughs (used by the navigation screens) ----

    /** @return array<int, DistrictData> District is the top of the hierarchy. */
    public function districts(): array
    {
        return array_values(array_filter(
            $this->provider->districts(),
            fn (DistrictData $d): bool => $this->scope->allowsDistrict($d->id),
        ));
    }

    /** @return array<int, ZoneData> */
    public function zones(?string $districtId = null): array
    {
        return array_values(array_filter(
            $this->provider->zones($districtId),
            fn (ZoneData $z): bool => $this->scope->allowsZone($z->id, $z->districtId),
        ));
    }

    /** @return array<int, PanchayatData> */
    public function panchayats(?string $zoneId = null): array
    {
        $allowedZoneIds = array_map(static fn (ZoneData $z): string => $z->id, $this->zones());

        return array_values(array_filter(
            $this->provider->panchayats($zoneId),
            fn (PanchayatData $p): bool => $this->scope->allowsPanchayat($p->id) && in_array($p->zoneId, $allowedZoneIds, true),
        ));
    }

    /** @return array<int, CategoryData> */
    public function categories(): array
    {
        return $this->provider->categories();
    }

    // ---- Single-node lookups (used by breadcrumbs and screen headers) ----

    // These single-node lookups are role-scoped exactly like detail(): a node
    // outside the user's scope resolves to null, so the hierarchy drill-down
    // screens (which redirect on null) cannot disclose an out-of-scope district /
    // zone / panchayat name via a hand-edited route param (RBAC / IDOR).

    public function districtById(string $id): ?DistrictData
    {
        if (! $this->scope->allowsDistrict($id)) {
            return null;
        }

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
                return $this->scope->allowsZone($zone->id, $zone->districtId) ? $zone : null;
            }
        }

        return null;
    }

    public function panchayatById(string $id): ?PanchayatData
    {
        foreach ($this->provider->panchayats() as $panchayat) {
            if ($panchayat->id === $id) {
                // Both the panchayat- and zone-level scope must allow it: a District
                // Officer must not reach a panchayat in another district, which
                // allowsPanchayat() alone (null panchayat scope) would permit.
                $allowed = $this->scope->allowsPanchayat($panchayat->id)
                    && $this->scope->allowsZone($panchayat->zoneId, $this->zoneDistrictId($panchayat->zoneId));

                return $allowed ? $panchayat : null;
            }
        }

        return null;
    }

    /** The district a zone belongs to, read unscoped (used to scope-check a panchayat). */
    private function zoneDistrictId(string $zoneId): ?string
    {
        foreach ($this->provider->zones() as $zone) {
            if ($zone->id === $zoneId) {
                return $zone->districtId;
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

    // ---- Flat counts across the whole (scoped) dataset (used by the index screens) ----

    /** Asset count per zone across the user's full scope. @return array<string, int> */
    public function assetCountsPerZone(): array
    {
        return $this->tally(fn (AssetData $a): ?string => $a->zoneId);
    }

    /** Asset count per panchayat across the user's full scope. @return array<string, int> */
    public function assetCountsPerPanchayat(): array
    {
        return $this->tally(fn (AssetData $a): ?string => $a->panchayatId);
    }

    /** Asset count per category across the user's full scope. @return array<string, int> */
    public function assetCountsPerCategory(): array
    {
        return $this->tally(fn (AssetData $a): ?string => $a->categoryId);
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

        foreach ($this->scopedRawAssets() as $asset) {
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
            $this->lifecycle->compute($asset->constructionYear),
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
