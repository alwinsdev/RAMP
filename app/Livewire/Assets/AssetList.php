<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use App\Support\Filtering\AssetFilter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * The Asset List — the convergence screen (SCR-05, BR-NV-10). Reached by full
 * drill-down, by dashboard shortcuts, and by search; it renders identically
 * regardless of entry path.
 *
 * Filter + search context lives in #[Url] properties so the view is shareable,
 * reversible, and survives refresh. All filtering/search runs through AssetService
 * (AND across filters, case-insensitive substring search) — never in this class.
 */
#[Layout('layouts.app')]
#[Title('Asset List — RAMP')]
final class AssetList extends Component
{
    #[Url] public string $districtId = '';
    #[Url] public string $zoneId = '';
    #[Url] public string $panchayatId = '';
    #[Url] public string $categoryId = '';
    #[Url] public string $assetType = '';
    #[Url] public string $status = '';
    #[Url] public string $q = '';

    /** The only properties a user is allowed to clear/reset (defence in depth). */
    private const FILTERABLE = ['districtId', 'zoneId', 'panchayatId', 'categoryId', 'assetType', 'status', 'q'];

    /** When the zone changes, drop any now-incompatible panchayat selection (BR-FL-04). */
    public function updatedZoneId(): void
    {
        $this->panchayatId = '';
    }

    /** When the category changes, drop any now-incompatible asset-type selection. */
    public function updatedCategoryId(): void
    {
        $this->assetType = '';
    }

    public function removeFilter(string $key): void
    {
        // Whitelist: only declared filter properties may be cleared, never arbitrary
        // (inherited/internal) component state reachable via property_exists().
        if (in_array($key, self::FILTERABLE, true)) {
            $this->{$key} = '';
        }
    }

    public function resetFilters(): void
    {
        $this->reset(self::FILTERABLE);
    }

    public function render(AssetService $assets, BreadcrumbBuilder $crumbs)
    {
        $filter = AssetFilter::fromArray([
            'districtId' => $this->districtId,
            'zoneId' => $this->zoneId,
            'panchayatId' => $this->panchayatId,
            'categoryId' => $this->categoryId,
            'assetType' => $this->assetType,
            'status' => $this->status,
            'query' => $this->q,
        ]);

        $results = $assets->list($filter);

        return view('livewire.assets.asset-list', [
            'assets' => $results,
            'resultCount' => count($results),
            'activeFilters' => $this->activeFilters($assets),
            'filterOptions' => $this->filterOptions($assets),
            'breadcrumbs' => $crumbs->build(
                [
                    'districtId' => $this->districtId ?: null,
                    'zoneId' => $this->zoneId ?: null,
                    'panchayatId' => $this->panchayatId ?: null,
                    'categoryId' => $this->categoryId ?: null,
                ],
                // When no category context exists, label the current page explicitly.
                $this->categoryId === '' ? [['label' => 'Asset List', 'url' => null]] : [],
            ),
        ]);
    }

    /**
     * Option lists for the filter selects. Panchayats are constrained by the
     * selected zone (BR-FL-04); asset types are constrained by the selected category.
     *
     * @return array{zones:array, panchayats:array, categories:array, types:array<int,string>, statuses:array<int,string>}
     */
    private function filterOptions(AssetService $assets): array
    {
        // Asset types: scoped to the selected category, else the union of all sub-types.
        if ($this->categoryId !== '') {
            $types = $assets->categoryById($this->categoryId)?->subTypes ?? [];
        } else {
            $types = [];
            foreach ($assets->categories() as $category) {
                $types = [...$types, ...$category->subTypes];
            }
            $types = array_values(array_unique($types));
        }

        return [
            'zones' => $assets->zones(),
            'panchayats' => $this->zoneId !== '' ? $assets->panchayats($this->zoneId) : $assets->panchayats(),
            'categories' => $assets->categories(),
            'types' => $types,
            'statuses' => array_map(static fn ($s) => $s->value, \App\Enums\LifecycleStatus::displayOrder()),
        ];
    }

    /**
     * Active filters as removable chips (BR-FL-06), resolved to human labels.
     *
     * @return array<int, array{key:string, label:string, value:string}>
     */
    private function activeFilters(AssetService $assets): array
    {
        $chips = [];

        if ($this->districtId !== '') {
            $chips[] = ['key' => 'districtId', 'label' => 'District', 'value' => $assets->districtById($this->districtId)?->name ?? $this->districtId];
        }
        if ($this->zoneId !== '') {
            $chips[] = ['key' => 'zoneId', 'label' => 'Zone', 'value' => $assets->zoneById($this->zoneId)?->name ?? $this->zoneId];
        }
        if ($this->panchayatId !== '') {
            $chips[] = ['key' => 'panchayatId', 'label' => 'Panchayat', 'value' => $assets->panchayatById($this->panchayatId)?->name ?? $this->panchayatId];
        }
        if ($this->categoryId !== '') {
            $chips[] = ['key' => 'categoryId', 'label' => 'Category', 'value' => $assets->categoryById($this->categoryId)?->name ?? $this->categoryId];
        }
        if ($this->assetType !== '') {
            $chips[] = ['key' => 'assetType', 'label' => 'Type', 'value' => $this->assetType];
        }
        if ($this->status !== '') {
            $chips[] = ['key' => 'status', 'label' => 'Status', 'value' => $this->status];
        }

        return $chips;
    }
}
