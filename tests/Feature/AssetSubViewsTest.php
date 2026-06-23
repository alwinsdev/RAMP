<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Asset Detail sub-views (Sprint 2): Photo Gallery, Location (Google Maps), and
 * Lifecycle. Covers happy paths plus the edge states (no photos, missing
 * coordinates, unknown lifecycle) and the return-to-detail affordance.
 */
final class AssetSubViewsTest extends TestCase
{
    // ---- Photo Gallery ----

    public function test_photo_gallery_renders_photos(): void
    {
        $this->get('/assets/AST-0001/photos')      // 3 embedded photos
            ->assertOk()
            ->assertSee('Photo Gallery')
            ->assertSee('Front view')
            ->assertSee('Back to detail');
    }

    public function test_photo_gallery_empty_state(): void
    {
        $this->get('/assets/AST-0003/photos')      // no photos
            ->assertOk()
            ->assertSee('No photos available');
    }

    // ---- Location ----

    public function test_location_with_valid_coordinates(): void
    {
        // No API key configured in tests -> graceful coordinate preview, coords shown.
        $this->get('/assets/AST-0001/location')
            ->assertOk()
            ->assertSee('Location')
            ->assertSee('12 School Road, Erumapalayam, Salem, Tamil Nadu')
            ->assertSee('11.6643');
    }

    public function test_location_unavailable_when_coordinates_missing(): void
    {
        $this->get('/assets/AST-0017/location')    // PUB-0002 has null coordinates
            ->assertOk()
            ->assertSee('Location unavailable');
    }

    // ---- Lifecycle ----

    public function test_lifecycle_view_renders_figures_and_status(): void
    {
        $this->get('/assets/AST-0001/lifecycle')   // 2010 / 30 yr -> Healthy
            ->assertOk()
            ->assertSee('Lifecycle Monitoring')
            ->assertSee('Construction Year')
            ->assertSee('2010')
            ->assertSee('Healthy');
    }

    public function test_lifecycle_view_handles_unknown(): void
    {
        $this->get('/assets/AST-0017/lifecycle')   // null construction year -> Unknown
            ->assertOk()
            ->assertSee('Unknown')
            ->assertSee('missing or invalid');
    }

    // ---- Graceful degradation ----

    public function test_unknown_asset_subview_redirects_to_asset_list(): void
    {
        $this->get('/assets/AST-9999/photos')->assertRedirect('/assets');
        $this->get('/assets/AST-9999/location')->assertRedirect('/assets');
        $this->get('/assets/AST-9999/lifecycle')->assertRedirect('/assets');
    }
}
