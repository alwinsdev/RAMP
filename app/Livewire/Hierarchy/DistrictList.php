<?php

declare(strict_types=1);

namespace App\Livewire\Hierarchy;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Top of the hierarchy (no State). Lists districts with their asset counts and
 * drills into each district's zones.
 */
#[Layout('layouts.app')]
#[Title('Districts — RAMP')]
final class DistrictList extends Component
{
    public function render(AssetService $assets, BreadcrumbBuilder $crumbs)
    {
        return view('livewire.hierarchy.district-list', [
            'districts' => $assets->districts(),
            'counts' => $assets->assetCountsByDistrict(),
            'breadcrumbs' => $crumbs->build([]),
        ]);
    }
}
