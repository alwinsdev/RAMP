<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * CR-07 (Asset Information + Location experience) and CR-10 (friendly language).
 */
final class AssetInformationTest extends TestCase
{
    public function test_asset_information_uses_friendly_labels_and_fields(): void
    {
        $this->get('/assets/AST-0001')
            ->assertOk()
            ->assertSee('Asset Information')   // friendly title (CR-10)
            ->assertSee('Asset health')        // renamed from "Lifecycle"
            ->assertSee('Asset Age')           // renamed from "Current Age"
            ->assertSee('Health Status')
            ->assertSee('District')
            ->assertSee('Zone')
            ->assertSee('Panchayat');
    }

    public function test_asset_information_has_quick_actions_and_progress_bar(): void
    {
        $this->get('/assets/AST-0001')
            ->assertSee('View on Map')
            ->assertSee('View Photos')
            ->assertSee('Years Used')          // lifecycle progress indicator
            ->assertSee('Lifecycle progress');
    }

    public function test_asset_information_embeds_location_and_photos(): void
    {
        $response = $this->get('/assets/AST-0001');

        // Location card links to the full map screen; coordinates shown.
        $response->assertSee('/assets/AST-0001/location')
            ->assertSee('11.6643');

        // Photos render as a clickable grid (Front view caption from the seed).
        $response->assertSee('Front view');
    }

    public function test_full_map_screen_has_info_panel_and_map_actions(): void
    {
        $this->get('/assets/AST-0001/location')
            ->assertOk()
            ->assertSee('Back to information')
            ->assertSee('Directions')
            ->assertSee('Open in Google Maps')
            ->assertSee('Copy Coordinates')
            ->assertSee('google.com/maps/dir');   // directions deep link
    }

    public function test_asset_health_screen_renamed_with_progress(): void
    {
        $this->get('/assets/AST-0001/lifecycle')   // route path unchanged (labels-only rename)
            ->assertOk()
            ->assertSee('Asset Health')
            ->assertSee('Asset Age')
            ->assertSee('Years Used');
    }

    public function test_location_actions_hidden_when_coordinates_missing(): void
    {
        // AST-0006 has null coordinates -> no Directions / Copy buttons.
        $this->get('/assets/AST-0006/location')
            ->assertOk()
            ->assertSee('Location unavailable')
            ->assertDontSee('Copy Coordinates');
    }
}
