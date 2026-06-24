<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DataObjects\CategorySummary;
use App\Services\CategoryService;
use Tests\TestCase;

/**
 * Panchayat Category Dashboard (CR-05) — the 10 category cards with per-category
 * health, the panchayat roll-up + health score, and the filtered-list drill-down.
 */
final class CategoryDashboardTest extends TestCase
{
    private function summaries(string $panchayat = 'PAN-ERU'): array
    {
        return $this->app->make(CategoryService::class)->summariesForPanchayat($panchayat);
    }

    public function test_returns_all_ten_categories_even_when_zero(): void
    {
        $summaries = $this->summaries();
        $this->assertCount(10, $summaries);

        // Includes a zero-count category (e.g. Toilet Buildings in this panchayat).
        $hasZero = collect($summaries)->contains(fn (CategorySummary $s): bool => $s->total === 0);
        $this->assertTrue($hasZero, 'Zero-count categories must still be shown (BR-CT-04).');
    }

    public function test_per_category_health_reconciles_to_category_total(): void
    {
        foreach ($this->summaries() as $summary) {
            $this->assertSame($summary->total, $summary->health->total());
        }
    }

    public function test_category_totals_reconcile_to_panchayat_total(): void
    {
        $service = $this->app->make(CategoryService::class);
        $summaries = $service->summariesForPanchayat('PAN-ERU');
        $rollup = $service->panchayatHealth('PAN-ERU');

        $sum = array_sum(array_map(static fn (CategorySummary $s): int => $s->total, $summaries));
        $this->assertSame($rollup->total(), $sum);
    }

    public function test_health_score_is_within_bounds(): void
    {
        $score = $this->app->make(CategoryService::class)->panchayatHealth('PAN-ERU')->healthScore();
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_screen_renders_ten_cards_with_health_and_drill_links(): void
    {
        $response = $this->get('/panchayats/PAN-ERU/categories')->assertOk();

        // All ten category names (incl. the full OHT/UGT labels).
        foreach (['Primary Schools', 'Nursery Schools', 'Play Schools', 'Toilet Buildings',
            'Overhead Water Tanks (OHT)', 'Underground Water Tanks (UGT)', 'Ration Shops',
            'Panchayat Offices', 'Function Halls', 'Bore Wells'] as $name) {
            $response->assertSee($name);
        }

        // Roll-up + health score present.
        $response->assertSee('Health score')->assertSee('Total assets');

        // Cards drill into the filtered Asset List (panchayat + category).
        $response->assertSee('/assets?panchayatId=PAN-ERU&categoryId=CAT-PRI');
    }

    public function test_category_dashboard_respects_role_scope(): void
    {
        // A district officer (Salem) can view a Salem panchayat's category dashboard.
        $this->actingAsRole('district_officer');
        $this->get('/panchayats/PAN-ERU/categories')->assertOk()->assertSee('Primary Schools');

        // An Erode panchayat is out of a Salem officer's scope -> empty category data.
        $erodeSummaries = $this->app->make(CategoryService::class)->summariesForPanchayat('PAN-CHI');
        $this->assertSame(0, array_sum(array_map(static fn (CategorySummary $s): int => $s->total, $erodeSummaries)));
    }
}
