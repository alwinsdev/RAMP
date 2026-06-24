<div>
    <a href="{{ route('login') }}" wire:navigate class="mb-6 inline-flex items-center gap-1.5 text-sm font-semibold text-brand hover:text-brand-hover">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        Back to sign in
    </a>

    <h2 class="text-2xl font-bold tracking-tight text-ink">Forgot password</h2>
    <p class="mt-1 text-sm text-ink-soft">Enter your email and we'll help you reset it.</p>

    @if ($sent)
        <div class="mt-6 rounded-xl border border-hairline bg-surface p-5">
            <div class="flex items-center gap-2.5 text-status-healthy">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                <p class="text-sm font-semibold text-ink">Request received</p>
            </div>
            <p class="mt-2 text-sm text-ink-soft">
                In production a reset link would be emailed to <span class="font-medium text-ink">{{ $email }}</span>.
                This is a proof of concept, so continue directly to set a new password.
            </p>
            <a href="{{ route('password.reset', ['email' => $email]) }}" wire:navigate
               class="mt-4 inline-flex w-full items-center justify-center rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-hover">
                Continue to reset password
            </a>
        </div>
    @else
        <form wire:submit="submit" class="mt-6 flex flex-col gap-4">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold text-ink">Email address</label>
                <input wire:model="email" id="email" type="email" autofocus
                       class="w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-muted focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
                       placeholder="you@ramp.gov.in">
                @error('email') <p class="mt-1 text-xs font-medium text-status-expired">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-hover">
                Send reset instructions
            </button>
        </form>
    @endif
</div>
