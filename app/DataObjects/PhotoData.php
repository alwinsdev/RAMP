<?php

declare(strict_types=1);

namespace App\DataObjects;

/**
 * Photo entity (docs/06 §3.7). Belongs to exactly one asset (BR-PH-01).
 *
 * In the POC photos are embedded in the asset record, so `assetId` may be null
 * for embedded reads; it is preserved when present (future separated collection).
 */
final readonly class PhotoData
{
    public function __construct(
        public string $id,
        public string $url,
        public ?string $caption = null,
        public ?int $sequence = null,
        public ?string $assetId = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (string) $row['id'],
            url: (string) $row['url'],
            caption: isset($row['caption']) ? (string) $row['caption'] : null,
            sequence: isset($row['sequence']) ? (int) $row['sequence'] : null,
            assetId: isset($row['asset_id']) ? (string) $row['asset_id'] : null,
        );
    }
}
