<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * QA sweep mapping to acceptance criteria in docs/03 that aren't already covered
 * explicitly elsewhere (breadcrumb path, zero-count categories, drill-down link
 * chain). Lifecycle boundaries, aggregation reconciliation, and search/filter
 * semantics are covered by the unit + component tests.
 */
final class AcceptanceCriteriaTest extends TestCase
{
    /** AC-NAV-02: the breadcrumb at the Asset List reflects District → Zone → Panchayat → Category. */
    public function test_asset_list_breadcrumb_reflects_full_context_path(): void
    {
        $this->get('/assets?panchayatId=PAN-ERU&categoryId=CAT-PRI')
            ->assertOk()
            ->assertSee('Salem')
            ->assertSee('North Zone')
            ->assertSee('Erumapalayam Panchayat')
            ->assertSee('Primary Schools');
    }

    /** AC-NAV-01: the full drill-down chain is navigable purely via on-screen links. */
    public function test_drilldown_links_chain_district_to_asset(): void
    {
        $this->get('/districts')->assertSee('/districts/DIST-SALEM/zones');
        $this->get('/districts/DIST-SALEM/zones')->assertSee('/zones/ZONE-SLM-N/panchayats');
        $this->get('/zones/ZONE-SLM-N/panchayats')->assertSee('/panchayats/PAN-ERU/categories');
        $this->get('/panchayats/PAN-ERU/categories')->assertSee('panchayatId=PAN-ERU');
    }

    /** BR-CT-04 / VR-CAT-01: a panchayat with no assets still lists all four categories (count 0). */
    public function test_zero_count_categories_are_still_shown(): void
    {
        // Veerapandi Panchayat (PAN-VEE) has no assets in the seed.
        $this->get('/panchayats/PAN-VEE/categories')
            ->assertOk()
            ->assertSee('Primary Schools')
            ->assertSee('Toilet Buildings')
            ->assertSee('Ration Shops')
            ->assertSee('Function Halls')
            ->assertSee('Bore Wells');
    }

    /** AC-ASST-04: from the detail the user can reach Photos, Location and Lifecycle. */
    public function test_asset_detail_links_to_all_subviews(): void
    {
        $this->get('/assets/AST-0001')
            ->assertSee('/assets/AST-0001/photos')
            ->assertSee('/assets/AST-0001/location')
            ->assertSee('/assets/AST-0001/lifecycle');
    }

    /** Sub-views return to their parent detail (BR-NV-07). */
    public function test_subviews_link_back_to_detail(): void
    {
        $this->get('/assets/AST-0001/photos')->assertSee('/assets/AST-0001');
        $this->get('/assets/AST-0001/lifecycle')->assertSee('/assets/AST-0001');
    }
}
