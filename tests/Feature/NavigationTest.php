<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * End-to-end drill-down through the District-first hierarchy and into the Asset
 * List + Asset Detail (Sprint 1). No database; renders the real seed.
 */
final class NavigationTest extends TestCase
{
    public function test_district_list_renders_with_counts(): void
    {
        $this->get('/districts')
            ->assertOk()
            ->assertSee('Districts')
            ->assertSee('Salem');
    }

    public function test_drill_district_to_zones(): void
    {
        $this->get('/districts/DIST-SALEM/zones')
            ->assertOk()
            ->assertSee('North Zone')
            ->assertSee('South Zone');
    }

    public function test_drill_zone_to_panchayats(): void
    {
        $this->get('/zones/ZONE-SLM-N/panchayats')
            ->assertOk()
            ->assertSee('Erumapalayam Panchayat')
            ->assertSee('Ammapet Panchayat');
    }

    public function test_drill_panchayat_to_categories_shows_all_four(): void
    {
        $this->get('/panchayats/PAN-ERU/categories')
            ->assertOk()
            ->assertSee('Educational Assets')
            ->assertSee('Healthcare Assets')
            ->assertSee('Water Infrastructure')
            ->assertSee('Public Infrastructure');
    }

    public function test_asset_list_shows_all_assets(): void
    {
        $this->get('/assets')
            ->assertOk()
            ->assertSee('Asset List')
            ->assertSee('EDU-0001')
            ->assertSee('PUB-0002');
    }

    public function test_asset_list_filtered_by_context_via_query_string(): void
    {
        // Drill-down context arrives as query params bound to #[Url] props.
        $this->get('/assets?panchayatId=PAN-ERU&categoryId=CAT-EDU')
            ->assertOk()
            ->assertSee('EDU-0001')
            ->assertSee('EDU-0003')
            ->assertDontSee('WAT-0002'); // Bore well is not Educational
    }

    public function test_asset_detail_renders_all_groups(): void
    {
        $this->get('/assets/AST-0001')
            ->assertOk()
            ->assertSee('Government Primary School')
            ->assertSee('Administrative information')
            ->assertSee('Lifecycle')
            ->assertSee('Salem')          // district (no State)
            ->assertSee('Healthy');       // computed status
    }

    public function test_unknown_zone_redirects_to_districts(): void
    {
        $this->get('/zones/ZONE-DOES-NOT-EXIST/panchayats')
            ->assertRedirect('/districts');
    }

    public function test_unknown_asset_redirects_to_asset_list(): void
    {
        $this->get('/assets/AST-9999')
            ->assertRedirect('/assets');
    }
}
