<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Lifecycle sub-view (SCR-09). Presents the computed lifecycle figures, the status,
 * and a "life consumed" gauge. All figures come from the shared lifecycle service
 * via AssetService (never stored). Returns to Asset Detail (BR-NV-07).
 */
#[Layout('layouts.app')]
final class LifecycleView extends Component
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

        // "Life consumed" = age / expected life, as a percentage (capped at 100 for the gauge).
        $consumed = null;
        if ($asset !== null && $asset->expectedLife && $asset->lifecycle?->currentAge !== null) {
            $consumed = max(0, min(100, (int) round($asset->lifecycle->currentAge / $asset->expectedLife * 100)));
        }

        return view('livewire.assets.lifecycle-view', [
            'asset' => $asset,
            'consumedPercent' => $consumed,
            'breadcrumbs' => $crumbs->build($asset?->context() ?? [], [
                ['label' => $asset?->assetName ?? 'Asset', 'url' => route('assets.show', ['asset' => $this->assetId])],
                ['label' => 'Lifecycle', 'url' => null],
            ]),
        ]);
    }
}
