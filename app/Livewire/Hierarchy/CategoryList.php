<?php

declare(strict_types=1);

namespace App\Livewire\Hierarchy;

use App\Services\AssetService;
use App\Services\CategoryService;
use App\Support\Breadcrumb\BreadcrumbBuilder;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Panchayat Category Dashboard (CR-05) — the primary operational screen. Shows the
 * 10 category cards (each with a per-category health breakdown) plus a panchayat
 * health roll-up. Each card drills into the filtered Asset List (BR-CT-04: zero-count
 * categories still shown).
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

    public function render(AssetService $assets, CategoryService $categories, BreadcrumbBuilder $crumbs)
    {
        $panchayat = $assets->panchayatById($this->panchayatId);
        $zone = $panchayat !== null ? $assets->zoneById($panchayat->zoneId) : null;

        return view('livewire.hierarchy.category-list', [
            'panchayat' => $panchayat,
            'summaries' => $categories->summariesForPanchayat($this->panchayatId),
            'health' => $categories->panchayatHealth($this->panchayatId),
            'breadcrumbs' => $crumbs->build([
                'districtId' => $zone?->districtId,
                'zoneId' => $zone?->id,
                'panchayatId' => $this->panchayatId,
            ]),
        ]);
    }
}
