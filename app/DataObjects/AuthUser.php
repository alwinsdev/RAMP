<?php

declare(strict_types=1);

namespace App\DataObjects;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * The authenticated user (CR-01) — mock, sourced from users.json, never a database.
 *
 * Implements Laravel's Authenticatable so it plugs into the standard session guard
 * (Auth::attempt / auth()->user() / actingAs in tests). Carries the role and the
 * optional scope (district/panchayat) that drives role-based data visibility.
 */
final class AuthUser implements Authenticatable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly UserRole $role,
        public readonly ?string $districtId = null,
        public readonly ?string $panchayatId = null,
        public readonly ?string $zoneId = null,
        private readonly string $password = '',
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromArray(array $row): self
    {
        return new self(
            id: (string) $row['id'],
            name: (string) $row['name'],
            email: (string) $row['email'],
            role: UserRole::from((string) $row['role']),
            districtId: isset($row['district_id']) ? (string) $row['district_id'] : null,
            panchayatId: isset($row['panchayat_id']) ? (string) $row['panchayat_id'] : null,
            zoneId: isset($row['zone_id']) ? (string) $row['zone_id'] : null,
            password: (string) ($row['password'] ?? ''),
        );
    }

    /** Initials for the avatar chip. */
    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $initials = array_map(static fn (string $p): string => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, 2));

        return implode('', $initials) ?: 'U';
    }

    // ---- Authenticatable contract ----

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): string
    {
        return $this->id;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }

    public function getRememberToken(): string
    {
        return '';
    }

    public function setRememberToken($value): void
    {
        // Mock users are stateless — no remember token persistence.
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}
