<?php

declare(strict_types=1);

namespace App\Livewire\Map;

use App\Enums\LifecycleStatus;
use App\Services\AssetService;
use App\Support\Filtering\AssetFilter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Asset Intelligence Map (flagship) — a full-screen interactive map of every
 * (role-scoped) asset, colour-coded by health, with clustering, a heatmap mode, and
 * filters. Selecting a district / zone / panchayat re-filters and auto-focuses the
 * map to that area. Marker data comes from AssetService (RBAC + lifecycle preserved);
 * the UI never touches JSON.
 */
#[Layout('layouts.app')]
#[Title('Asset Intelligence Map — RAMP')]
final class AssetIntelMap extends Component
{
    public string $districtId = '';
    public string $zoneId = '';
    public string $panchayatId = '';
    public string $categoryId = '';
    public string $status = '';

    private const FILTERS = ['districtId', 'zoneId', 'panchayatId', 'categoryId', 'status'];

    public function updatedDistrictId(): void
    {
        $this->zoneId = '';
        $this->panchayatId = '';
    }

    public function updatedZoneId(): void
    {
        $this->panchayatId = '';
    }

    /** Push the refreshed (scoped, filtered) marker set to the map after any change. */
    public function updated(string $property): void
    {
        if (in_array($property, self::FILTERS, true)) {
            $this->dispatch('intelmap-data', mapId: $this->getId(), markers: $this->markers(app(AssetService::class)));
        }
    }

    public function resetFilters(): void
    {
        $this->reset(self::FILTERS);
        $this->dispatch('intelmap-data', mapId: $this->getId(), markers: $this->markers(app(AssetService::class)));
    }

    private function filter(): AssetFilter
    {
        return AssetFilter::fromArray([
            'districtId' => $this->districtId,
            'zoneId' => $this->zoneId,
            'panchayatId' => $this->panchayatId,
            'categoryId' => $this->categoryId,
            'status' => $this->status,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function markers(AssetService $assets): array
    {
        return $assets->mapMarkers($this->filter());
    }

    public function render(AssetService $assets)
    {
        $markers = $this->markers($assets);

        return view('livewire.map.asset-intel-map', [
            'markers' => $markers,
            'mappedCount' => count($markers),
            'filterOptions' => [
                'districts' => $assets->districts(),
                'zones' => $this->districtId !== '' ? $assets->zones($this->districtId) : $assets->zones(),
                'panchayats' => $this->zoneId !== '' ? $assets->panchayats($this->zoneId) : $assets->panchayats(),
                'categories' => $assets->categories(),
                'statuses' => array_map(static fn (LifecycleStatus $s): string => $s->value, LifecycleStatus::displayOrder()),
            ],
        ]);
    }
}
