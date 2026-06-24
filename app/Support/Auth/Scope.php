<?php

declare(strict_types=1);

namespace App\Support\Auth;

use App\DataObjects\AssetData;
use App\DataObjects\AuthUser;

/**
 * Role-based data visibility (CR-01 #6). Resolved from the authenticated user:
 *
 *   Administrator     -> unrestricted (sees everything)
 *   District Officer  -> scoped to one district
 *   Panchayat Officer -> scoped to one panchayat
 *
 * Services apply this scope to every read so an officer only ever sees their area.
 * A guest / missing user resolves to an unrestricted scope (routes are auth-gated
 * anyway, and tests act as an administrator by default).
 */
final readonly class Scope
{
    public function __construct(
        public ?string $districtId = null,
        public ?string $zoneId = null,
        public ?string $panchayatId = null,
    ) {
    }

    public static function forUser(?AuthUser $user): self
    {
        if ($user === null || $user->role->isUnrestricted()) {
            return new self();
        }

        return new self(
            districtId: $user->districtId,
            zoneId: $user->zoneId,
            panchayatId: $user->panchayatId,
        );
    }

    public function isUnrestricted(): bool
    {
        return $this->districtId === null && $this->zoneId === null && $this->panchayatId === null;
    }

    public function allowsDistrict(string $districtId): bool
    {
        return $this->districtId === null || $this->districtId === $districtId;
    }

    /** $zoneDistrictId is the district the zone belongs to. */
    public function allowsZone(string $zoneId, ?string $zoneDistrictId): bool
    {
        if ($this->panchayatId !== null && $this->zoneId !== null) {
            return $this->zoneId === $zoneId;
        }

        return $zoneDistrictId === null || $this->allowsDistrict($zoneDistrictId);
    }

    public function allowsPanchayat(string $panchayatId): bool
    {
        return $this->panchayatId === null || $this->panchayatId === $panchayatId;
    }

    public function allowsAsset(AssetData $asset): bool
    {
        if ($this->panchayatId !== null) {
            return $asset->panchayatId === $this->panchayatId;
        }

        if ($this->districtId !== null) {
            return $asset->districtId === $this->districtId;
        }

        return true;
    }
}
