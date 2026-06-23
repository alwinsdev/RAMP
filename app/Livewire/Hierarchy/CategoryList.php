<?php

declare(strict_types=1);

namespace App\Livewire\Hierarchy;

use App\Services\AssetService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Asset categories within a panchayat. Each category drills into the Asset List
 * filtered by panchayat + category. Categories with zero assets are still shown
 * with a count of 0 (BR-CT-04).
 */
#[Layout('layouts.app')]
final class CategoryList extends Component
{
    public string $panchayatId = '';

    public function mount(string $panchayat, AssetService $assets): void
    {
        if ($assets->panchayatById($panchayat) === null) {
            session()->flash('notice', 'That panchayat could not be found.');
            $this->redirect(route('districts'), navigate: true);

            return;
        }

        $this->panchayatId = $panchayat;
    }

    public function render(AssetService $assets, BreadcrumbBuilder $crumbs)
    {
        $panchayat = $assets->panchayatById($this->panchayatId);
        $zone = $panchayat !== null ? $assets->zoneById($panchayat->zoneId) : null;

        return view('livewire.hierarchy.category-list', [
            'panchayat' => $panchayat,
            'categories' => $assets->categories(),
            'counts' => $assets->assetCountsByCategory($this->panchayatId),
            'breadcrumbs' => $crumbs->build([
                'districtId' => $zone?->districtId,
                'zoneId' => $zone?->id,
                'panchayatId' => $this->panchayatId,
            ]),
        ]);
    }
}
