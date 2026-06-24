<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Asset Information (SCR-06) — the leaf record consolidating asset, administrative,
 * asset-health, location (with an embedded map preview), and photo information.
 * An unknown / out-of-scope asset id degrades to the Asset List (BR-NV-09, RBAC).
 */
#[Layout('layouts.app')]
#[Title('Asset Information — RAMP')]
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
