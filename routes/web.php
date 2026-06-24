<?php

declare(strict_types=1);

use App\Livewire\Assets\AssetDetail;
use App\Livewire\Assets\AssetList;
use App\Livewire\Assets\LifecycleView;
use App\Livewire\Assets\LocationView;
use App\Livewire\Assets\PhotoGallery;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\Hierarchy\CategoryIndex;
use App\Livewire\Hierarchy\CategoryList;
use App\Livewire\Hierarchy\DistrictList;
use App\Livewire\Hierarchy\PanchayatIndex;
use App\Livewire\Hierarchy\PanchayatList;
use App\Livewire\Hierarchy\ZoneIndex;
use App\Livewire\Hierarchy\ZoneList;
use App\Livewire\Map\AssetIntelMap;
use App\Livewire\Misc\Settings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$idPattern = '[A-Za-z0-9\-]{1,40}';

/*
 | Guest (unauthenticated) routes — the mock authentication flow (CR-01).
 */
Route::middleware('guest')->group(function (): void {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password', ResetPassword::class)->name('password.reset');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

/*
 | Authenticated application — everything below requires a signed-in officer.
 | Data is automatically scoped to the user's role (Scope).
 */
Route::middleware('auth')->group(function () use ($idPattern): void {
    // Dashboard is the landing screen / Home (SCR-01).
    Route::get('/', Dashboard::class)->name('home');

    // Flat hierarchy index screens (sidebar entries — every node in the user's scope).
    Route::get('/districts', DistrictList::class)->name('districts');
    Route::get('/zones', ZoneIndex::class)->name('zones.index');
    Route::get('/panchayats', PanchayatIndex::class)->name('panchayats.index');
    Route::get('/categories', CategoryIndex::class)->name('categories.index');

    // Hierarchy drill-down (District → Zone → Panchayat → Category → Asset List).
    Route::get('/districts/{district}/zones', ZoneList::class)->name('zones')->where('district', $idPattern);
    Route::get('/zones/{zone}/panchayats', PanchayatList::class)->name('panchayats')->where('zone', $idPattern);
    Route::get('/panchayats/{panchayat}/categories', CategoryList::class)->name('categories')->where('panchayat', $idPattern);

    // Asset List — the convergence screen (filters carried as query params via #[Url]).
    Route::get('/assets', AssetList::class)->name('assets');
    Route::get('/assets/{asset}', AssetDetail::class)->name('assets.show')->where('asset', $idPattern);
    Route::get('/assets/{asset}/photos', PhotoGallery::class)->name('assets.photos')->where('asset', $idPattern);
    Route::get('/assets/{asset}/location', LocationView::class)->name('assets.location')->where('asset', $idPattern);
    Route::get('/assets/{asset}/lifecycle', LifecycleView::class)->name('assets.lifecycle')->where('asset', $idPattern);

    // Asset Intelligence Map — the flagship full-screen visualization (replaces Reports).
    Route::get('/map', AssetIntelMap::class)->name('map');
    Route::get('/settings', Settings::class)->name('settings');
});
