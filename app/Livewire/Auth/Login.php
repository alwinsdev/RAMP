<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Login (CR-01) — mock authentication via the session guard backed by users.json.
 *
 * Brute-force hardened: attempts are throttled per (email + IP). A generic failure
 * message is used for both unknown users and wrong passwords so the form never
 * reveals whether an account exists (enumeration prevention).
 */
#[Layout('layouts.guest')]
#[Title('Sign in — RAMP')]
final class Login extends Component
{
    /** Max failed attempts before lockout, and the lockout window (seconds). */
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $this->validate();

        $key = $this->throttleKey();

        // Lock out once the attempt ceiling is hit (Rule 3.2 / Rule 12).
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} second(s).",
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($key);
        session()->regenerate();

        $this->redirectIntended(route('home'), navigate: true);
    }

    /** Throttle bucket keyed by the submitted email + client IP. */
    private function throttleKey(): string
    {
        return 'login|'.Str::transliterate(Str::lower($this->email)).'|'.request()->ip();
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
