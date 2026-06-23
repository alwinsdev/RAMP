<?php

declare(strict_types=1);

namespace App\DataObjects;

/**
 * One row of a dashboard breakdown (zone-wise / panchayat-wise / category-wise).
 *
 * `filterKey`/`filterValue` describe the drill-down this row links to on the Asset
 * List (BR-NV-06) — e.g. ['zoneId' => 'ZONE-SLM-N'].
 */
final readonly class Breakdown
{
    public function __construct(
        public string $id,
        public string $name,
        public int $count,
        public string $filterKey,
    ) {
    }
}
