<?php

declare(strict_types=1);

namespace App\Livewire\Misc;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Settings sidebar destination (CR-02). Shows the signed-in profile + app info. */
#[Layout('layouts.app')]
#[Title('Settings — RAMP')]
final class Settings extends Component
{
    public function render()
    {
        return view('livewire.misc.settings');
    }
}
