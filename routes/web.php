<?php

declare(strict_types=1);

use App\Livewire\Assets\AssetDetail;
use App\Livewire\Assets\AssetList;
use App\Livewire\Assets\LifecycleView;
use App\Livewire\Assets\LocationView;
use App\Livewire\Assets\PhotoGallery;
use App\Livewire\Hierarchy\CategoryList;
use App\Livewire\Hierarchy\DistrictList;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Hierarchy\PanchayatList;
use App\Livewire\Hierarchy\ZoneList;
use Illuminate\Support\Facades\Route;

// Identifiers are stable, prefixed strings (e.g. DIST-SALEM, AST-0001). Constrain
// every route parameter to that shape so malformed paths 404 before reaching a
// component (defence in depth; invalid-but-well-formed ids still degrade gracefully).
$idPattern = '[A-Za-z0-9\-]{1,40}';

// The Dashboard is the landing screen / Home (SCR-01).
Route::get('/', Dashboard::class)->name('home');

/*
 | Hierarchy drill-down (District → Zone → Panchayat → Category → Asset List).
 | Each level carries its parent in the path so context + breadcrumbs are shareable.
 */
Route::get('/districts', DistrictList::class)->name('districts');
Route::get('/districts/{district}/zones', ZoneList::class)->name('zones')->where('district', $idPattern);
Route::get('/zones/{zone}/panchayats', PanchayatList::class)->name('panchayats')->where('zone', $idPattern);
Route::get('/panchayats/{panchayat}/categories', CategoryList::class)->name('categories')->where('panchayat', $idPattern);

// The Asset List — the convergence screen (filters carried as query params via #[Url]).
Route::get('/assets', AssetList::class)->name('assets');
Route::get('/assets/{asset}', AssetDetail::class)->name('assets.show')->where('asset', $idPattern);

// Asset Detail sub-views (always return to the parent detail — BR-NV-07).
Route::get('/assets/{asset}/photos', PhotoGallery::class)->name('assets.photos')->where('asset', $idPattern);
Route::get('/assets/{asset}/location', LocationView::class)->name('assets.location')->where('asset', $idPattern);
Route::get('/assets/{asset}/lifecycle', LifecycleView::class)->name('assets.lifecycle')->where('asset', $idPattern);
