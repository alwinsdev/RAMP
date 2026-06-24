<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Auth\Login;
use App\Services\AssetService;
use App\Services\DashboardService;
use App\Support\Filtering\AssetFilter;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Mock authentication + role-based data visibility (CR-01).
 */
final class AuthTest extends TestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        Auth::logout();
        $this->get('/')->assertRedirect('/login');
        $this->get('/assets')->assertRedirect('/login');
    }

    public function test_login_screen_renders(): void
    {
        Auth::logout();
        $this->get('/login')->assertOk()->assertSee('Welcome back');
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        Auth::logout();

        Livewire::test(Login::class)
            ->set('email', 'admin@ramp.gov.in')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertTrue(Auth::check());
        $this->assertSame('USR-ADMIN', Auth::id());
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        Auth::logout();

        Livewire::test(Login::class)
            ->set('email', 'admin@ramp.gov.in')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertFalse(Auth::check());
    }

    public function test_administrator_sees_all_assets(): void
    {
        $this->actingAsRole('administrator');
        $count = app(DashboardService::class)->summary()->totalAssets;
        $this->assertSame(100, $count);
    }

    public function test_district_officer_is_scoped_to_their_district(): void
    {
        $this->actingAsRole('district_officer'); // Salem

        $assets = app(AssetService::class)->list(new AssetFilter());
        $this->assertNotEmpty($assets);
        foreach ($assets as $asset) {
            $this->assertSame('DIST-SALEM', $asset->districtId);
        }

        // Fewer than the full dataset, and only their district visible.
        $summary = app(DashboardService::class)->summary();
        $this->assertLessThan(100, $summary->totalAssets);
        $this->assertSame(1, count(app(AssetService::class)->districts()));

        // The hierarchy screen only shows Salem.
        $this->get('/districts')->assertOk()->assertSee('Salem')->assertDontSee('Erode');
    }

    public function test_panchayat_officer_is_scoped_to_their_panchayat(): void
    {
        $this->actingAsRole('panchayat_officer'); // Erumapalayam (PAN-ERU)

        $assets = app(AssetService::class)->list(new AssetFilter());
        $this->assertNotEmpty($assets);
        foreach ($assets as $asset) {
            $this->assertSame('PAN-ERU', $asset->panchayatId);
        }
    }

    public function test_officer_cannot_open_an_asset_outside_their_scope(): void
    {
        // An Erode asset (Chithode/Perundurai) is invisible to the Salem district officer.
        $this->actingAsRole('district_officer');

        // Find an Erode asset id as the administrator first.
        $this->actingAsRole('administrator');
        $erode = collect(app(AssetService::class)->list(new AssetFilter(districtId: 'DIST-ERODE')))->first();
        $this->assertNotNull($erode);

        $this->actingAsRole('district_officer');
        $this->assertNull(app(AssetService::class)->detail($erode->id));
        $this->get('/assets/'.$erode->id)->assertRedirect('/assets');
    }
}
