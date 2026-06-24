<?php

declare(strict_types=1);

namespace App\DataProviders;

use App\Contracts\DashboardDataProvider;
use App\DataObjects\AssetData;
use App\DataObjects\CategoryData;
use App\DataObjects\DistrictData;
use App\DataObjects\PanchayatData;
use App\DataObjects\ZoneData;
use App\DataProviders\Concerns\ReadsMockJson;

/**
 * Phase 1 implementation of the dashboard data seam. Returns the RAW dataset the
 * DashboardService aggregates over. Performs NO counting or status computation —
 * that is business logic owned by the service (BR-DI-05).
 */
final class MockDashboardProvider implements DashboardDataProvider
{
    use ReadsMockJson;

    /** @return array<int, AssetData> */
    public function allAssets(): array
    {
        return array_map(
            static fn (array $row): AssetData => AssetData::fromArray($row),
            $this->readCollection('assets'),
        );
    }

    /** @return array<int, DistrictData> */
    public function districts(): array
    {
        return array_map(
            static fn (array $row): DistrictData => DistrictData::fromArray($row),
            $this->readCollection('districts'),
        );
    }

    /** @return array<int, ZoneData> */
    public function zones(): array
    {
        return array_map(
            static fn (array $row): ZoneData => ZoneData::fromArray($row),
            $this->readCollection('zones'),
        );
    }

    /** @return array<int, PanchayatData> */
    public function panchayats(): array
    {
        return array_map(
            static fn (array $row): PanchayatData => PanchayatData::fromArray($row),
            $this->readCollection('panchayats'),
        );
    }

    /** @return array<int, CategoryData> */
    public function categories(): array
    {
        return array_map(
            static fn (array $row): CategoryData => CategoryData::fromArray($row),
            $this->readCollection('categories'),
        );
    }
}
