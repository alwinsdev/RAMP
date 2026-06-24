<?php

declare(strict_types=1);

namespace App\DataProviders;

use App\DataObjects\AuthUser;
use App\DataProviders\Concerns\ReadsMockJson;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * Laravel user provider backed by mock JSON (users.json) — no database (CR-01).
 *
 * Plugs into the standard session guard so Auth::attempt / auth()->user() / actingAs
 * all work. Passwords are stored in plaintext for the demo and compared in
 * constant time; this is a POC-only convenience, never production.
 */
final class MockUserProvider implements UserProvider
{
    use ReadsMockJson;

    /** @return AuthUser|null */
    public function retrieveById($identifier): ?Authenticatable
    {
        foreach ($this->readCollection('users') as $row) {
            if (($row['id'] ?? null) === $identifier) {
                return AuthUser::fromArray($row);
            }
        }

        return null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null; // mock users have no remember tokens
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        // no-op (stateless mock users)
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $email = $credentials['email'] ?? null;
        if ($email === null) {
            return null;
        }

        foreach ($this->readCollection('users') as $row) {
            if (mb_strtolower((string) ($row['email'] ?? '')) === mb_strtolower((string) $email)) {
                return AuthUser::fromArray($row);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return hash_equals($user->getAuthPassword(), (string) ($credentials['password'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // no-op: mock passwords are not hashed
    }

    /** Convenience for tests/demo seeding — first user with the given role. */
    public function firstWhereRole(string $role): ?AuthUser
    {
        foreach ($this->readCollection('users') as $row) {
            if (($row['role'] ?? null) === $role) {
                return AuthUser::fromArray($row);
            }
        }

        return null;
    }
}
