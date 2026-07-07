<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Location / Map sub-view (SCR-08). Renders a Leaflet + OpenStreetMap pin at the
 * asset's coordinates, or a graceful "location unavailable" state when they are
 * missing or invalid (BR-LO-02/03). Returns to Asset Detail (BR-NV-07).
 */
#[Layout('layouts.app')]
final class LocationView extends Component
{
    public string $assetId = '';

    public function mount(string $asset, AssetService $assets): void
    {
        if ($assets->detail($asset) === null) {
            session()->flash('notice', 'That asset could not be found.');
            $this->redirect(route('assets'), navigate: true);

            return;
        }

        $this->assetId = $asset;
    }

    public function render(AssetService $assets, BreadcrumbBuilder $crumbs)
    {
        $asset = $assets->detail($this->assetId);

        return view('livewire.assets.location-view', [
            'asset' => $asset,
            'breadcrumbs' => $crumbs->build($asset?->context() ?? [], [
                ['label' => $asset?->assetName ?? 'Asset', 'url' => route('assets.show', ['asset' => $this->assetId])],
                ['label' => 'Location', 'url' => null],
            ]),
        ]);
    }
}
