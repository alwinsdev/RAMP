<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DataObjects\AssetData;
use App\DataObjects\CategoryData;
use App\DataObjects\DistrictData;
use App\DataObjects\PanchayatData;
use App\DataObjects\ZoneData;

/**
 * Data seam for the dashboard. Returns the RAW dataset the DashboardService
 * aggregates over — the provider performs NO counting or status computation
 * (that is business logic and lives in the service, BR-DI-05 / TD-05).
 *
 * Phase 1: MockDashboardProvider (reads JSON).
 * Phase 2+: a query-backed provider returning the same shapes.
 */
interface DashboardDataProvider
{
    /** @return array<int, AssetData> */
    public function allAssets(): array;

    /** @return array<int, DistrictData> */
    public function districts(): array;

    /** @return array<int, ZoneData> */
    public function zones(): array;

    /** @return array<int, PanchayatData> */
    public function panchayats(): array;

    /** @return array<int, CategoryData> */
    public function categories(): array;
}
