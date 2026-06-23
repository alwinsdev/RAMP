<?php

declare(strict_types=1);

namespace App\DataObjects;

/** District entity (docs/06 §3.2). Top of the administrative hierarchy in the POC. */
final readonly class DistrictData
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $code = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (string) $row['id'],
            name: (string) $row['name'],
            code: isset($row['code']) ? (string) $row['code'] : null,
        );
    }
}
