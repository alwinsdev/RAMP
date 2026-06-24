<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Map\AssetIntelMap;
use App\Services\AssetService;
use App\Support\Filtering\AssetFilter;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Asset Intelligence Map (flagship) — the full-screen map screen, its live filters,
 * RBAC scoping of the marker set, and the removal of the retired Reports module.
 * Marker data flows Livewire → AssetService → Provider; the UI never reads JSON.
 */
final class MapTest extends TestCase
{
    public function test_map_screen_renders(): void
    {
        $this->get('/map')
            ->assertOk()
            ->assertSee('Asset Intelligence Map')
            ->assertSee('All districts')
            ->assertSee('All health statuses');
    }

    public function test_retired_reports_route_is_gone(): void
    {
        $this->get('/reports')->assertNotFound();
    }

    public function test_markers_only_include_assets_with_valid_coordinates(): void
    {
        $markers = app(AssetService::class)->mapMarkers(new AssetFilter());

        $this->assertNotEmpty($markers);
        foreach ($markers as $marker) {
            $this->assertIsNumeric($marker['lat']);
            $this->assertIsNumeric($marker['lng']);
        }

        // AST-0006 (Function Hall) has null coordinates and must not be plotted.
        $ids = array_column($markers, 'id');
        $this->assertContains('AST-0001', $ids);
        $this->assertNotContains('AST-0006', $ids);
    }

    public function test_each_marker_carries_its_computed_health_colour(): void
    {
        $markers = app(AssetService::class)->mapMarkers(new AssetFilter());
        $anchor = collect($markers)->firstWhere('id', 'AST-0001');

        $this->assertNotNull($anchor);
        $this->assertSame('Healthy', $anchor['status']);   // computed, never stored
        $this->assertArrayHasKey('color', $anchor);
        $this->assertArrayHasKey('remaining', $anchor);
    }

    public function test_category_filter_narrows_the_marker_set(): void
    {
        $service = app(AssetService::class);

        $all = $service->mapMarkers(new AssetFilter());
        $primary = $service->mapMarkers(AssetFilter::fromArray(['categoryId' => 'CAT-PRI']));

        $this->assertLessThan(count($all), count($primary));
        foreach ($primary as $marker) {
            $this->assertStringContainsString('School', $marker['category']);
        }
    }

    public function test_markers_are_scoped_to_a_panchayat_officer(): void
    {
        $this->actingAsRole('panchayat_officer'); // USR-PANC → PAN-ERU

        $markers = app(AssetService::class)->mapMarkers(new AssetFilter());

        $this->assertNotEmpty($markers);
        foreach ($markers as $marker) {
            $this->assertSame('Erumapalayam Panchayat', $marker['panchayat']);
        }
    }

    public function test_changing_district_clears_dependent_filters(): void
    {
        Livewire::test(AssetIntelMap::class)
            ->set('zoneId', 'ZONE-SLM-N')
            ->set('panchayatId', 'PAN-ERU')
            ->set('districtId', 'DIST-SALEM')
            ->assertSet('zoneId', '')
            ->assertSet('panchayatId', '');
    }

    public function test_filter_change_pushes_refreshed_markers_to_the_map(): void
    {
        Livewire::test(AssetIntelMap::class)
            ->set('categoryId', 'CAT-PRI')
            ->assertDispatched('intelmap-data');
    }

    public function test_reset_filters_clears_every_filter(): void
    {
        Livewire::test(AssetIntelMap::class)
            ->set('categoryId', 'CAT-PRI')
            ->set('status', 'Healthy')
            ->call('resetFilters')
            ->assertSet('categoryId', '')
            ->assertSet('status', '')
            ->assertDispatched('intelmap-data');
    }
}
