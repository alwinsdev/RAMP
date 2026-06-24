<?php

declare(strict_types=1);

namespace Tests;

use App\DataObjects\AuthUser;
use App\DataProviders\MockUserProvider;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Feature tests run as the Administrator by default (unrestricted scope), so the
     * existing assertions about counts and visibility hold. Auth/scope tests call
     * actingAsRole() to switch roles or assert guest behaviour explicitly.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsRole('administrator');
    }

    protected function actingAsRole(string $role): AuthUser
    {
        $user = app(MockUserProvider::class)->firstWhereRole($role);
        \assert($user !== null, "Mock user with role [{$role}] not found.");

        $this->actingAs($user);

        return $user;
    }
}
