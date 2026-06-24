<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Forgot Password (CR-01) — mock flow. In the POC there is no email/database, so we
 * acknowledge the request and link straight to the reset step (clearly labelled).
 */
#[Layout('layouts.guest')]
#[Title('Forgot password — RAMP')]
final class ForgotPassword extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    public bool $sent = false;

    public function submit(): void
    {
        $this->validate();
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
