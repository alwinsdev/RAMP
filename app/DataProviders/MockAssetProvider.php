<?php

declare(strict_types=1);

namespace App\DataProviders;

use App\Contracts\AssetDataProvider;
use App\DataObjects\AssetData;
use App\DataObjects\CategoryData;
use App\DataObjects\DistrictData;
use App\DataObjects\PanchayatData;
use App\DataObjects\PhotoData;
use App\DataObjects\ZoneData;
use App\DataProviders\Concerns\ReadsMockJson;

/**
 * Phase 1 implementation of the asset data seam — reads mock JSON in memory and
 * maps rows to DTOs. Contains NO business logic: no filtering semantics, no
 * lifecycle, no counting. It only loads, maps, and resolves parent relationships
 * for hierarchy reads (SL-02 / SL-08).
 */
final class MockAssetProvider implements AssetDataProvider
{
    use ReadsMockJson;

    /** @return array<int, DistrictData> */
    public function districts(): array
    {
        return array_map(
            static fn (array $row): DistrictData => DistrictData::fromArray($row),
            $this->readCollection('districts'),
        );
    }

    /** @return array<int, ZoneData> */
    public function zones(?string $districtId = null): array
    {
        $rows = $this->readCollection('zones');

        if ($districtId !== null) {
            $rows = array_filter($rows, static fn (array $r): bool => ($r['district_id'] ?? null) === $districtId);
        }

        return array_map(
            static fn (array $row): ZoneData => ZoneData::fromArray($row),
            array_values($rows),
        );
    }

    /** @return array<int, PanchayatData> */
    public function panchayats(?string $zoneId = null): array
    {
        $rows = $this->readCollection('panchayats');

        if ($zoneId !== null) {
            $rows = array_filter($rows, static fn (array $r): bool => ($r['zone_id'] ?? null) === $zoneId);
        }

        return array_map(
            static fn (array $row): PanchayatData => PanchayatData::fromArray($row),
            array_values($rows),
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

    /** @return array<int, AssetData> */
    public function assets(): array
    {
        return array_map(
            static fn (array $row): AssetData => AssetData::fromArray($row),
            $this->readCollection('assets'),
        );
    }

    public function assetById(string $assetId): ?AssetData
    {
        foreach ($this->readCollection('assets') as $row) {
            if (($row['id'] ?? null) === $assetId) {
                return AssetData::fromArray($row);
            }
        }

        return null; // predictable empty for "not found" (SL-05)
    }

    /** @return array<int, PhotoData> */
    public function photosByAsset(string $assetId): array
    {
        $asset = $this->assetById($assetId);

        return $asset?->photos ?? [];
    }
}
