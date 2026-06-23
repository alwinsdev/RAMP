<?php

declare(strict_types=1);

namespace App\Livewire\Hierarchy;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Panchayats within a zone. Drills into each panchayat's asset categories.
 */
#[Layout('layouts.app')]
final class PanchayatList extends Component
{
    public string $zoneId = '';

    public function mount(string $zone, AssetService $assets): void
    {
        if ($assets->zoneById($zone) === null) {
            session()->flash('notice', 'That zone could not be found.');
            $this->redirect(route('districts'), navigate: true);

            return;
        }

        $this->zoneId = $zone;
    }

    public function render(AssetService $assets, BreadcrumbBuilder $crumbs)
    {
        $zone = $assets->zoneById($this->zoneId);

        return view('livewire.hierarchy.panchayat-list', [
            'zone' => $zone,
            'panchayats' => $assets->panchayats($this->zoneId),
            'counts' => $assets->assetCountsByPanchayat($this->zoneId),
            'breadcrumbs' => $crumbs->build([
                'districtId' => $zone?->districtId,
                'zoneId' => $this->zoneId,
            ]),
        ]);
    }
}
