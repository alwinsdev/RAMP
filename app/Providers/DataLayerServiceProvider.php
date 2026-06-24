<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\AssetDataProvider;
use App\Contracts\DashboardDataProvider;
use App\DataProviders\MockAssetProvider;
use App\DataProviders\MockDashboardProvider;
use App\Support\Lifecycle\LifecycleCalculator;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

/**
 * The composition root for the data layer (ARCHITECTURE_RULES SL-04 / TD-13).
 *
 * This is the ONE place provider selection happens. Flipping config('ramp.data_provider')
 * from 'mock' to 'eloquent' (Phase 2+) swaps the data source for the whole app with
 * zero changes to Services, Livewire components, or Blade views.
 *
 * Also binds the single shared LifecycleCalculator with the configured threshold,
 * so the near-expiry boundary is defined exactly once (BR-PR-03).
 */
final class DataLayerServiceProvider extends ServiceProvider
{
    /**
     * Map of provider key => [contract => concrete] bindings.
     *
     * @var array<string, array<class-string, class-string>>
     */
    private const PROVIDERS = [
        'mock' => [
            AssetDataProvider::class => MockAssetProvider::class,
            DashboardDataProvider::class => MockDashboardProvider::class,
        ],
        // 'eloquent' => [ AssetDataProvider::class => EloquentAssetProvider::class, ... ]  // Phase 2+
    ];

    public function register(): void
    {
        $this->bindDataProviders();
        $this->bindLifecycleCalculator();
    }

    private function bindDataProviders(): void
    {
        $key = (string) config('ramp.data_provider', 'mock');

        if (! isset(self::PROVIDERS[$key])) {
            throw new InvalidArgumentException(
                "Unknown RAMP data provider [{$key}]. Supported: ".implode(', ', array_keys(self::PROVIDERS)).'.',
            );
        }

        foreach (self::PROVIDERS[$key] as $contract => $concrete) {
            $this->app->singleton($contract, $concrete);
        }
    }

    private function bindLifecycleCalculator(): void
    {
        $this->app->singleton(
            LifecycleCalculator::class,
            static fn (): LifecycleCalculator => new LifecycleCalculator(
                expectedLife: (int) config('ramp.lifecycle.expected_life', LifecycleCalculator::DEFAULT_EXPECTED_LIFE),
                nearExpiryYears: (int) config('ramp.lifecycle.near_expiry_years', LifecycleCalculator::DEFAULT_NEAR_EXPIRY_YEARS),
            ),
        );
    }
}
