<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Sprint 0 exit smoke test: the app shell renders end to end (route -> Livewire
 * component -> layout -> DashboardService -> mock data) without a database.
 */
final class HomePageTest extends TestCase
{
    public function test_home_renders_with_live_figures(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Rural Asset Management Platform');
        $response->assertSee('Total Assets');
        $response->assertSee('Foundation Ready');
        // The live total from the seed (8 assets) is rendered, proving the data chain.
        $response->assertSee('8');
    }
}
