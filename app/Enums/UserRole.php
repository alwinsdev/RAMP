<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The three user roles for the POC (CR-01). Role drives data visibility (scope) and
 * the friendly label shown in the UI.
 */
enum UserRole: string
{
    case Administrator = 'administrator';
    case DistrictOfficer = 'district_officer';
    case PanchayatOfficer = 'panchayat_officer';

    public function label(): string
    {
        return match ($this) {
            self::Administrator => 'Administrator',
            self::DistrictOfficer => 'District Officer',
            self::PanchayatOfficer => 'Panchayat Officer',
        };
    }

    /** Short label for compact UI (e.g. a profile chip). */
    public function shortLabel(): string
    {
        return match ($this) {
            self::Administrator => 'Admin',
            self::DistrictOfficer => 'District Officer',
            self::PanchayatOfficer => 'Panchayat Officer',
        };
    }

    /** Administrators see everything; the others are scoped to their area. */
    public function isUnrestricted(): bool
    {
        return $this === self::Administrator;
    }
}
