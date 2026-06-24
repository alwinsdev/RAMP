<?php

declare(strict_types=1);

namespace App\Livewire\Hierarchy;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Flat index of all asset categories (sidebar "Asset Categories" for admin / district
 * users). Each card opens the Asset List filtered to that category. Zero-count
 * categories are still shown (BR-CT-04). Counts derive live from the scoped dataset.
 */
#[Layout('layouts.app')]
#[Title('Asset Categories — RAMP')]
final class CategoryIndex extends Component
{
    public function render(AssetService $assets, BreadcrumbBuilder $crumbs)
    {
        return view('livewire.hierarchy.category-index', [
            'categories' => $assets->categories(),
            'counts' => $assets->assetCountsPerCategory(),
            'breadcrumbs' => $crumbs->build([]),
        ]);
    }
}
