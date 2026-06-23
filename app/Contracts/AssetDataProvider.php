<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DataObjects\AssetData;
use App\DataObjects\CategoryData;
use App\DataObjects\DistrictData;
use App\DataObjects\PanchayatData;
use App\DataObjects\PhotoData;
use App\DataObjects\ZoneData;

/**
 * THE SEAM. The stable contract every Service depends on for asset/hierarchy data.
 *
 * Phase 1: MockAssetProvider (reads storage/app/mock-data/*.json).
 * Phase 2+: EloquentAssetProvider (same shapes, from Postgres) — swapped via
 *           config in DataLayerServiceProvider with ZERO UI/Service changes.
 *
 * Contract guarantees (ARCHITECTURE_RULES SL-*):
 *  - Returns the documented DTO shapes; field names never drift across providers.
 *  - Returns predictable empties ([], null) for "not found" — never throws (SL-05).
 *  - Returns RAW asset inputs; lifecycle status is NOT computed here (SL-10) — the
 *    AssetService derives it through the shared LifecycleCalculator.
 */
interface AssetDataProvider
{
    /** @return array<int, DistrictData> */
    public function districts(): array;

    /** @return array<int, ZoneData> */
    public function zones(?string $districtId = null): array;

    /** @return array<int, PanchayatData> */
    public function panchayats(?string $zoneId = null): array;

    /** @return array<int, CategoryData> */
    public function categories(): array;

    /**
     * All assets (raw — no lifecycle attached). Filtering/search lives in the
     * service layer so semantics are identical across providers (SL-07).
     *
     * @return array<int, AssetData>
     */
    public function assets(): array;

    public function assetById(string $assetId): ?AssetData;

    /** @return array<int, PhotoData> */
    public function photosByAsset(string $assetId): array;
}
