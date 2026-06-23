<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The four — and only four — lifecycle health statuses (BUSINESS_RULES BR-HL-*).
 *
 * This enum is the single definition of the status set, its exact display labels,
 * and its canonical colors (UI_RULES §3.1). No synonyms, no extra statuses, and no
 * status color defined anywhere else. The value of each case IS the exact label.
 */
enum LifecycleStatus: string
{
    case Healthy = 'Healthy';
    case NearExpiry = 'Near Expiry';
    case Expired = 'Expired';
    case Unknown = 'Unknown';

    /**
     * Human-facing label. Equal to the backing value, exposed as a method so call
     * sites read intentionally and never hard-code the string.
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Canonical hex color (UI_RULES §3.1). Reused by badges, charts, and map markers.
     */
    public function color(): string
    {
        return match ($this) {
            self::Healthy => '#1E8E3E',
            self::NearExpiry => '#F9A825',
            self::Expired => '#D93025',
            self::Unknown => '#80868B',
        };
    }

    /**
     * Readable text color to place ON the filled status color (accessible contrast).
     * Amber needs dark ink; the darker fills take white.
     */
    public function onColor(): string
    {
        return $this === self::NearExpiry ? '#202124' : '#FFFFFF';
    }

    /**
     * Tailwind token slug for this status (maps to the --color-status-* tokens in app.css).
     * e.g. "healthy" => bg-status-healthy / text-status-healthy.
     */
    public function token(): string
    {
        return match ($this) {
            self::Healthy => 'healthy',
            self::NearExpiry => 'near',
            self::Expired => 'expired',
            self::Unknown => 'unknown',
        };
    }

    /**
     * Whether this status counts toward the health distribution percentages.
     * Unknown is counted separately and excluded from percentages (BR-HL-08).
     */
    public function countsTowardHealth(): bool
    {
        return $this !== self::Unknown;
    }

    /**
     * The three "real" health statuses plus Unknown, in canonical display order.
     *
     * @return array<int, self>
     */
    public static function displayOrder(): array
    {
        return [self::Healthy, self::NearExpiry, self::Expired, self::Unknown];
    }
}
