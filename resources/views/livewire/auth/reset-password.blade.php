<div>
    <a href="{{ route('login') }}" wire:navigate class="mb-6 inline-flex items-center gap-1.5 text-sm font-semibold text-brand hover:text-brand-hover">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        Back to sign in
    </a>

    <h2 class="text-2xl font-bold tracking-tight text-ink">Set a new password</h2>
    <p class="mt-1 text-sm text-ink-soft">Choose a new password for your account.</p>

    <form wire:submit="submit" class="mt-6 flex flex-col gap-4">
        <div>
            <label for="email" class="mb-1.5 block text-sm font-semibold text-ink">Email address</label>
            <input wire:model="email" id="email" type="email"
                   class="w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            @error('email') <p class="mt-1 text-xs font-medium text-status-expired">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="mb-1.5 block text-sm font-semibold text-ink">New password</label>
            <input wire:model="password" id="password" type="password" autocomplete="new-password"
                   class="w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
                   placeholder="At least 8 characters">
            @error('password') <p class="mt-1 text-xs font-medium text-status-expired">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="passwordConfirmation" class="mb-1.5 block text-sm font-semibold text-ink">Confirm password</label>
            <input wire:model="passwordConfirmation" id="passwordConfirmation" type="password" autocomplete="new-password"
                   class="w-full rounded-lg border border-hairline bg-surface px-3.5 py-2.5 text-sm text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20">
            @error('passwordConfirmation') <p class="mt-1 text-xs font-medium text-status-expired">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-hover">
            Reset password
        </button>
    </form>
</div>
