<?php

declare(strict_types=1);

namespace App\DataObjects;

/**
 * AssetCategory entity (docs/06 §3.5). Classifies assets; carries its sub-types inline.
 *
 * `assetCount` is a derived, display-time figure (BR-CT-03/04) populated by the
 * CategoryService over the live dataset — never read from JSON, never hard-coded.
 */
final readonly class CategoryData
{
    /** @param array<int, string> $subTypes */
    public function __construct(
        public string $id,
        public string $name,
        public array $subTypes,
        public ?string $description = null,
        public ?int $assetCount = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (string) $row['id'],
            name: (string) $row['name'],
            subTypes: array_values(array_map('strval', $row['sub_types'] ?? [])),
            description: isset($row['description']) ? (string) $row['description'] : null,
        );
    }

    /** Return a copy carrying the derived asset count (set once by the service). */
    public function withAssetCount(int $count): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            subTypes: $this->subTypes,
            description: $this->description,
            assetCount: $count,
        );
    }
}
