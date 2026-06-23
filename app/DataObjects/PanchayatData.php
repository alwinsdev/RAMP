<?php

declare(strict_types=1);

namespace App\DataObjects;

/** Panchayat entity (docs/06 §3.4). Belongs to one Zone; direct parent of assets. */
final readonly class PanchayatData
{
    public function __construct(
        public string $id,
        public string $zoneId,
        public string $name,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (string) $row['id'],
            zoneId: (string) $row['zone_id'],
            name: (string) $row['name'],
        );
    }
}
