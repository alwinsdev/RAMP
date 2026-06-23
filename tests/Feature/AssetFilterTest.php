<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\AssetService;
use App\Support\Filtering\AssetFilter;
use Tests\TestCase;

/**
 * Validates search + filter semantics (BR-SR-*, BR-FL-*) through AssetService over
 * the seed dataset. Assertions are time-independent (no health-status-by-year
 * dependence except the always-Unknown asset).
 */
final class AssetFilterTest extends TestCase
{
    private AssetService $assets;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assets = $this->app->make(AssetService::class);
    }

    public function test_empty_filter_returns_all(): void
    {
        $this->assertCount(8, $this->assets->list(new AssetFilter()));
        $this->assertSame(8, $this->assets->resultCount(new AssetFilter()));
    }

    public function test_zone_filter(): void
    {
        $this->assertCount(6, $this->assets->list(new AssetFilter(zoneId: 'ZONE-SLM-N')));
    }

    public function test_panchayat_filter(): void
    {
        $this->assertCount(5, $this->assets->list(new AssetFilter(panchayatId: 'PAN-ERU')));
    }

    public function test_category_filter(): void
    {
        $this->assertCount(3, $this->assets->list(new AssetFilter(categoryId: 'CAT-EDU')));
    }

    public function test_asset_type_filter(): void
    {
        $this->assertCount(1, $this->assets->list(new AssetFilter(assetType: 'Primary School')));
    }

    public function test_status_filter_unknown_is_time_independent(): void
    {
        // PUB-0002 has a null construction year -> always Unknown (BR-HL-04).
        $result = $this->assets->list(new AssetFilter(status: 'Unknown'));
        $this->assertCount(1, $result);
        $this->assertSame('PUB-0002', $result[0]->assetNumber);
    }

    public function test_search_matches_name_case_insensitively_and_trimmed(): void
    {
        // "school" appears in EDU-0001/0002/0003 names (BR-SR-01/02/03/04).
        $this->assertCount(3, $this->assets->list(new AssetFilter(query: '  SCHOOL ')));
    }

    public function test_search_matches_asset_number(): void
    {
        $this->assertCount(3, $this->assets->list(new AssetFilter(query: 'EDU-000')));
    }

    public function test_filters_combine_with_and(): void
    {
        // Water Infrastructure AND Ammapet -> only WAT-0002 (BR-FL-02).
        $result = $this->assets->list(new AssetFilter(panchayatId: 'PAN-AMM', categoryId: 'CAT-WAT'));
        $this->assertCount(1, $result);
        $this->assertSame('WAT-0002', $result[0]->assetNumber);
    }

    public function test_search_combines_with_filter_using_and(): void
    {
        // North zone AND "school" -> the three educational assets, all in North (BR-SR-06).
        $this->assertCount(3, $this->assets->list(new AssetFilter(zoneId: 'ZONE-SLM-N', query: 'school')));
    }

    public function test_no_match_returns_empty(): void
    {
        $this->assertSame([], $this->assets->list(new AssetFilter(query: 'zzz-nonexistent')));
    }

    public function test_detail_returns_enriched_asset_with_computed_status(): void
    {
        $asset = $this->assets->detail('AST-0008'); // PUB-0002, Unknown
        $this->assertNotNull($asset);
        $this->assertNotNull($asset->lifecycle);
        $this->assertSame('Unknown', $asset->lifecycle->status->value);
    }

    public function test_detail_returns_null_for_unknown_id(): void
    {
        $this->assertNull($this->assets->detail('AST-9999'));
    }
}
