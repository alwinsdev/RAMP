<?php

declare(strict_types=1);

namespace App\Livewire\Assets;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Photo Gallery sub-view (SCR-07). Thumbnails + lightbox; returns to Asset Detail
 * (BR-NV-07). Unknown asset id degrades to the Asset List (BR-NV-09).
 */
#[Layout('layouts.app')]
final class PhotoGallery extends Component
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

        return view('livewire.assets.photo-gallery', [
            'asset' => $asset,
            'breadcrumbs' => $crumbs->build($asset?->context() ?? [], [
                ['label' => $asset?->assetName ?? 'Asset', 'url' => route('assets.show', ['asset' => $this->assetId])],
                ['label' => 'Photos', 'url' => null],
            ]),
        ]);
    }
}
