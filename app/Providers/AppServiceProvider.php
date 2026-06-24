<?php

declare(strict_types=1);

namespace App\Providers;

use App\DataObjects\AuthUser;
use App\DataProviders\MockUserProvider;
use App\Support\Auth\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The current request's data-visibility scope, derived from the logged-in
        // user. Bound per-resolution so it always reflects the active user.
        $this->app->bind(Scope::class, static function (): Scope {
            $user = Auth::user();

            return Scope::forUser($user instanceof AuthUser ? $user : null);
        });
    }

    public function boot(): void
    {
        // Register the mock (JSON-backed) user provider for the session guard.
        Auth::provider('mock', static fn ($app, array $config): MockUserProvider => new MockUserProvider());
    }
}
