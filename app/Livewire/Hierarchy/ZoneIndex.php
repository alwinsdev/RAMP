<?php

declare(strict_types=1);

namespace App\Livewire\Hierarchy;

use App\DataObjects\DistrictData;
use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Flat index of every zone in the user's scope (sidebar "Zones"). Each card drills
 * into the zone's panchayats. Counts are derived live from the scoped dataset.
 */
#[Layout('layouts.app')]
#[Title('Zones — RAMP')]
final class ZoneIndex extends Component
{
    public function render(AssetService $assets, BreadcrumbBuilder $crumbs)
    {
        $districts = [];
        foreach ($assets->districts() as $district) {
            /** @var DistrictData $district */
            $districts[$district->id] = $district->name;
        }

        return view('livewire.hierarchy.zone-index', [
            'zones' => $assets->zones(),
            'districtNames' => $districts,
            'counts' => $assets->assetCountsPerZone(),
            'breadcrumbs' => $crumbs->build([]),
        ]);
    }
}
