<?php

declare(strict_types=1);

namespace App\Support\Filtering;

/**
 * Immutable description of an asset-list query: hierarchy/category/status filters
 * plus a free-text search term.
 *
 * The POC uses single-valued filters per dimension (matching the single-select
 * controls in the wireframe). Different dimensions combine with AND (BR-FL-02);
 * the engine that applies this lives in AssetService so semantics are identical
 * across data providers (BR-FL-10).
 */
final readonly class AssetFilter
{
    public function __construct(
        public ?string $zoneId = null,
        public ?string $panchayatId = null,
        public ?string $categoryId = null,
        public ?string $assetType = null,
        public ?string $status = null,   // canonical status label, e.g. "Near Expiry" (BR-FL-05)
        public string $query = '',
    ) {
    }

    /**
     * Build from a loose array (e.g. Livewire #[Url] props / query string).
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            zoneId: self::nullableString($data['zoneId'] ?? null),
            panchayatId: self::nullableString($data['panchayatId'] ?? null),
            categoryId: self::nullableString($data['categoryId'] ?? null),
            assetType: self::nullableString($data['assetType'] ?? null),
            status: self::nullableString($data['status'] ?? null),
            query: trim((string) ($data['query'] ?? '')),
        );
    }

    /** True when no filter and no search term is active. */
    public function isEmpty(): bool
    {
        return $this->zoneId === null
            && $this->panchayatId === null
            && $this->categoryId === null
            && $this->assetType === null
            && $this->status === null
            && $this->query === '';
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
