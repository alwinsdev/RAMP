<?php

declare(strict_types=1);

namespace App\DataObjects;

/**
 * A district summary card on the Dashboard (CR-09). Counts are derived from the
 * live, role-scoped dataset by DashboardService — never hard-coded (BR-DI-05).
 * The card drills into the district's zones (hierarchy-first, CR-04).
 */
final readonly class DistrictSummary
{
    public function __construct(
        public string $id,
        public string $name,
        public int $zoneCount,
        public int $panchayatCount,
        public int $assetCount,
        public HealthSummary $health,
    ) {
    }
}
