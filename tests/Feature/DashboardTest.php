<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The Dashboard landing screen (Sprint 3): KPIs, health summary, breakdowns, and
 * drill-down shortcuts into the filtered Asset List (BR-NV-06). Reconciliation of
 * the underlying counts is covered by AggregationTest.
 */
final class DashboardTest extends TestCase
{
    public function test_dashboard_is_the_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Total Assets')
            ->assertSee('Asset health distribution');
    }

    public function test_dashboard_shows_breakdowns(): void
    {
        $this->get('/')
            ->assertSee('North Zone')
            ->assertSee('Erumapalayam Panchayat')
            ->assertSee('Educational Assets');
    }

    public function test_total_assets_kpi_links_to_asset_list(): void
    {
        $this->get('/')->assertSee(route('assets'));
    }

    public function test_health_card_drills_into_status_filter(): void
    {
        // The Unknown card links to the asset list filtered by status.
        $this->get('/')->assertSee('/assets?status=Unknown');
    }

    public function test_zone_breakdown_row_drills_into_zone_filter(): void
    {
        $this->get('/')->assertSee('/assets?zoneId=ZONE-SLM-N');
    }
}
