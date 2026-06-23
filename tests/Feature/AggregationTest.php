<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\DashboardDataProvider;
use App\DataObjects\Breakdown;
use App\Services\DashboardService;
use Tests\TestCase;

/**
 * Validates DashboardService aggregation against the reconciliation rules
 * (BR-CT-03, BR-HL-08). Assertions are structural — derived from the live dataset
 * rather than hard-coded counts — so they hold as the dataset grows.
 */
final class AggregationTest extends TestCase
{
    private function summary()
    {
        return $this->app->make(DashboardService::class)->summary();
    }

    /** @return array<int, \App\DataObjects\AssetData> */
    private function rawAssets(): array
    {
        return $this->app->make(DashboardDataProvider::class)->allAssets();
    }

    public function test_kpi_totals_match_the_dataset(): void
    {
        $summary = $this->summary();
        $provider = $this->app->make(DashboardDataProvider::class);

        $this->assertSame(count($this->rawAssets()), $summary->totalAssets);
        $this->assertSame(4, $summary->totalCategories);
        $this->assertSame(count($provider->zones()), $summary->totalZones);
        $this->assertSame(count($provider->panchayats()), $summary->totalPanchayats);
        $this->assertGreaterThan(0, $summary->totalAssets);
    }

    public function test_zone_breakdown_sum_reconciles_to_total(): void
    {
        $summary = $this->summary();
        $this->assertSame(
            $summary->totalAssets,
            array_sum(array_map(static fn (Breakdown $b): int => $b->count, $summary->zoneBreakdown)),
        );
    }

    public function test_panchayat_breakdown_sum_reconciles_to_total(): void
    {
        $summary = $this->summary();
        $this->assertSame(
            $summary->totalAssets,
            array_sum(array_map(static fn (Breakdown $b): int => $b->count, $summary->panchayatBreakdown)),
        );
    }

    public function test_category_breakdown_shows_all_four_and_reconciles(): void
    {
        $summary = $this->summary();

        $this->assertCount(4, $summary->categoryBreakdown); // zero-count categories still shown (BR-CT-04)
        $this->assertSame(
            $summary->totalAssets,
            array_sum(array_map(static fn (Breakdown $b): int => $b->count, $summary->categoryBreakdown)),
        );
    }

    public function test_breakdowns_are_sorted_by_count_descending(): void
    {
        $counts = array_map(static fn (Breakdown $b): int => $b->count, $this->summary()->zoneBreakdown);
        $sorted = $counts;
        rsort($sorted);
        $this->assertSame($sorted, $counts, 'Zone breakdown should be sorted by count desc (DB-11).');
    }

    public function test_health_reconciles_and_excludes_unknown_from_base(): void
    {
        $health = $this->summary()->health;

        // Every asset lands in exactly one bucket.
        $this->assertSame(count($this->rawAssets()), $health->total());

        // Percentage base excludes Unknown (BR-HL-08).
        $this->assertSame($health->total() - $health->unknown, $health->healthCountedTotal());

        // The dataset deliberately includes at least one Unknown asset (MD-04).
        $this->assertGreaterThanOrEqual(1, $health->unknown);
    }
}
