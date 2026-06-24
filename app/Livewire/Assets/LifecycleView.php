<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use App\Support\Lifecycle\LifecycleCalculator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Lifecycle sub-view (SCR-09). Presents the computed lifecycle figures, the status,
 * and a "life consumed" gauge. All figures come from the shared lifecycle service
 * via AssetService (never stored). Returns to Asset Detail (BR-NV-07).
 */
#[Layout('layouts.app')]
#[Title('Asset Health — RAMP')]
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

    public function render(AssetService $assets, LifecycleCalculator $lifecycle, BreadcrumbBuilder $crumbs)
    {
        $asset = $assets->detail($this->assetId);
        $expectedLife = $lifecycle->expectedLife(); // fixed 25 years for every asset (CR-06)

        // "Life consumed" = age / expected life, as a percentage (capped at 100 for the gauge).
        $consumed = null;
        if ($asset !== null && $asset->lifecycle?->currentAge !== null) {
            $consumed = max(0, min(100, (int) round($asset->lifecycle->currentAge / $expectedLife * 100)));
        }

        return view('livewire.assets.lifecycle-view', [
            'asset' => $asset,
            'expectedLife' => $expectedLife,
            'consumedPercent' => $consumed,
            'breadcrumbs' => $crumbs->build($asset?->context() ?? [], [
                ['label' => $asset?->assetName ?? 'Asset', 'url' => route('assets.show', ['asset' => $this->assetId])],
                ['label' => 'Asset Health', 'url' => null],
            ]),
        ]);
    }
}
