<?php

declare(strict_types=1);

namespace App\Livewire\Hierarchy;

use App\DataObjects\ZoneData;
use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Flat index of every panchayat in the user's scope (sidebar "Panchayats"). Each card
 * drills into the panchayat's category dashboard. Counts derive live from the dataset.
 */
#[Layout('layouts.app')]
#[Title('Panchayats — RAMP')]
final class PanchayatIndex extends Component
{
    public function render(AssetService $assets, BreadcrumbBuilder $crumbs)
    {
        $zones = [];
        foreach ($assets->zones() as $zone) {
            /** @var ZoneData $zone */
            $zones[$zone->id] = $zone->name;
        }

        return view('livewire.hierarchy.panchayat-index', [
            'panchayats' => $assets->panchayats(),
            'zoneNames' => $zones,
            'counts' => $assets->assetCountsPerPanchayat(),
            'breadcrumbs' => $crumbs->build([]),
        ]);
    }
}
