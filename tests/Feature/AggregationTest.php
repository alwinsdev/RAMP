<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\DashboardDataProvider;
use App\DataObjects\Breakdown;
use App\DataObjects\DistrictSummary;
use App\Services\DashboardService;
use Tests\TestCase;

/**
 * Validates DashboardService aggregation against the reconciliation rules
 * (BR-CT-03, BR-HL-08) for the redesigned dashboard (CR-09). Structural assertions,
 * so they hold as the dataset grows.
 */
final class AggregationTest extends TestCase
{
    private function summary()
    {
        return $this->app->make(DashboardService::class)->summary();
    }

    private function rawAssetCount(): int
    {
        return count($this->app->make(DashboardDataProvider::class)->allAssets());
    }

    public function test_kpi_totals_match_the_dataset(): void
    {
        $summary = $this->summary();
        $provider = $this->app->make(DashboardDataProvider::class);

        $this->assertSame($this->rawAssetCount(), $summary->totalAssets);
        $this->assertSame(count($provider->districts()), $summary->totalDistricts);
        $this->assertSame(10, $summary->totalCategories);
        $this->assertSame(count($provider->zones()), $summary->totalZones);
        $this->assertSame(count($provider->panchayats()), $summary->totalPanchayats);
    }

    public function test_district_card_asset_counts_reconcile_to_total(): void
    {
        $summary = $this->summary();

        $this->assertNotEmpty($summary->districtCards);
        $this->assertSame(
            $summary->totalAssets,
            array_sum(array_map(static fn (DistrictSummary $d): int => $d->assetCount, $summary->districtCards)),
        );
    }

    public function test_district_cards_are_sorted_by_asset_count_desc(): void
    {
        $counts = array_map(static fn (DistrictSummary $d): int => $d->assetCount, $this->summary()->districtCards);
        $sorted = $counts;
        rsort($sorted);
        $this->assertSame($sorted, $counts);
    }

    public function test_category_distribution_shows_all_ten_and_reconciles(): void
    {
        $summary = $this->summary();

        $this->assertCount(10, $summary->categoryDistribution); // zero-count categories still shown (BR-CT-04)
        $this->assertSame(
            $summary->totalAssets,
            array_sum(array_map(static fn (Breakdown $b): int => $b->count, $summary->categoryDistribution)),
        );
    }

    public function test_recent_assets_are_limited_enriched_and_newest_first(): void
    {
        $recent = $this->summary()->recentAssets;

        $this->assertLessThanOrEqual(5, count($recent));
        $this->assertNotEmpty($recent);

        // Lifecycle is attached (computed centrally) and ordering is newest-first.
        $previous = null;
        foreach ($recent as $asset) {
            $this->assertNotNull($asset->lifecycle, 'Recent assets must carry the computed lifecycle.');
            if ($previous !== null) {
                $this->assertLessThanOrEqual($previous, $asset->createdAt);
            }
            $previous = $asset->createdAt;
        }
    }

    public function test_health_reconciles_and_excludes_unknown_from_base(): void
    {
        $health = $this->summary()->health;

        $this->assertSame($this->rawAssetCount(), $health->total());
        $this->assertSame($health->total() - $health->unknown, $health->healthCountedTotal());
        $this->assertGreaterThanOrEqual(1, $health->unknown);
    }
}
