<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Reset Password (CR-01) — mock flow. Validates the new password but does not
 * persist it (no database in the POC); on success it returns the user to sign in.
 */
#[Layout('layouts.guest')]
#[Title('Reset password — RAMP')]
final class ResetPassword extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:8|same:passwordConfirmation')]
    public string $password = '';

    #[Validate('required')]
    public string $passwordConfirmation = '';

    public function mount(string $email = ''): void
    {
        $this->email = $email;
    }

    public function submit(): void
    {
        $this->validate();

        session()->flash('status', 'Your password has been reset. Please sign in. (Demo — not stored.)');

        $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.reset-password');
    }
}
