<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Support\Lifecycle\LifecycleResult;

/**
 * Asset entity (docs/06 §3.6) — the core record.
 *
 * Stores ONLY the raw lifecycle inputs (constructionYear, expectedLife). The
 * computed lifecycle (age / remaining life / status) is NEVER stored in data; it
 * is attached at runtime by the AssetService via withLifecycle(), using the single
 * shared LifecycleCalculator (BR-LC-04/05). `$lifecycle` is null until enriched.
 *
 * Carries denormalized hierarchy/category labels for display convenience (docs/06
 * §3.6 denormalization note); the canonical relationships remain the FK ids.
 */
final readonly class AssetData
{
    /** @param array<int, PhotoData> $photos */
    public function __construct(
        public string $id,
        public string $assetNumber,
        public string $assetName,
        public string $categoryId,
        public string $assetType,
        public string $panchayatId,
        public ?string $categoryName = null,
        public ?string $panchayatName = null,
        public ?string $zoneId = null,
        public ?string $zoneName = null,
        public ?string $districtId = null,
        public ?string $districtName = null,
        public ?string $address = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?int $constructionYear = null,
        public ?string $createdAt = null,   // synthetic record-creation date (for "Recent Assets")
        public array $photos = [],
        public ?LifecycleResult $lifecycle = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        $photos = array_map(
            static fn (array $p): PhotoData => PhotoData::fromArray($p),
            $row['photos'] ?? [],
        );

        // Keep a deterministic order by sequence, else load order (BR-PH-02).
        usort(
            $photos,
            static fn (PhotoData $a, PhotoData $b): int => ($a->sequence ?? PHP_INT_MAX) <=> ($b->sequence ?? PHP_INT_MAX),
        );

        return new self(
            id: (string) $row['id'],
            assetNumber: (string) $row['asset_number'],
            assetName: (string) $row['asset_name'],
            categoryId: (string) $row['category_id'],
            assetType: (string) $row['asset_type'],
            panchayatId: (string) $row['panchayat_id'],
            categoryName: isset($row['category_name']) ? (string) $row['category_name'] : null,
            panchayatName: isset($row['panchayat_name']) ? (string) $row['panchayat_name'] : null,
            zoneId: isset($row['zone_id']) ? (string) $row['zone_id'] : null,
            zoneName: isset($row['zone_name']) ? (string) $row['zone_name'] : null,
            districtId: isset($row['district_id']) ? (string) $row['district_id'] : null,
            districtName: isset($row['district_name']) ? (string) $row['district_name'] : null,
            address: isset($row['address']) ? (string) $row['address'] : null,
            latitude: isset($row['latitude']) ? (float) $row['latitude'] : null,
            longitude: isset($row['longitude']) ? (float) $row['longitude'] : null,
            constructionYear: isset($row['construction_year']) ? (int) $row['construction_year'] : null,
            createdAt: isset($row['created_at']) ? (string) $row['created_at'] : null,
            photos: array_values($photos),
        );
    }

    /**
     * Return a copy with the computed lifecycle attached. Called ONLY by the
     * AssetService, which owns the single LifecycleCalculator. Status is therefore
     * computed in exactly one place and never persisted (BR-PR-02).
     */
    public function withLifecycle(LifecycleResult $lifecycle): self
    {
        return new self(
            id: $this->id,
            assetNumber: $this->assetNumber,
            assetName: $this->assetName,
            categoryId: $this->categoryId,
            assetType: $this->assetType,
            panchayatId: $this->panchayatId,
            categoryName: $this->categoryName,
            panchayatName: $this->panchayatName,
            zoneId: $this->zoneId,
            zoneName: $this->zoneName,
            districtId: $this->districtId,
            districtName: $this->districtName,
            address: $this->address,
            latitude: $this->latitude,
            longitude: $this->longitude,
            constructionYear: $this->constructionYear,
            createdAt: $this->createdAt,
            photos: $this->photos,
            lifecycle: $lifecycle,
        );
    }

    /**
     * Whether this asset has valid, displayable coordinates (BR-LO-01).
     * latitude ∈ [−90, 90], longitude ∈ [−180, 180]; both must be present.
     */
    public function hasValidCoordinates(): bool
    {
        if ($this->latitude === null || $this->longitude === null) {
            return false;
        }

        return $this->latitude >= -90 && $this->latitude <= 90
            && $this->longitude >= -180 && $this->longitude <= 180;
    }

    public function hasPhotos(): bool
    {
        return $this->photos !== [];
    }

    /**
     * The asset's hierarchy context (for breadcrumbs / drill-down links).
     *
     * @return array{districtId:?string, zoneId:?string, panchayatId:?string, categoryId:?string}
     */
    public function context(): array
    {
        return [
            'districtId' => $this->districtId,
            'zoneId' => $this->zoneId,
            'panchayatId' => $this->panchayatId,
            'categoryId' => $this->categoryId,
        ];
    }
}
