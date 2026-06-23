<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Assets\AssetList;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Security hardening checks: baseline OWASP response headers, route-parameter
 * constraints, and the filter-removal whitelist (defence in depth).
 */
final class SecurityTest extends TestCase
{
    public function test_baseline_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy');
    }

    public function test_content_security_policy_locks_down_origins(): void
    {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
    }

    public function test_malformed_route_parameters_are_rejected(): void
    {
        // Characters outside the id pattern never reach a component — they 404.
        $this->get('/zones/'.urlencode('zone!<script>').'/panchayats')->assertNotFound();
        $this->get('/assets/'.str_repeat('a', 60))->assertNotFound();
    }

    public function test_remove_filter_ignores_non_whitelisted_keys(): void
    {
        Livewire::test(AssetList::class)
            ->set('categoryId', 'CAT-EDU')
            ->call('removeFilter', 'notARealFilter')   // must be a safe no-op
            ->assertSet('categoryId', 'CAT-EDU')        // unchanged
            ->call('removeFilter', 'categoryId')        // whitelisted -> cleared
            ->assertSet('categoryId', '');
    }
}
