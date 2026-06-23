<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Enums\LifecycleStatus;

/**
 * The lifecycle health distribution for the dashboard.
 *
 * Unknown assets are counted separately and EXCLUDED from the percentage base
 * (BR-HL-08): percentages are computed over Healthy + Near Expiry + Expired only.
 */
final readonly class HealthSummary
{
    public function __construct(
        public int $healthy,
        public int $nearExpiry,
        public int $expired,
        public int $unknown,
    ) {
    }

    /** Count for a given status. */
    public function count(LifecycleStatus $status): int
    {
        return match ($status) {
            LifecycleStatus::Healthy => $this->healthy,
            LifecycleStatus::NearExpiry => $this->nearExpiry,
            LifecycleStatus::Expired => $this->expired,
            LifecycleStatus::Unknown => $this->unknown,
        };
    }

    /** All assets across every status (Unknown included). */
    public function total(): int
    {
        return $this->healthy + $this->nearExpiry + $this->expired + $this->unknown;
    }

    /** Base for health percentages — excludes Unknown (BR-HL-08). */
    public function healthCountedTotal(): int
    {
        return $this->healthy + $this->nearExpiry + $this->expired;
    }

    /** Percentage of the (Unknown-excluded) base for a status; 0.0 when nothing counted. */
    public function percentage(LifecycleStatus $status): float
    {
        $base = $this->healthCountedTotal();

        if ($base === 0 || ! $status->countsTowardHealth()) {
            return 0.0;
        }

        return round($this->count($status) / $base * 100, 1);
    }
}
