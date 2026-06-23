<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\AssetDataProvider;
use App\DataObjects\AssetData;
use App\Services\AssetService;
use App\Support\Filtering\AssetFilter;
use Tests\TestCase;

/**
 * Validates search + filter semantics (BR-SR-*, BR-FL-*) through AssetService.
 * Assertions are structural (derived from the dataset), so they hold as the data
 * grows: each returned asset must satisfy the filter; the unfiltered call returns
 * every asset.
 */
final class AssetFilterTest extends TestCase
{
    private AssetService $assets;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assets = $this->app->make(AssetService::class);
    }

    private function totalAssets(): int
    {
        return count($this->app->make(AssetDataProvider::class)->assets());
    }

    public function test_empty_filter_returns_all(): void
    {
        $this->assertCount($this->totalAssets(), $this->assets->list(new AssetFilter()));
        $this->assertSame($this->totalAssets(), $this->assets->resultCount(new AssetFilter()));
    }

    public function test_zone_filter_returns_only_that_zone(): void
    {
        $result = $this->assets->list(new AssetFilter(zoneId: 'ZONE-SLM-N'));
        $this->assertNotEmpty($result);
        foreach ($result as $asset) {
            $this->assertSame('ZONE-SLM-N', $asset->zoneId);
        }
    }

    public function test_panchayat_filter_returns_only_that_panchayat(): void
    {
        $result = $this->assets->list(new AssetFilter(panchayatId: 'PAN-ERU'));
        $this->assertNotEmpty($result);
        foreach ($result as $asset) {
            $this->assertSame('PAN-ERU', $asset->panchayatId);
        }
    }

    public function test_category_filter_returns_only_that_category(): void
    {
        $result = $this->assets->list(new AssetFilter(categoryId: 'CAT-EDU'));
        $this->assertNotEmpty($result);
        foreach ($result as $asset) {
            $this->assertSame('CAT-EDU', $asset->categoryId);
        }
    }

    public function test_asset_type_filter(): void
    {
        $result = $this->assets->list(new AssetFilter(assetType: 'Primary School'));
        $this->assertNotEmpty($result);
        foreach ($result as $asset) {
            $this->assertSame('Primary School', $asset->assetType);
        }
    }

    public function test_status_filter_uses_computed_status(): void
    {
        // PUB-0002 has a null construction year -> always Unknown (BR-HL-04).
        $result = $this->assets->list(new AssetFilter(status: 'Unknown'));
        $this->assertNotEmpty($result);
        foreach ($result as $asset) {
            $this->assertSame('Unknown', $asset->lifecycle?->status->value);
        }
        $this->assertContains('PUB-0002', array_map(static fn (AssetData $a): string => $a->assetNumber, $result));
    }

    public function test_search_matches_name_or_number_case_insensitively(): void
    {
        $result = $this->assets->list(new AssetFilter(query: '  SCHOOL '));
        $this->assertNotEmpty($result);
        foreach ($result as $asset) {
            $haystack = mb_strtolower($asset->assetName.' '.$asset->assetNumber);
            $this->assertStringContainsString('school', $haystack);
        }
    }

    public function test_search_matches_asset_number(): void
    {
        $result = $this->assets->list(new AssetFilter(query: 'EDU-0001'));
        $this->assertNotEmpty($result);
        $this->assertContains('EDU-0001', array_map(static fn (AssetData $a): string => $a->assetNumber, $result));
    }

    public function test_filters_combine_with_and(): void
    {
        $result = $this->assets->list(new AssetFilter(panchayatId: 'PAN-ERU', categoryId: 'CAT-EDU'));
        $this->assertNotEmpty($result);
        foreach ($result as $asset) {
            $this->assertSame('PAN-ERU', $asset->panchayatId);
            $this->assertSame('CAT-EDU', $asset->categoryId);
        }
    }

    public function test_search_combines_with_filter_using_and(): void
    {
        $result = $this->assets->list(new AssetFilter(zoneId: 'ZONE-SLM-N', query: 'school'));
        $this->assertNotEmpty($result);
        foreach ($result as $asset) {
            $this->assertSame('ZONE-SLM-N', $asset->zoneId);
            $this->assertStringContainsString('school', mb_strtolower($asset->assetName.' '.$asset->assetNumber));
        }
    }

    public function test_no_match_returns_empty(): void
    {
        $this->assertSame([], $this->assets->list(new AssetFilter(query: 'zzz-nonexistent')));
    }

    public function test_detail_returns_enriched_asset_with_computed_status(): void
    {
        $asset = $this->assets->detail('AST-0017'); // PUB-0002, Unknown
        $this->assertNotNull($asset);
        $this->assertNotNull($asset->lifecycle);
        $this->assertSame('Unknown', $asset->lifecycle->status->value);
    }

    public function test_detail_returns_null_for_unknown_id(): void
    {
        $this->assertNull($this->assets->detail('AST-9999'));
    }
}
