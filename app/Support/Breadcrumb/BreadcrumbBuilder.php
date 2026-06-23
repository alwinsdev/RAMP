<?php

declare(strict_types=1);

namespace App\Support\Breadcrumb;

use App\Services\AssetService;

/**
 * Builds a breadcrumb trail from accumulated drill-down context (BR-NV-04).
 *
 * The hierarchy is District → Zone → Panchayat → Category → Asset (no State).
 * Every trail starts with Home and the Districts index, then one crumb per level
 * present in the context. The <x-breadcrumb> component renders the LAST item as
 * plain text (the current page) and all earlier items as links — so callers simply
 * append context in order and, optionally, a leaf crumb (e.g. an asset name).
 */
final class BreadcrumbBuilder
{
    public function __construct(
        private readonly AssetService $assets,
    ) {
    }

    /**
     * @param  array{districtId?:?string, zoneId?:?string, panchayatId?:?string, categoryId?:?string}  $context
     * @param  array<int, array{label:string, url:?string}>  $leaves  trailing crumbs appended in order
     *         (e.g. a linked asset name + a plain sub-view name). The component renders the last as plain.
     * @return array<int, array{label:string, url:?string}>
     */
    public function build(array $context, array $leaves = []): array
    {
        $trail = [
            ['label' => 'Home', 'url' => route('home')],
            ['label' => 'Districts', 'url' => route('districts')],
        ];

        $districtId = $context['districtId'] ?? null;
        $zoneId = $context['zoneId'] ?? null;
        $panchayatId = $context['panchayatId'] ?? null;
        $categoryId = $context['categoryId'] ?? null;

        // Backfill ancestry so the trail always reflects a valid full path (BR-NV-04),
        // even when arriving with only the deepest id (e.g. a category drill-down that
        // carries just panchayatId + categoryId).
        if ($panchayatId !== null && $zoneId === null) {
            $zoneId = $this->assets->panchayatById($panchayatId)?->zoneId;
        }
        if ($zoneId !== null && $districtId === null) {
            $districtId = $this->assets->zoneById($zoneId)?->districtId;
        }

        if ($districtId !== null && ($district = $this->assets->districtById($districtId)) !== null) {
            $trail[] = ['label' => $district->name, 'url' => route('zones', ['district' => $district->id])];
        }

        if ($zoneId !== null && ($zone = $this->assets->zoneById($zoneId)) !== null) {
            $trail[] = ['label' => $zone->name, 'url' => route('panchayats', ['zone' => $zone->id])];
        }

        if ($panchayatId !== null && ($panchayat = $this->assets->panchayatById($panchayatId)) !== null) {
            $trail[] = ['label' => $panchayat->name, 'url' => route('categories', ['panchayat' => $panchayat->id])];
        }

        if ($categoryId !== null && ($category = $this->assets->categoryById($categoryId)) !== null) {
            $trail[] = [
                'label' => $category->name,
                'url' => route('assets', array_filter([
                    'panchayatId' => $panchayatId,
                    'categoryId' => $category->id,
                ])),
            ];
        }

        foreach ($leaves as $leaf) {
            $trail[] = ['label' => $leaf['label'], 'url' => $leaf['url'] ?? null];
        }

        return $trail;
    }
}
