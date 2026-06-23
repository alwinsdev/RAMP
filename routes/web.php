<?php

declare(strict_types=1);

use App\Livewire\Home;
use Illuminate\Support\Facades\Route;

// Sprint 0 foundation landing. Becomes the real Dashboard (SCR-01) in Sprint 3.
Route::get('/', Home::class)->name('home');
