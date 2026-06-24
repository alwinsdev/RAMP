<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The redesigned Dashboard (CR-04 hierarchy-first navigation + CR-09 layout).
 * Reconciliation of counts is covered by AggregationTest.
 */
final class DashboardTest extends TestCase
{
    public function test_dashboard_is_the_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Total Assets')
            ->assertSee('Lifecycle health')
            ->assertSee('Recent assets');
    }

    public function test_dashboard_shows_district_cards_and_distribution(): void
    {
        $this->get('/')
            ->assertSee('Salem')
            ->assertSee('Erode')
            ->assertSee('Primary Schools');   // category distribution
    }

    /** CR-04: hierarchy KPIs enter the hierarchy at Districts, never the Asset List. */
    public function test_hierarchy_kpis_drill_into_districts(): void
    {
        $this->get('/')->assertSee(route('districts'));
    }

    /** CR-04: no dashboard card drills straight to a zone/panchayat filtered Asset List. */
    public function test_dashboard_does_not_link_breakdowns_to_asset_list(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('/assets?zoneId', $html);
        $this->assertStringNotContainsString('/assets?panchayatId', $html);
    }

    public function test_district_card_drills_into_its_zones(): void
    {
        $this->get('/')->assertSee('/districts/DIST-SALEM/zones');
    }

    /** Health status is the one approved cross-cutting drill into the Asset List. */
    public function test_status_kpi_drills_into_status_filtered_asset_list(): void
    {
        $this->get('/')
            ->assertSee('/assets?status=Healthy')
            ->assertSee('/assets?status=Unknown');
    }

    public function test_recent_assets_link_to_the_asset_detail(): void
    {
        // The recent list links each row to its Asset Information page (not the list).
        $this->get('/')->assertSee('/assets/AST-');
    }

    public function test_dashboard_respects_role_scope(): void
    {
        // A panchayat officer sees their district card only — and not Erode.
        $this->actingAsRole('panchayat_officer');
        $this->get('/')
            ->assertOk()
            ->assertSee('Salem')
            ->assertDontSee('Erode');
    }
}
