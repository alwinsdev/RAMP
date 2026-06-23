<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DataObjects\Breakdown;
use App\Services\DashboardService;
use Tests\TestCase;

/**
 * Validates DashboardService aggregation against the seed dataset expectations
 * (docs/08 §9) and the reconciliation rules (BR-CT-03, BR-HL-08). Assertions are
 * time-independent: structural counts plus reconciliation, and the always-Unknown
 * asset (PUB-0002 has a null construction year).
 */
final class AggregationTest extends TestCase
{
    private function summary()
    {
        return $this->app->make(DashboardService::class)->summary();
    }

    public function test_kpi_totals_match_seed(): void
    {
        $summary = $this->summary();

        $this->assertSame(8, $summary->totalAssets);
        $this->assertSame(4, $summary->totalCategories);
        $this->assertSame(5, $summary->totalZones);
        $this->assertSame(5, $summary->totalPanchayats);
    }

    public function test_zone_breakdown_counts_and_sorting(): void
    {
        $rows = $this->summary()->zoneBreakdown;

        $counts = $this->indexByName($rows);
        $this->assertSame(6, $counts['North Zone']);
        $this->assertSame(1, $counts['South Zone']);
        $this->assertSame(1, $counts['East Zone']);

        // Sorted by count desc -> North Zone leads (DB-11).
        $this->assertSame('North Zone', $rows[0]->name);

        // Zone-wise sum reconciles to total (AC-DASH-02).
        $this->assertSame(8, array_sum(array_map(static fn (Breakdown $b): int => $b->count, $rows)));
    }

    public function test_panchayat_breakdown_reconciles(): void
    {
        $rows = $this->summary()->panchayatBreakdown;
        $counts = $this->indexByName($rows);

        $this->assertSame(5, $counts['Erumapalayam Panchayat']);
        $this->assertSame(1, $counts['Ammapet Panchayat']);
        $this->assertSame(8, array_sum(array_map(static fn (Breakdown $b): int => $b->count, $rows)));
    }

    public function test_category_breakdown_reconciles_and_shows_all(): void
    {
        $rows = $this->summary()->categoryBreakdown;
        $counts = $this->indexByName($rows);

        $this->assertSame(3, $counts['Educational Assets']);
        $this->assertSame(1, $counts['Healthcare Assets']);
        $this->assertSame(2, $counts['Water Infrastructure']);
        $this->assertSame(2, $counts['Public Infrastructure']);

        // All four categories present; sum reconciles to total (BR-CT-03/04).
        $this->assertCount(4, $rows);
        $this->assertSame(8, array_sum(array_map(static fn (Breakdown $b): int => $b->count, $rows)));
    }

    public function test_health_summary_reconciles_and_excludes_unknown_from_base(): void
    {
        $health = $this->summary()->health;

        // Every asset lands in exactly one bucket (reconciliation).
        $this->assertSame(8, $health->total());

        // PUB-0002 (null construction year) is always Unknown, regardless of the year.
        $this->assertSame(1, $health->unknown);

        // Percentage base excludes Unknown (BR-HL-08).
        $this->assertSame(7, $health->healthCountedTotal());
    }

    /**
     * @param  array<int, Breakdown>  $rows
     * @return array<string, int>
     */
    private function indexByName(array $rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $map[$row->name] = $row->count;
        }

        return $map;
    }
}
