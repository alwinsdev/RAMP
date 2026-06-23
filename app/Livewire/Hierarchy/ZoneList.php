<?php

declare(strict_types=1);

namespace App\Livewire\Hierarchy;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Zones within a district. Drills into each zone's panchayats. An unknown district
 * id degrades to the Districts index (BR-NV-09).
 */
#[Layout('layouts.app')]
final class ZoneList extends Component
{
    public string $districtId = '';

    public function mount(string $district, AssetService $assets): void
    {
        if ($assets->districtById($district) === null) {
            session()->flash('notice', 'That district could not be found.');
            $this->redirect(route('districts'), navigate: true);

            return;
        }

        $this->districtId = $district;
    }

    public function render(AssetService $assets, BreadcrumbBuilder $crumbs)
    {
        $district = $assets->districtById($this->districtId);

        return view('livewire.hierarchy.zone-list', [
            'district' => $district,
            'zones' => $assets->zones($this->districtId),
            'counts' => $assets->assetCountsByZone($this->districtId),
            'breadcrumbs' => $crumbs->build(['districtId' => $this->districtId]),
        ]);
    }
}
