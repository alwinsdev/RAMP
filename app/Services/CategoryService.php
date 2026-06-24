<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\AssetDataProvider;
use App\DataObjects\AssetData;
use App\DataObjects\CategoryData;
use App\DataObjects\CategorySummary;
use App\DataObjects\HealthSummary;
use App\Enums\LifecycleStatus;
use App\Support\Auth\Scope;
use App\Support\Lifecycle\LifecycleCalculator;

/**
 * Category taxonomy + derived per-category asset counts and health summaries.
 *
 * Counts/health are computed over the live, role-scoped dataset (BR-DI-05) via the
 * shared LifecycleCalculator (BR-LC-05). Every category is returned even when it has
 * zero assets (BR-CT-04), and per-category totals reconcile to the panchayat total
 * (BR-CT-03).
 */
final class CategoryService
{
    public function __construct(
        private readonly AssetDataProvider $provider,
        private readonly Scope $scope,
        private readonly LifecycleCalculator $lifecycle,
    ) {
    }

    /**
     * All categories, each carrying its derived asset count.
     *
     * @return array<int, CategoryData>
     */
    public function withCounts(): array
    {
        $counts = [];
        foreach ($this->provider->assets() as $asset) {
            if (! $this->scope->allowsAsset($asset)) {
                continue;
            }
            $counts[$asset->categoryId] = ($counts[$asset->categoryId] ?? 0) + 1;
        }

        return array_map(
            static fn (CategoryData $category): CategoryData => $category->withAssetCount($counts[$category->id] ?? 0),
            $this->provider->categories(),
        );
    }

    /**
     * The 10 category cards for a panchayat (CR-05): each with its asset total and
     * Healthy / Near Expiry / Expired / Unknown health breakdown. Role-scoped; every
     * category is returned (zero-count included), in the canonical taxonomy order.
     *
     * @return array<int, CategorySummary>
     */
    public function summariesForPanchayat(string $panchayatId): array
    {
        $assetsByCategory = $this->scopedPanchayatAssetsByCategory($panchayatId);

        return array_map(
            fn (CategoryData $category): CategorySummary => new CategorySummary(
                id: $category->id,
                name: $category->name,
                description: $category->description,
                total: count($assetsByCategory[$category->id] ?? []),
                health: $this->healthOf($assetsByCategory[$category->id] ?? []),
            ),
            $this->provider->categories(),
        );
    }

    /** Panchayat-wide health roll-up (all categories combined), role-scoped. */
    public function panchayatHealth(string $panchayatId): HealthSummary
    {
        $all = array_merge(...array_values($this->scopedPanchayatAssetsByCategory($panchayatId))) ?: [];

        return $this->healthOf($all);
    }

    /**
     * Assets in a panchayat (role-scoped) grouped by category id.
     *
     * @return array<string, array<int, AssetData>>
     */
    private function scopedPanchayatAssetsByCategory(string $panchayatId): array
    {
        $grouped = [];
        foreach ($this->provider->assets() as $asset) {
            if ($asset->panchayatId !== $panchayatId || ! $this->scope->allowsAsset($asset)) {
                continue;
            }
            $grouped[$asset->categoryId][] = $asset;
        }

        return $grouped;
    }

    /**
     * Compute a HealthSummary for a set of assets via the shared lifecycle engine.
     *
     * @param  array<int, AssetData>  $assets
     */
    private function healthOf(array $assets): HealthSummary
    {
        $tally = [
            LifecycleStatus::Healthy->value => 0,
            LifecycleStatus::NearExpiry->value => 0,
            LifecycleStatus::Expired->value => 0,
            LifecycleStatus::Unknown->value => 0,
        ];

        foreach ($assets as $asset) {
            $tally[$this->lifecycle->compute($asset->constructionYear)->status->value]++;
        }

        return new HealthSummary(
            healthy: $tally[LifecycleStatus::Healthy->value],
            nearExpiry: $tally[LifecycleStatus::NearExpiry->value],
            expired: $tally[LifecycleStatus::Expired->value],
            unknown: $tally[LifecycleStatus::Unknown->value],
        );
    }
}
