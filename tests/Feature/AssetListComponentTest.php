<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Assets\AssetList;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Interactive behaviour of the Asset List Livewire component: live search,
 * status filtering, chip removal, and reset (BR-SR-*, BR-FL-*).
 */
final class AssetListComponentTest extends TestCase
{
    public function test_renders_all_assets_by_default(): void
    {
        Livewire::test(AssetList::class)
            ->assertSee('PRI-0001')
            ->assertSee('BOR-0001')
            ->assertSee('FUN-0001');
    }

    public function test_live_search_filters_by_name(): void
    {
        Livewire::test(AssetList::class)
            ->set('q', 'bore')
            ->assertSee('BOR-0001')       // Public Bore Well #3, Ammapet
            ->assertDontSee('PRI-0001');
    }

    public function test_search_is_case_insensitive_and_trimmed(): void
    {
        Livewire::test(AssetList::class)
            ->set('q', '  SCHOOL ')
            ->assertSee('PRI-0001')
            ->assertSee('PRI-0002')
            ->assertDontSee('BOR-0001');
    }

    public function test_status_filter_uses_computed_status(): void
    {
        Livewire::test(AssetList::class)
            ->set('status', 'Unknown')
            ->assertSee('FUN-0001')
            ->assertDontSee('PRI-0001');
    }

    public function test_context_filters_combine_with_and(): void
    {
        Livewire::test(AssetList::class)
            ->set('panchayatId', 'PAN-ERU')
            ->set('categoryId', 'CAT-PRI')
            ->assertSee('PRI-0001')
            ->assertDontSee('BOR-0001');
    }

    public function test_reset_clears_all_filters(): void
    {
        Livewire::test(AssetList::class)
            ->set('categoryId', 'CAT-NUR')
            ->set('q', 'nursery')
            ->assertDontSee('PAN-0001')
            ->call('resetFilters')
            ->assertSet('categoryId', '')
            ->assertSet('q', '')
            ->assertSee('PAN-0001');
    }

    public function test_remove_single_filter_chip(): void
    {
        Livewire::test(AssetList::class)
            ->set('categoryId', 'CAT-OHT')
            ->assertDontSee('PRI-0001')
            ->call('removeFilter', 'categoryId')
            ->assertSet('categoryId', '')
            ->assertSee('PRI-0001');
    }

    public function test_no_results_shows_empty_state(): void
    {
        Livewire::test(AssetList::class)
            ->set('q', 'zzz-nonexistent')
            ->assertSee('No assets match');
    }

    public function test_filter_selects_render(): void
    {
        Livewire::test(AssetList::class)
            ->assertSee('All zones')
            ->assertSee('All panchayats')
            ->assertSee('All statuses');
    }

    public function test_changing_zone_clears_incompatible_panchayat(): void
    {
        // BR-FL-04: panchayat options are constrained by zone; changing zone clears it.
        Livewire::test(AssetList::class)
            ->set('panchayatId', 'PAN-ERU')
            ->set('zoneId', 'ZONE-SLM-S')
            ->assertSet('panchayatId', '');
    }

    public function test_changing_category_clears_incompatible_type(): void
    {
        Livewire::test(AssetList::class)
            ->set('assetType', 'Primary School')
            ->set('categoryId', 'CAT-WAT')
            ->assertSet('assetType', '');
    }
}
