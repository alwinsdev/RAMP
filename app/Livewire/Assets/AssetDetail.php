<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Asset Detail (SCR-06) — the leaf record consolidating administrative, asset,
 * location, lifecycle, and media information. An unknown asset id degrades to the
 * Asset List (BR-NV-09). The dedicated Photos / Location (Google Maps) / Lifecycle
 * sub-views arrive in Sprint 2; this screen shows each group inline.
 */
#[Layout('layouts.app')]
final class AssetDetail extends Component
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

        return view('livewire.assets.asset-detail', [
            'asset' => $asset,
            'breadcrumbs' => $crumbs->build(
                $asset?->context() ?? [],
                [['label' => $asset?->assetName ?? 'Asset', 'url' => null]],
            ),
        ]);
    }
}
