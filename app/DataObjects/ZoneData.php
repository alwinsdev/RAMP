<?php

declare(strict_types=1);

namespace App\DataObjects;

/** Zone entity (docs/06 §3.3). Belongs to one District. */
final readonly class ZoneData
{
    public function __construct(
        public string $id,
        public string $districtId,
        public string $name,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (string) $row['id'],
            districtId: (string) $row['district_id'],
            name: (string) $row['name'],
        );
    }
}
