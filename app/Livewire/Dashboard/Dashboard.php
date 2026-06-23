<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Services\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The Dashboard (SCR-01) — landing screen and command center. Answers, at a glance:
 * how many assets, where, and what needs attention. Every figure is a drill-down
 * doorway into the filtered Asset List (BR-NV-06). All counts come from
 * DashboardService over the live dataset — never hard-coded (BR-DI-05).
 */
#[Layout('layouts.app')]
#[Title('Dashboard — RAMP')]
final class Dashboard extends Component
{
    public function render(DashboardService $dashboard)
    {
        return view('livewire.dashboard.dashboard', [
            'summary' => $dashboard->summary(),
        ]);
    }
}
