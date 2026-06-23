<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * TEMPORARY Sprint 0 landing — a foundation smoke screen proving the full stack is
 * wired: Livewire 3 + layout + Tailwind tokens + container DI + the data seam.
 *
 * Replaced by the real Dashboard Livewire component in Sprint 3. It deliberately
 * shows only headline figures via DashboardService (never hard-coded) to demonstrate
 * the UI -> Service -> Provider -> mock JSON chain end to end.
 */
#[Layout('layouts.app')]
#[Title('RAMP — Foundation Ready')]
final class Home extends Component
{
    public function render(DashboardService $dashboard)
    {
        return view('livewire.home', [
            'summary' => $dashboard->summary(),
        ]);
    }
}
